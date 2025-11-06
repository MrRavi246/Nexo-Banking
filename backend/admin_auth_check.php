<?php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect(ADMIN_LOGIN_URL);
}

// Validate admin session
$conn = getDBConnection();
if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    session_unset();
    session_destroy();
    redirect(ADMIN_LOGIN_URL);
}

// Check if admin account is still active
$stmt = $conn->prepare("SELECT status FROM admin_users WHERE admin_id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

if (!$admin || $admin['status'] !== 'active') {
    session_unset();
    session_destroy();
    redirect(ADMIN_LOGIN_URL);
}
?>
