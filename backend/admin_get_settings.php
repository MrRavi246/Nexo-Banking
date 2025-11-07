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

try {
    $sql = "SELECT setting_id, setting_key, setting_value, description, is_active, created_at, updated_at FROM system_settings ORDER BY setting_key";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(true, 'Settings loaded', $rows);
} catch (Exception $e) {
    error_log('admin_get_settings error: ' . $e->getMessage());
    sendResponse(false, 'Failed to load settings', null, 500);
}

?>
