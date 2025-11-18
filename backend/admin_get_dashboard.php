<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    
    // Admin auth
    if (!isAdminLoggedIn()) {
        sendResponse(false, 'Not authenticated (admin)', null, 401);
        exit;
    }
    if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
        sendResponse(false, 'Invalid admin session', null, 401);
        exit;
    }
} catch (Exception $e) {
    error_log('Dashboard auth error: ' . $e->getMessage());
    sendResponse(false, 'Authentication error: ' . $e->getMessage(), null, 500);
    exit;
}

try {
    // Get total users count
    $stmt = $conn->query("SELECT COUNT(*) as total_users FROM users");
    $totalUsers = intval($stmt->fetch(PDO::FETCH_ASSOC)['total_users']);
    
    // Get total users last month for comparison
    $stmt = $conn->query("SELECT COUNT(*) as total_users_last_month FROM users WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $totalUsersLastMonth = intval($stmt->fetch(PDO::FETCH_ASSOC)['total_users_last_month']);
    
    // Calculate user growth percentage
    $userGrowth = 0;
    if ($totalUsersLastMonth > 0) {
        $userGrowth = (($totalUsers - $totalUsersLastMonth) / $totalUsersLastMonth) * 100;
    } elseif ($totalUsers > $totalUsersLastMonth) {
        $userGrowth = 100;
    }
    
    // Get total transaction amount for current month
    $stmt = $conn->query("
        SELECT COALESCE(SUM(amount), 0) as total_transactions 
        FROM transactions 
        WHERE status = 'completed' 
        AND MONTH(transaction_date) = MONTH(NOW()) 
        AND YEAR(transaction_date) = YEAR(NOW())
    ");
    $totalTransactions = floatval($stmt->fetch(PDO::FETCH_ASSOC)['total_transactions']);
    
    // Get total transaction amount for last month
    $stmt = $conn->query("
        SELECT COALESCE(SUM(amount), 0) as total_transactions_last_month 
        FROM transactions 
        WHERE status = 'completed' 
        AND MONTH(transaction_date) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) 
        AND YEAR(transaction_date) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))
    ");
    $totalTransactionsLastMonth = floatval($stmt->fetch(PDO::FETCH_ASSOC)['total_transactions_last_month']);
    
    // Calculate transaction growth percentage
    $transactionGrowth = 0;
    if ($totalTransactionsLastMonth > 0) {
        $transactionGrowth = (($totalTransactions - $totalTransactionsLastMonth) / $totalTransactionsLastMonth) * 100;
    } elseif ($totalTransactions > 0) {
        $transactionGrowth = 100;
    }
    
    // Get active accounts count
    $stmt = $conn->query("SELECT COUNT(*) as active_accounts FROM accounts WHERE status = 'active'");
    $activeAccounts = intval($stmt->fetch(PDO::FETCH_ASSOC)['active_accounts']);
    
    // Get active accounts last month
    $stmt = $conn->query("SELECT COUNT(*) as active_accounts_last_month FROM accounts WHERE status = 'active' AND created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $activeAccountsLastMonth = intval($stmt->fetch(PDO::FETCH_ASSOC)['active_accounts_last_month']);
    
    // Calculate account growth percentage
    $accountGrowth = 0;
    if ($activeAccountsLastMonth > 0) {
        $accountGrowth = (($activeAccounts - $activeAccountsLastMonth) / $activeAccountsLastMonth) * 100;
    } elseif ($activeAccounts > 0) {
        $accountGrowth = 100;
    }
    
    // Calculate monthly revenue (fees from transactions - simplified calculation)
    $stmt = $conn->query("
        SELECT COALESCE(SUM(amount), 0) as monthly_revenue 
        FROM transactions 
        WHERE transaction_type = 'fee' 
        AND status = 'completed' 
        AND MONTH(transaction_date) = MONTH(NOW()) 
        AND YEAR(transaction_date) = YEAR(NOW())
    ");
    $monthlyRevenue = floatval($stmt->fetch(PDO::FETCH_ASSOC)['monthly_revenue']);
    
    // Get monthly revenue last month
    $stmt = $conn->query("
        SELECT COALESCE(SUM(amount), 0) as monthly_revenue_last_month 
        FROM transactions 
        WHERE transaction_type = 'fee' 
        AND status = 'completed' 
        AND MONTH(transaction_date) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) 
        AND YEAR(transaction_date) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))
    ");
    $monthlyRevenueLastMonth = floatval($stmt->fetch(PDO::FETCH_ASSOC)['monthly_revenue_last_month']);
    
    // Calculate revenue growth percentage
    $revenueGrowth = 0;
    if ($monthlyRevenueLastMonth > 0) {
        $revenueGrowth = (($monthlyRevenue - $monthlyRevenueLastMonth) / $monthlyRevenueLastMonth) * 100;
    } elseif ($monthlyRevenue > 0) {
        $revenueGrowth = 100;
    }
    
    // Get recent activities from audit logs
    $recentActivities = [];
    $formattedActivities = [];
    
    try {
        $stmt = $conn->query("
            SELECT 
                al.action_type,
                al.table_name,
                al.created_at,
                al.ip_address,
                u.username,
                u.first_name,
                u.last_name
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.user_id
            ORDER BY al.created_at DESC
            LIMIT 10
        ");
        $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format recent activities
        foreach ($recentActivities as $activity) {
            $actorName = 'System';
            if (!empty($activity['username'])) {
                $actorName = trim($activity['first_name'] . ' ' . $activity['last_name']);
                if (empty($actorName)) {
                    $actorName = $activity['username'];
                }
            }
            
            $formattedActivities[] = [
                'actor' => $actorName,
                'action' => $activity['action_type'],
                'table' => $activity['table_name'] ?? '',
                'timestamp' => $activity['created_at'],
                'ip_address' => $activity['ip_address'] ?? ''
            ];
        }
    } catch (Exception $e) {
        error_log('Error fetching audit logs: ' . $e->getMessage());
        // Continue with empty activities
    }
    
    // Get pending users count
    $pendingUsers = 0;
    try {
        $stmt = $conn->query("SELECT COUNT(*) as pending_users FROM users WHERE status = 'pending'");
        $pendingUsers = $stmt->fetch(PDO::FETCH_ASSOC)['pending_users'];
    } catch (Exception $e) {
        error_log('Error fetching pending users: ' . $e->getMessage());
    }
    
    // Get pending loan applications count (table may not exist)
    $pendingLoans = 0;
    try {
        $stmt = $conn->query("SELECT COUNT(*) as pending_loans FROM loan_applications WHERE status = 'pending'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $pendingLoans = $result['pending_loans'] ?? 0;
    } catch (Exception $e) {
        // Table doesn't exist, that's okay
        error_log('Loan applications table does not exist: ' . $e->getMessage());
    }
    
    // Get user status breakdown
    $stmt = $conn->query("
        SELECT status, COUNT(*) as count 
        FROM users 
        GROUP BY status
    ");
    $userStatusBreakdown = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $userStatusBreakdown[$row['status']] = $row['count'];
    }
    
    $data = [
        'total_users' => intval($totalUsers),
        'user_growth' => round($userGrowth, 1),
        'total_transactions' => round($totalTransactions, 2),
        'transaction_growth' => round($transactionGrowth, 1),
        'active_accounts' => intval($activeAccounts),
        'account_growth' => round($accountGrowth, 1),
        'monthly_revenue' => round($monthlyRevenue, 2),
        'revenue_growth' => round($revenueGrowth, 1),
        'recent_activities' => $formattedActivities,
        'pending_users' => intval($pendingUsers),
        'pending_loans' => intval($pendingLoans),
        'user_status_breakdown' => $userStatusBreakdown,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    sendResponse(true, 'Dashboard data retrieved', $data);

} catch (Exception $e) {
    error_log('admin_get_dashboard error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    sendResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
?>
