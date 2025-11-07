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
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$action = isset($_GET['action']) ? trim($_GET['action']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 200;
if ($limit <= 0 || $limit > 2000) $limit = 200;

try {
    $sql = "SELECT al.log_id, al.user_id, al.action_type, al.table_name, al.record_id, al.old_values, al.new_values, al.ip_address, al.user_agent, al.created_at, u.email, u.first_name, u.last_name
        FROM audit_logs al
        LEFT JOIN users u ON u.user_id = al.user_id
        WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $params = [$days];

    if ($q !== '') {
        $sql .= " AND (al.action_type LIKE ? OR al.table_name LIKE ? OR al.ip_address LIKE ? OR al.old_values LIKE ? OR al.new_values LIKE ? OR u.email LIKE ? )";
        $like = "%$q%";
        $params = array_merge($params, [$like, $like, $like, $like, $like, $like]);
    }
    if ($action !== '') { $sql .= " AND al.action_type = ?"; $params[] = $action; }

    $sql .= " ORDER BY al.created_at DESC LIMIT ?";
    $params[] = $limit;

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(true, 'Audit logs loaded', $rows);
} catch (Exception $e) {
    error_log('admin_get_audit error: ' . $e->getMessage());
    sendResponse(false, 'Failed to load audit logs', null, 500);
}

?>
