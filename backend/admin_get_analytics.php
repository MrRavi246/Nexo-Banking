<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$conn = getDBConnection();

// Admin auth
if (!isAdminLoggedIn()) {
    sendResponse(false, 'Not authenticated (admin)', null, 401);
}
if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    sendResponse(false, 'Invalid admin session', null, 401);
}

$days = isset($_GET['days']) ? intval($_GET['days']) : 30;
if ($days <= 0) $days = 30;
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

try {
    // scope accounts by user if provided
    $accountWhere = '';
    $params = [$days];
    if ($userId) {
        // get account ids for user
        $acctStmt = $conn->prepare('SELECT account_id FROM accounts WHERE user_id = ?');
        $acctStmt->execute([$userId]);
        $accs = $acctStmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($accs)) {
            sendResponse(true, 'No data', [
                'total_income'=>0,'total_expenses'=>0,'net_savings'=>0,'savings_rate'=>0,'income_expense_series'=>['labels'=>[],'income'=>[],'expenses'=>[]],'cash_flow_series'=>['labels'=>[],'values'=>[]]
            ]);
        }
        $placeholders = implode(',', array_fill(0, count($accs), '?'));
        $accountWhere = "AND account_id IN ($placeholders)";
        $params = array_merge($accs, [$days]);
    }

    // Totals
    $totSql = "SELECT
        SUM(CASE WHEN transaction_type IN ('deposit','refund','interest') THEN amount ELSE 0 END) AS total_income,
        SUM(CASE WHEN transaction_type IN ('withdrawal','payment','fee') THEN amount ELSE 0 END) AS total_expenses
        FROM transactions
        WHERE status = 'completed'
        AND transaction_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
        $accountWhere
    ";
    $totStmt = $conn->prepare($totSql);
    $totStmt->execute($params);
    $tot = $totStmt->fetch(PDO::FETCH_ASSOC);
    $totalIncome = floatval($tot['total_income'] ?? 0);
    $totalExpenses = floatval($tot['total_expenses'] ?? 0);
    $net = $totalIncome - $totalExpenses;
    $rate = $totalIncome > 0 ? ($net / $totalIncome) * 100 : 0;

    // Time series
    $seriesSql = "SELECT DATE(transaction_date) as dt,
        SUM(CASE WHEN transaction_type IN ('deposit','refund','interest') THEN amount ELSE 0 END) AS income,
        SUM(CASE WHEN transaction_type IN ('withdrawal','payment','fee') THEN amount ELSE 0 END) AS expenses
        FROM transactions
        WHERE status = 'completed'
        AND transaction_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
        $accountWhere
        GROUP BY dt ORDER BY dt ASC";
    $seriesStmt = $conn->prepare($seriesSql);
    $seriesStmt->execute($params);
    $rows = $seriesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Build daily labels
    $labels = [];$incomeSeries=[];$expenseSeries=[];$cashLabels=[];$cashValues=[];
    $rowMap = [];
    foreach ($rows as $r) $rowMap[$r['dt']] = $r;
    $period = new DatePeriod(new DateTime("-{$days} days"), new DateInterval('P1D'), new DateTime('+1 day'));
    foreach ($period as $d) {
        $ds = $d->format('Y-m-d');
        $labels[] = $d->format('M j');
        $income = isset($rowMap[$ds]) ? floatval($rowMap[$ds]['income']) : 0.0;
        $expenses = isset($rowMap[$ds]) ? floatval($rowMap[$ds]['expenses']) : 0.0;
        $incomeSeries[] = $income; $expenseSeries[] = $expenses;
        $cashLabels[] = $d->format('M j'); $cashValues[] = $income - $expenses;
    }

    $data = [
        'total_income'=>round($totalIncome,2),
        'total_expenses'=>round($totalExpenses,2),
        'net_savings'=>round($net,2),
        'savings_rate'=>round($rate,1),
        'income_expense_series'=>['labels'=>$labels,'income'=>$incomeSeries,'expenses'=>$expenseSeries],
        'cash_flow_series'=>['labels'=>$cashLabels,'values'=>$cashValues]
    ];

    sendResponse(true, 'Admin analytics', $data);

} catch (Exception $e) {
    error_log('admin_get_analytics error: '.$e->getMessage());
    sendResponse(false, 'Failed to load analytics', null, 500);
}

?>
