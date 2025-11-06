<?php
require_once 'config.php';

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate password strength
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return $errors;
}

/**
 * Generate unique account number
 */
function generateAccountNumber($conn) {
    do {
        $accountNumber = '10' . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        $stmt = $conn->prepare("SELECT account_id FROM accounts WHERE account_number = ?");
        $stmt->execute([$accountNumber]);
    } while ($stmt->rowCount() > 0);
    
    return $accountNumber;
}

/**
 * Generate session token
 */
function generateSessionToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Handle file upload
 */
function handleFileUpload($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error');
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File size exceeds maximum allowed size');
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, ALLOWED_EXTENSIONS)) {
        throw new Exception('Invalid file type. Only JPG, PNG, and GIF files are allowed');
    }
    
    $fileName = uniqid('profile_', true) . '.' . $fileExtension;
    $filePath = UPLOAD_DIR . $fileName;
    
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    return 'uploads/profiles/' . $fileName;
}

/**
 * Log audit action
 */
function logAudit($conn, $userId, $actionType, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO audit_logs 
            (user_id, action_type, table_name, record_id, old_values, new_values, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $actionType,
            $tableName,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        error_log("Audit Log Error: " . $e->getMessage());
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['session_token']);
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_session_token']);
}

/**
 * Validate session
 */
function validateSession($conn, $userId, $sessionToken) {
    $stmt = $conn->prepare("
        SELECT session_id, expires_at 
        FROM sessions 
        WHERE user_id = ? AND session_token = ? AND expires_at > NOW()
    ");
    $stmt->execute([$userId, $sessionToken]);
    $session = $stmt->fetch();
    
    if ($session) {
        // Update last activity
        $updateStmt = $conn->prepare("UPDATE sessions SET last_activity = NOW() WHERE session_id = ?");
        $updateStmt->execute([$session['session_id']]);
        return true;
    }
    
    return false;
}

/**
 * Validate admin session
 */
function validateAdminSession($conn, $adminId, $sessionToken) {
    $stmt = $conn->prepare("
        SELECT session_id, expires_at 
        FROM admin_sessions 
        WHERE admin_id = ? AND session_token = ? AND expires_at > NOW()
    ");
    $stmt->execute([$adminId, $sessionToken]);
    $session = $stmt->fetch();
    
    if ($session) {
        // Update last activity
        $updateStmt = $conn->prepare("UPDATE admin_sessions SET last_activity = NOW() WHERE session_id = ?");
        $updateStmt->execute([$session['session_id']]);
        return true;
    }
    
    return false;
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Send JSON response
 */
function sendResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

/**
 * Create notification
 */
function createNotification($conn, $userId, $title, $message, $type = 'system_maintenance') {
    try {
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, type) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $title, $message, $type]);
    } catch (Exception $e) {
        error_log("Notification Error: " . $e->getMessage());
    }
}
?>
