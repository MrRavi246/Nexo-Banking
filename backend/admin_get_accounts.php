<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$conn = getDBConnection();

// Ensure admin is logged in
if (!isAdminLoggedIn()) {
    sendResponse(false, 'Not authenticated (admin)', null, 401);
}

if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    sendResponse(false, 'Invalid admin session', null, 401);
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$status = isset($_GET['status']) && $_GET['status'] !== '' ? trim($_GET['status']) : '';
$type = isset($_GET['type']) && $_GET['type'] !== '' ? trim($_GET['type']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 500;
if ($limit <= 0 || $limit > 2000) $limit = 500;

try {
    $where = [];
    $params = [];

    if ($q !== '') {
        $where[] = "(a.account_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR a.account_id LIKE ?)";
        $like = '%' . $q . '%';
        $params = array_merge($params, [$like, $like, $like, $like, $like]);
    }

    if ($status !== '') {
        $where[] = 'a.status = ?';
        $params[] = $status;
    }

    if ($type !== '') {
        $where[] = 'a.account_type = ?';
        $params[] = $type;
    }

    $sql = "SELECT a.account_id, a.account_number, a.user_id, a.account_type, a.balance, a.currency, a.status, a.last_activity, a.created_at,
            u.first_name, u.last_name, u.email
        FROM accounts a
        LEFT JOIN users u ON u.user_id = a.user_id";

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY a.created_at DESC LIMIT ' . intval($limit);

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(true, 'Accounts loaded', ['accounts' => $rows]);

} catch (Exception $e) {
    error_log('admin_get_accounts error: ' . $e->getMessage());
    sendResponse(false, 'Failed to load accounts', null, 500);
}

?>
