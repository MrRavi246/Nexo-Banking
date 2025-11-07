<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$conn = getDBConnection();

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo 'Not authenticated';
    exit;
}
if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    http_response_code(401);
    echo 'Invalid admin session';
    exit;
}

$days = isset($_GET['days']) ? intval($_GET['days']) : 30;
$action = isset($_GET['action']) ? trim($_GET['action']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 1000;
if ($limit <= 0 || $limit > 5000) $limit = 1000;

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="audit-logs-' . date('Ymd') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['log_id','created_at','user_id','email','action_type','table_name','record_id','ip_address','user_agent','old_values','new_values']);

$sql = "SELECT al.log_id, al.created_at, al.user_id, u.email, al.action_type, al.table_name, al.record_id, al.ip_address, al.user_agent, al.old_values, al.new_values
    FROM audit_logs al
    LEFT JOIN users u ON u.user_id = al.user_id
    WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
$params = [$days];
if ($action !== '') { $sql .= ' AND al.action_type = ?'; $params[] = $action; }
$sql .= ' ORDER BY al.created_at DESC LIMIT ?'; $params[] = $limit;

$stmt = $conn->prepare($sql);
$stmt->execute($params);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($out, [$row['log_id'], $row['created_at'], $row['user_id'], $row['email'], $row['action_type'], $row['table_name'], $row['record_id'], $row['ip_address'], $row['user_agent'], $row['old_values'], $row['new_values']]);
}

fclose($out);
exit;

?>
