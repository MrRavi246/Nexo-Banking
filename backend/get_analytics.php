<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$conn = getDBConnection();

// Ensure user is logged in
if (!isLoggedIn()) {
    sendResponse(false, 'Not authenticated', null, 401);
}

if (!validateSession($conn, $_SESSION['user_id'], $_SESSION['session_token'])) {
    sendResponse(false, 'Invalid session', null, 401);
}

$userId = $_SESSION['user_id'];

// Accept days parameter (GET) default 30
$days = isset($_GET['days']) ? intval($_GET['days']) : 30;
if ($days <= 0) $days = 30;

try {
    // get user's account ids
    $acctStmt = $conn->prepare("SELECT account_id FROM accounts WHERE user_id = ?");
    $acctStmt->execute([$userId]);
    $accounts = $acctStmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($accounts)) {
        // no accounts: return zeros
        $data = [
            'total_income' => 0.00,
            'total_expenses' => 0.00,
            'net_savings' => 0.00,
            'savings_rate' => 0.0,
            'top_categories' => [],
            'income_expense_series' => ['labels'=>[], 'income'=>[], 'expenses'=>[]],
            'cash_flow_series' => ['labels'=>[], 'values'=>[]]
        ];
        sendResponse(true, 'No accounts', $data);
    }

    // Build IN clause for account ids
    $placeholders = implode(',', array_fill(0, count($accounts), '?'));

    // Totals (income vs expenses)
    $totalsSql = "SELECT
        SUM(CASE WHEN transaction_type IN ('deposit','refund','interest') THEN amount ELSE 0 END) AS total_income,
        SUM(CASE WHEN transaction_type IN ('withdrawal','payment','fee') THEN amount ELSE 0 END) AS total_expenses
        FROM transactions
        WHERE account_id IN ($placeholders)
        AND status = 'completed'
        AND transaction_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
    ";

    $totalsStmt = $conn->prepare($totalsSql);
    $bindParams = array_merge($accounts, [$days]);
    $totalsStmt->execute($bindParams);
    $totals = $totalsStmt->fetch(PDO::FETCH_ASSOC);

    $totalIncome = floatval($totals['total_income'] ?? 0);
    $totalExpenses = floatval($totals['total_expenses'] ?? 0);
    $netSavings = $totalIncome - $totalExpenses;
    $savingsRate = $totalIncome > 0 ? ($netSavings / $totalIncome) * 100 : 0.0;

    // Top categories (expenses by category)
    $catSql = "SELECT category, SUM(amount) as total FROM transactions
        WHERE account_id IN ($placeholders)
        AND status = 'completed'
        AND transaction_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
        AND category IS NOT NULL
        AND transaction_type IN ('withdrawal','payment')
        GROUP BY category
        ORDER BY total DESC
        LIMIT 6";

    $catStmt = $conn->prepare($catSql);
    $catStmt->execute($bindParams);
    $topCats = $catStmt->fetchAll(PDO::FETCH_ASSOC);

    // Income/expense series by date (daily)
    $seriesSql = "SELECT DATE(transaction_date) as dt,
        SUM(CASE WHEN transaction_type IN ('deposit','refund','interest') THEN amount ELSE 0 END) AS income,
        SUM(CASE WHEN transaction_type IN ('withdrawal','payment','fee') THEN amount ELSE 0 END) AS expenses
        FROM transactions
        WHERE account_id IN ($placeholders)
        AND status = 'completed'
        AND transaction_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
        GROUP BY dt
        ORDER BY dt ASC";

    $seriesStmt = $conn->prepare($seriesSql);
    $seriesStmt->execute($bindParams);
    $rows = $seriesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Build full labels for each day in range to avoid gaps
    $labels = [];
    $incomeSeries = [];
    $expenseSeries = [];
    $cashFlowLabels = [];
    $cashValues = [];

    $period = new DatePeriod(new DateTime("-{$days} days"), new DateInterval('P1D'), new DateTime('+1 day'));
    // Map rows by date string
    $rowMap = [];
    foreach ($rows as $r) {
        $rowMap[$r['dt']] = $r;
    }

    foreach ($period as $d) {
        $ds = $d->format('Y-m-d');
        $labels[] = $d->format('M j');
        $income = isset($rowMap[$ds]) ? floatval($rowMap[$ds]['income']) : 0.0;
        $expenses = isset($rowMap[$ds]) ? floatval($rowMap[$ds]['expenses']) : 0.0;
        $incomeSeries[] = $income;
        $expenseSeries[] = $expenses;
        $cashFlowLabels[] = $d->format('M j');
        $cashValues[] = $income - $expenses;
    }

    $data = [
        'total_income' => round($totalIncome,2),
        'total_expenses' => round($totalExpenses,2),
        'net_savings' => round($netSavings,2),
        'savings_rate' => round($savingsRate,1),
        'top_categories' => $topCats,
        'income_expense_series' => ['labels' => $labels, 'income' => $incomeSeries, 'expenses' => $expenseSeries],
        'cash_flow_series' => ['labels' => $cashFlowLabels, 'values' => $cashValues]
    ];

    sendResponse(true, 'Analytics data', $data);

} catch (Exception $e) {
    error_log('Analytics Error: ' . $e->getMessage());
    sendResponse(false, 'Failed to load analytics', null, 500);
}

?>
