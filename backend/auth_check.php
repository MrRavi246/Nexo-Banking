<?php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect(LOGIN_URL);
}

// Validate session
$conn = getDBConnection();
if (!validateSession($conn, $_SESSION['user_id'], $_SESSION['session_token'])) {
    session_unset();
    session_destroy();
    redirect(LOGIN_URL);
}

// Check if user account is still active
$stmt = $conn->prepare("SELECT status FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['status'] !== 'active') {
    session_unset();
    session_destroy();
    redirect(LOGIN_URL);
}
?>
