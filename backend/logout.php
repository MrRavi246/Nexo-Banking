<?php
require_once 'config.php';
require_once 'functions.php';

// Destroy session
session_unset();
session_destroy();

// Delete session from database if exists
if (isset($_SESSION['user_id']) && isset($_SESSION['session_token'])) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("DELETE FROM sessions WHERE user_id = ? AND session_token = ?");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);
        
        // Log logout
        logAudit($conn, $_SESSION['user_id'], 'LOGOUT', 'users', $_SESSION['user_id']);
    } catch (Exception $e) {
        error_log("Logout Error: " . $e->getMessage());
    }
}

// Redirect to login page
redirect(LOGIN_URL);
?>
