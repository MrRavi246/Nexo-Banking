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

$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 1000;
if ($limit <= 0 || $limit > 5000) $limit = 1000;

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="support-tickets-' . date('Ymd') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['ticket_id','user_id','email','subject','message','status','assigned_to','created_at','updated_at']);

$sql = "SELECT t.ticket_id, t.user_id, u.email, t.subject, t.message, t.status, t.assigned_to, t.created_at, t.updated_at
    FROM support_tickets t
    LEFT JOIN users u ON u.user_id = t.user_id
    WHERE 1=1";
$params = [];
if ($status !== '') { $sql .= ' AND t.status = ?'; $params[] = $status; }
$sql .= ' ORDER BY t.updated_at DESC LIMIT ?'; $params[] = $limit;

$stmt = $conn->prepare($sql);
$stmt->execute($params);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($out, [$row['ticket_id'],$row['user_id'],$row['email'],$row['subject'],$row['message'],$row['status'],$row['assigned_to'],$row['created_at'],$row['updated_at']]);
}

fclose($out);
exit;

?>
