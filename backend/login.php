<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method', null, 405);
}

try {
    $conn = getDBConnection();
    
    // Collect and sanitize input
    $accountNumber = sanitizeInput($_POST['account_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember']) && $_POST['remember'] === 'on';
    
    // Validation
    if (empty($accountNumber)) {
        sendResponse(false, 'Account number or username is required', null, 400);
    }
    
    if (empty($password)) {
        sendResponse(false, 'Password is required', null, 400);
    }
    
    // First, try to authenticate as admin (check if input looks like username/email)
    $isAdmin = false;
    $admin = null;
    
    // Check if it's NOT a numeric account number (likely username or email)
    if (!is_numeric($accountNumber) || strpos($accountNumber, '@') !== false) {
        $stmt = $conn->prepare("
            SELECT admin_id, username, email, password_hash, first_name, last_name, role, status
            FROM admin_users
            WHERE username = ? OR email = ?
            LIMIT 1
        ");
        $stmt->execute([$accountNumber, $accountNumber]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            $isAdmin = true;
        }
    }
    
    // If not admin, try regular user login
    if (!$isAdmin) {
        // Get user by account number
        $stmt = $conn->prepare("
            SELECT u.user_id, u.username, u.email, u.password_hash, u.first_name, u.last_name, 
                   u.status, u.profile_image, u.member_type, a.account_id, a.account_number
            FROM users u
            INNER JOIN accounts a ON u.user_id = a.user_id
            WHERE a.account_number = ? AND a.account_type = 'checking'
            LIMIT 1
        ");
        $stmt->execute([$accountNumber]);
        $user = $stmt->fetch();
    } else {
        $user = null;
    }
    
    // Handle Admin Login
    if ($isAdmin && $admin) {
        // Check admin status
        if ($admin['status'] !== 'active') {
            sendResponse(false, 'Your admin account is inactive. Please contact the system administrator.', null, 403);
        }
        
        // Verify admin password
        if (!password_verify($password, $admin['password_hash'])) {
            sendResponse(false, 'Invalid username or password', null, 401);
        }
        
        // Generate session token for admin
        $sessionToken = generateSessionToken();
        $expiresAt = $rememberMe 
            ? date('Y-m-d H:i:s', time() + REMEMBER_ME_LIFETIME)
            : date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        
        // Create admin session in database
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
        
        // Set admin session variables
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_first_name'] = $admin['first_name'];
        $_SESSION['admin_last_name'] = $admin['last_name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_session_token'] = $sessionToken;
        $_SESSION['admin_loggedin'] = true;
        
        sendResponse(true, 'Login successful', [
            'redirect_url' => ADMIN_DASHBOARD_URL,
            'user_type' => 'admin',
            'admin' => [
                'admin_id' => $admin['admin_id'],
                'username' => $admin['username'],
                'first_name' => $admin['first_name'],
                'last_name' => $admin['last_name'],
                'role' => $admin['role']
            ]
        ]);
        
    }
    
    // Handle Regular User Login
    if (!$user) {
        // Log failed login attempt
        logAudit($conn, null, 'LOGIN_FAILED', null, null, null, [
            'account_number' => $accountNumber,
            'reason' => 'Invalid account number'
        ]);
        
        sendResponse(false, 'Invalid account number or password', null, 401);
    }
    
    // Check user status
    if ($user['status'] === 'pending') {
        sendResponse(false, 'Your account is pending approval. Please wait for admin approval.', [
            'status' => 'pending'
        ], 403);
    }
    
    if ($user['status'] === 'rejected') {
        sendResponse(false, 'Your account registration was rejected. Please contact support for more information.', [
            'status' => 'rejected'
        ], 403);
    }
    
    if ($user['status'] === 'suspended') {
        sendResponse(false, 'Your account has been suspended. Please contact support.', [
            'status' => 'suspended'
        ], 403);
    }
    
    if ($user['status'] === 'inactive') {
        sendResponse(false, 'Your account is inactive. Please contact support to reactivate.', [
            'status' => 'inactive'
        ], 403);
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        // Log failed login attempt
        logAudit($conn, $user['user_id'], 'LOGIN_FAILED', null, null, null, [
            'account_number' => $accountNumber,
            'reason' => 'Invalid password'
        ]);
        
        sendResponse(false, 'Invalid account number or password', null, 401);
    }
    
    // Generate session token
    $sessionToken = generateSessionToken();
    $expiresAt = $rememberMe 
        ? date('Y-m-d H:i:s', time() + REMEMBER_ME_LIFETIME)
        : date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
    
    // Create session in database
    $stmt = $conn->prepare("
        INSERT INTO sessions 
        (user_id, session_token, ip_address, user_agent, expires_at) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user['user_id'],
        $sessionToken,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $expiresAt
    ]);
    
    // Update last login
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);
    
    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['member_type'] = $user['member_type'];
    $_SESSION['profile_image'] = $user['profile_image'];
    $_SESSION['session_token'] = $sessionToken;
    $_SESSION['account_number'] = $user['account_number'];
    
    // Log successful login
    logAudit($conn, $user['user_id'], 'LOGIN_SUCCESS', 'users', $user['user_id'], null, [
        'account_number' => $accountNumber
    ]);
    
    // Create welcome notification if first login
    if (empty($user['last_login'])) {
        createNotification(
            $conn,
            $user['user_id'],
            'Welcome to Nexo Banking!',
            'Your account has been successfully activated. Start exploring our features.',
            'system_maintenance'
        );
    }
    
    sendResponse(true, 'Login successful', [
        'redirect_url' => DASHBOARD_URL,
        'user_type' => 'user',
        'user' => [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'member_type' => $user['member_type'],
            'profile_image' => $user['profile_image']
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    sendResponse(false, 'An error occurred during login. Please try again later.', null, 500);
}
?>
