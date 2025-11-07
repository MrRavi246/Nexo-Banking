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

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    sendResponse(false, 'Invalid JSON body', null, 400);
}

$settingId = isset($input['setting_id']) ? intval($input['setting_id']) : null;
$settingKey = isset($input['setting_key']) ? trim($input['setting_key']) : null;
$settingValue = isset($input['setting_value']) ? $input['setting_value'] : null;
$isActive = isset($input['is_active']) ? (int)$input['is_active'] : null;

if (!$settingId && !$settingKey) {
    sendResponse(false, 'setting_id or setting_key required', null, 400);
}

try {
    // Build update dynamically
    $sets = [];
    $params = [];
    if ($settingValue !== null) { $sets[] = 'setting_value = ?'; $params[] = $settingValue; }
    if ($isActive !== null) { $sets[] = 'is_active = ?'; $params[] = $isActive; }

    if (count($sets) === 0) {
        sendResponse(false, 'Nothing to update', null, 400);
    }

    $sql = 'UPDATE system_settings SET ' . implode(', ', $sets) . ' WHERE ' . ($settingId ? 'setting_id = ?' : 'setting_key = ?');
    $params[] = $settingId ? $settingId : $settingKey;

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    // Return updated record
    $sel = $conn->prepare('SELECT setting_id, setting_key, setting_value, description, is_active, created_at, updated_at FROM system_settings WHERE ' . ($settingId ? 'setting_id = ?' : 'setting_key = ?'));
    $sel->execute([$settingId ? $settingId : $settingKey]);
    $row = $sel->fetch(PDO::FETCH_ASSOC);

    sendResponse(true, 'Setting updated', $row);
} catch (Exception $e) {
    error_log('admin_update_settings error: ' . $e->getMessage());
    sendResponse(false, 'Failed to update setting', null, 500);
}

?>
