<?php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method', null, 405);
}

try {
    $conn = getDBConnection();
    
    // Collect and sanitize input
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember']) && $_POST['remember'] === 'on';
    
    // Validation
    if (empty($username)) {
        sendResponse(false, 'Username is required', null, 400);
    }
    
    if (empty($password)) {
        sendResponse(false, 'Password is required', null, 400);
    }
    
    // Get admin by username
    $stmt = $conn->prepare("
        SELECT admin_id, username, email, password_hash, first_name, last_name, role, status
        FROM admin_users
        WHERE username = ? OR email = ?
        LIMIT 1
    ");
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        sendResponse(false, 'Invalid username or password', null, 401);
    }
    
    // Check admin status
    if ($admin['status'] !== 'active') {
        sendResponse(false, 'Your admin account is inactive. Please contact the system administrator.', null, 403);
    }
    
    // Verify password
    if (!password_verify($password, $admin['password_hash'])) {
        sendResponse(false, 'Invalid username or password', null, 401);
    }
    
    // Generate session token
    $sessionToken = generateSessionToken();
    $expiresAt = $rememberMe 
        ? date('Y-m-d H:i:s', time() + REMEMBER_ME_LIFETIME)
        : date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
    
    // Create session in database
    $stmt = $conn->prepare("
        INSERT INTO admin_sessions 
        (admin_id, session_token, ip_address, user_agent, expires_at) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $admin['admin_id'],
        $sessionToken,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $expiresAt
    ]);
    
    // Update last login
    $stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE admin_id = ?");
    $stmt->execute([$admin['admin_id']]);
    
    // Set session variables
    $_SESSION['admin_id'] = $admin['admin_id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_first_name'] = $admin['first_name'];
    $_SESSION['admin_last_name'] = $admin['last_name'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['admin_session_token'] = $sessionToken;
    
    sendResponse(true, 'Login successful', [
        'redirect_url' => ADMIN_DASHBOARD_URL,
        'admin' => [
            'admin_id' => $admin['admin_id'],
            'username' => $admin['username'],
            'first_name' => $admin['first_name'],
            'last_name' => $admin['last_name'],
            'role' => $admin['role']
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Admin Login Error: " . $e->getMessage());
    sendResponse(false, 'An error occurred during login. Please try again later.', null, 500);
}
?>
