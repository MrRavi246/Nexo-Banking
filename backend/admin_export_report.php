<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$conn = getDBConnection();

// Admin auth
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo "Not authenticated";
    exit;
}
if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    http_response_code(401);
    echo "Invalid session";
    exit;
}

$days = isset($_GET['days']) ? intval($_GET['days']) : 30;
if ($days <= 0) $days = 30;
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

try {
    $where = ['t.transaction_date >= DATE_SUB(NOW(), INTERVAL ? DAY)'];
    $params = [$days];
    if ($status !== '') { $where[] = 't.status = ?'; $params[] = $status; }

    $sql = "SELECT t.transaction_id, t.account_id, t.transaction_type, t.amount, t.status, t.transaction_date, t.description, t.recipient_name, t.recipient_account, u.first_name, u.last_name, u.email
        FROM transactions t
        LEFT JOIN accounts a ON a.account_id = t.account_id
        LEFT JOIN users u ON u.user_id = a.user_id
        WHERE " . implode(' AND ', $where) . " ORDER BY t.transaction_date DESC LIMIT 5000";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // stream CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions_report.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['transaction_id','account_id','account_holder','email','type','amount','status','date','description','recipient_name','recipient_account']);
    foreach ($rows as $r) {
        $holder = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
        fputcsv($out, [ $r['transaction_id'], $r['account_id'], $holder, $r['email'] ?? '', $r['transaction_type'] ?? '', $r['amount'], $r['status'] ?? '', $r['transaction_date'] ?? '', $r['description'] ?? '', $r['recipient_name'] ?? '', $r['recipient_account'] ?? '' ]);
    }
    fclose($out);
    exit;

} catch (Exception $e) {
    error_log('admin_export_report error: '.$e->getMessage());
    http_response_code(500);
    echo 'Failed to export';
}

?>
