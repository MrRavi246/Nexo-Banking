<?php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/functions.php';

header('Content-Type: application/json');

// Check admin authentication
if (!isAdminLoggedIn()) {
    sendResponse(false, 'Unauthorized access', null, 401);
}

$conn = getDBConnection();

// Validate admin session
if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    sendResponse(false, 'Session expired', null, 401);
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_pending_users':
            getPendingUsers($conn);
            break;
            
        case 'approve_user':
            approveUser($conn);
            break;
            
        case 'reject_user':
            rejectUser($conn);
            break;
            
        case 'get_all_users':
            getAllUsers($conn);
            break;
            
        default:
            sendResponse(false, 'Invalid action', null, 400);
    }
} catch (Exception $e) {
    error_log("User Management Error: " . $e->getMessage());
    sendResponse(false, 'An error occurred. Please try again.', null, 500);
}

/**
 * Get all pending users
 */
function getPendingUsers($conn) {
    $stmt = $conn->prepare("
        SELECT u.user_id, u.username, u.email, u.first_name, u.last_name, 
               u.phone_number, u.date_of_birth, u.address, u.member_type, 
               u.profile_image, u.created_at, a.account_number
        FROM users u
        LEFT JOIN accounts a ON u.user_id = a.user_id AND a.account_type = 'checking'
        WHERE u.status = 'pending'
        ORDER BY u.created_at DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    sendResponse(true, 'Pending users retrieved successfully', ['users' => $users]);
}

/**
 * Approve user registration
 */
function approveUser($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method', null, 405);
    }
    
    $userId = intval($_POST['user_id'] ?? 0);
    
    if ($userId <= 0) {
        sendResponse(false, 'Invalid user ID', null, 400);
    }
    
    // Get user details
    $stmt = $conn->prepare("SELECT user_id, email, first_name, last_name, status FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        sendResponse(false, 'User not found', null, 404);
    }
    
    if ($user['status'] !== 'pending') {
        sendResponse(false, 'User is not in pending status', null, 400);
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Update user status to active
        $stmt = $conn->prepare("
            UPDATE users 
            SET status = 'active', 
                approved_by = ?, 
                approved_at = NOW() 
            WHERE user_id = ?
        ");
        $stmt->execute([$_SESSION['admin_id'], $userId]);
        
        // Activate user accounts
        $stmt = $conn->prepare("
            UPDATE accounts 
            SET status = 'active' 
            WHERE user_id = ? AND status = 'inactive'
        ");
        $stmt->execute([$userId]);
        
        // Create notification for user
        createNotification(
            $conn,
            $userId,
            'Account Approved!',
            'Congratulations! Your Nexo Banking account has been approved. You can now login and start using all our services.',
            'system_maintenance'
        );
        
        // Log audit
        logAudit($conn, $_SESSION['admin_id'], 'USER_APPROVED', 'users', $userId, 
            ['status' => 'pending'], 
            ['status' => 'active', 'approved_by' => $_SESSION['admin_id']]
        );
        
        $conn->commit();
        
        sendResponse(true, 'User approved successfully', [
            'user_id' => $userId,
            'status' => 'active'
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("User Approval Error: " . $e->getMessage());
        sendResponse(false, 'Failed to approve user', null, 500);
    }
}

/**
 * Reject user registration
 */
function rejectUser($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method', null, 405);
    }
    
    $userId = intval($_POST['user_id'] ?? 0);
    $rejectionReason = sanitizeInput($_POST['rejection_reason'] ?? 'Registration rejected by administrator');
    
    if ($userId <= 0) {
        sendResponse(false, 'Invalid user ID', null, 400);
    }
    
    // Get user details
    $stmt = $conn->prepare("SELECT user_id, email, first_name, last_name, status FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        sendResponse(false, 'User not found', null, 404);
    }
    
    if ($user['status'] !== 'pending') {
        sendResponse(false, 'User is not in pending status', null, 400);
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Update user status to rejected
        $stmt = $conn->prepare("
            UPDATE users 
            SET status = 'rejected', 
                rejection_reason = ?,
                approved_by = ?, 
                approved_at = NOW() 
            WHERE user_id = ?
        ");
        $stmt->execute([$rejectionReason, $_SESSION['admin_id'], $userId]);
        
        // Create notification for user
        createNotification(
            $conn,
            $userId,
            'Account Registration Rejected',
            'We regret to inform you that your account registration has been rejected. Reason: ' . $rejectionReason . '. Please contact support for more information.',
            'security_alert'
        );
        
        // Log audit
        logAudit($conn, $_SESSION['admin_id'], 'USER_REJECTED', 'users', $userId, 
            ['status' => 'pending'], 
            ['status' => 'rejected', 'reason' => $rejectionReason]
        );
        
        $conn->commit();
        
        sendResponse(true, 'User registration rejected', [
            'user_id' => $userId,
            'status' => 'rejected'
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("User Rejection Error: " . $e->getMessage());
        sendResponse(false, 'Failed to reject user', null, 500);
    }
}

/**
 * Get all users with filters
 */
function getAllUsers($conn) {
    $status = $_GET['status'] ?? 'all';
    
    $sql = "
        SELECT u.user_id, u.username, u.email, u.first_name, u.last_name, 
               u.phone_number, u.member_type, u.status, u.created_at, u.last_login,
               COUNT(DISTINCT a.account_id) as account_count
        FROM users u
        LEFT JOIN accounts a ON u.user_id = a.user_id
    ";
    
    if ($status !== 'all') {
        $sql .= " WHERE u.status = ?";
    }
    
    $sql .= " GROUP BY u.user_id ORDER BY u.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    
    if ($status !== 'all') {
        $stmt->execute([$status]);
    } else {
        $stmt->execute();
    }
    
    $users = $stmt->fetchAll();
    
    sendResponse(true, 'Users retrieved successfully', ['users' => $users]);
}
?>
