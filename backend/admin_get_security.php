<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$conn = getDBConnection();

// Admin auth
if (!isAdminLoggedIn()) {
    sendResponse(false, 'Not authenticated (admin)', null, 401);
}
if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    sendResponse(false, 'Invalid admin session', null, 401);
}

$days = isset($_GET['days']) ? intval($_GET['days']) : 30;
if ($days <= 0) $days = 30;

try {
    // Recent failed login attempts
    $failedSql = "SELECT al.log_id, al.user_id, al.action_type, al.ip_address, al.user_agent, al.created_at, u.email, u.first_name, u.last_name
        FROM audit_logs al
        LEFT JOIN users u ON u.user_id = al.user_id
        WHERE al.action_type = 'LOGIN_FAILED' AND al.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ORDER BY al.created_at DESC LIMIT 200";
    $stmt = $conn->prepare($failedSql);
    $stmt->execute([$days]);
    $failed = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Suspicious IPs (IPs with multiple failures)
    $ipSql = "SELECT ip_address, COUNT(*) as failures, MAX(created_at) as last_seen
        FROM audit_logs
        WHERE action_type = 'LOGIN_FAILED' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        GROUP BY ip_address
        HAVING failures > 2
        ORDER BY failures DESC, last_seen DESC
        LIMIT 50";
    $ipStmt = $conn->prepare($ipSql);
    $ipStmt->execute([$days]);
    $suspiciousIps = $ipStmt->fetchAll(PDO::FETCH_ASSOC);

    // Active sessions (recent activity)
    $sessSql = "SELECT session_id, user_id, ip_address, user_agent, last_activity, expires_at
        FROM sessions
        WHERE last_activity >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ORDER BY last_activity DESC
        LIMIT 200";
    $sessStmt = $conn->prepare($sessSql);
    $sessStmt->execute([$days]);
    $sessions = $sessStmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent audit events (top 200)
    $auditSql = "SELECT log_id, user_id, action_type, table_name, record_id, ip_address, user_agent, created_at
        FROM audit_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ORDER BY created_at DESC
        LIMIT 200";
    $auditStmt = $conn->prepare($auditSql);
    $auditStmt->execute([$days]);
    $audits = $auditStmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [
        'failed_logins' => $failed,
        'suspicious_ips' => $suspiciousIps,
        'active_sessions' => $sessions,
        'recent_audit' => $audits,
        'days' => $days
    ];

    sendResponse(true, 'Security data loaded', $data);

} catch (Exception $e) {
    error_log('admin_get_security error: ' . $e->getMessage());
    sendResponse(false, 'Failed to load security data', null, 500);
}

?>
