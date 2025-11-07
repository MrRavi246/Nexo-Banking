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

// Read query parameters
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$status = isset($_GET['status']) && $_GET['status'] !== '' ? trim($_GET['status']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 500;
if ($limit <= 0 || $limit > 2000) $limit = 500;

try {
    $where = [];
    $params = [];

    if ($q !== '') {
        // search across id, recipient_name, recipient_account, transaction_type, status, description
        $where[] = "(transaction_id LIKE ? OR recipient_name LIKE ? OR recipient_account LIKE ? OR transaction_type LIKE ? OR status LIKE ? OR description LIKE ? OR reference_id LIKE ? )";
        $like = '%' . $q . '%';
        $params = array_merge($params, [$like, $like, $like, $like, $like, $like, $like]);
    }

    if ($status !== '') {
        $where[] = 'status = ?';
        $params[] = $status;
    }

    $sql = 'SELECT transaction_id, account_id, transaction_type, amount, description, recipient_name, recipient_account, category, reference_id, status, transaction_date FROM transactions';
    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY transaction_date DESC LIMIT ' . intval($limit);

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(true, 'Transactions loaded', ['transactions' => $rows]);

} catch (Exception $e) {
    error_log('admin_get_transactions error: ' . $e->getMessage());
    sendResponse(false, 'Failed to load transactions', null, 500);
}

?>
