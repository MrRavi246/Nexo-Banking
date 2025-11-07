<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$conn = getDBConnection();

if (!isAdminLoggedIn()) {
    sendResponse(false, 'Not authenticated (admin)', null, 401);
}
if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    sendResponse(false, 'Invalid admin session', null, 401);
}

$days = isset($_GET['days']) ? intval($_GET['days']) : 30;
if ($days <= 0) $days = 30;

// Stream a CSV of recent audit logs and failed logins
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="security-audit-' . date('Ymd') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['type', 'log_id', 'user_id', 'email', 'action_type', 'table_name', 'record_id', 'ip_address', 'user_agent', 'created_at']);

$sql = "SELECT al.log_id, al.user_id, u.email, al.action_type, al.table_name, al.record_id, al.ip_address, al.user_agent, al.created_at
    FROM audit_logs al
    LEFT JOIN users u ON u.user_id = al.user_id
    WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
    ORDER BY al.created_at DESC LIMIT 1000";
$stmt = $conn->prepare($sql);
$stmt->execute([$days]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $type = ($row['action_type'] === 'LOGIN_FAILED') ? 'failed_login' : 'audit';
    fputcsv($out, [$type, $row['log_id'], $row['user_id'], $row['email'], $row['action_type'], $row['table_name'], $row['record_id'], $row['ip_address'], $row['user_agent'], $row['created_at']]);
}

fclose($out);
exit;

?>
