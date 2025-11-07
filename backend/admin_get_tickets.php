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

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 200;
if ($limit <= 0 || $limit > 2000) $limit = 200;

try {
    $sql = "SELECT t.ticket_id, t.user_id, t.subject, t.message, t.status, t.assigned_to, t.created_at, t.updated_at, u.email, u.first_name, u.last_name
        FROM support_tickets t
        LEFT JOIN users u ON u.user_id = t.user_id
        WHERE 1=1 ";
    $params = [];
    if ($q !== '') {
        $sql .= " AND (t.subject LIKE ? OR t.message LIKE ? OR u.email LIKE ? )";
        $params = array_merge($params, ["%$q%","%$q%","%$q%"]);
    }
    if ($status !== '') { $sql .= " AND t.status = ?"; $params[] = $status; }
    $sql .= " ORDER BY t.updated_at DESC LIMIT ?"; $params[] = $limit;

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(true, 'Tickets loaded', $rows);
} catch (Exception $e) {
    error_log('admin_get_tickets error: ' . $e->getMessage());
    sendResponse(false, 'Failed to load tickets', null, 500);
}

?>
