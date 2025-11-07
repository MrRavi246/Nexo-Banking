<?php
// Start output buffering to prevent any output before JSON
ob_start();

require_once 'config.php';
require_once 'functions.php';

// Clear any output that might have occurred
ob_clean();

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    sendResponse(false, 'Not authenticated', null, 401);
}

try {
    $conn = getDBConnection();
    
    // Validate session
    if (!validateSession($conn, $_SESSION['user_id'], $_SESSION['session_token'])) {
        sendResponse(false, 'Invalid session', null, 401);
    }
    
    $userId = $_SESSION['user_id'];
    
    // Get user information
    $stmt = $conn->prepare("
        SELECT 
            user_id,
            username,
            email,
            first_name,
            last_name,
            phone_number,
            date_of_birth,
            address,
            profile_image,
            member_type,
            status,
            created_at,
            last_login
        FROM users 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        sendResponse(false, 'User not found', null, 404);
    }
    
    // Get user accounts with balances
    $stmt = $conn->prepare("
        SELECT 
            account_id,
            account_type,
            account_number,
            balance,
            currency,
            status,
            credit_limit,
            interest_rate,
            last_activity
        FROM accounts 
        WHERE user_id = ? AND status = 'active'
        ORDER BY account_type
    ");
    $stmt->execute([$userId]);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total balance
    $totalBalance = 0;
    $checkingBalance = 0;
    $savingsBalance = 0;
    $creditBalance = 0;
    $checkingAccount = null;
    $savingsAccount = null;
    $creditAccount = null;
    
    foreach ($accounts as $account) {
        if ($account['account_type'] === 'checking') {
            $checkingBalance = $account['balance'];
            $checkingAccount = $account['account_number'];
        } elseif ($account['account_type'] === 'savings') {
            $savingsBalance = $account['balance'];
            $savingsAccount = $account['account_number'];
        } elseif ($account['account_type'] === 'credit') {
            $creditBalance = $account['balance'];
            $creditAccount = $account['account_number'];
        }
        
        // Don't include credit card debt in total balance
        if ($account['account_type'] !== 'credit') {
            $totalBalance += $account['balance'];
        }
    }
    
    // Get recent transactions (last 10)
    $stmt = $conn->prepare("
        SELECT 
            t.transaction_id,
            t.transaction_type,
            t.amount,
            t.description,
            t.category,
            t.status,
            t.transaction_date,
            a.account_type
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        WHERE a.user_id = ?
        ORDER BY t.transaction_date DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get savings goals
    $stmt = $conn->prepare("
        SELECT 
            goal_id,
            goal_name,
            target_amount,
            current_amount,
            target_date,
            category,
            status
        FROM savings_goals 
        WHERE user_id = ? AND status = 'active'
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $savingsGoals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get spending by category (current month)
    $stmt = $conn->prepare("
        SELECT 
            t.category,
            SUM(ABS(t.amount)) as total_spent
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        WHERE a.user_id = ? 
        AND t.transaction_type IN ('withdrawal', 'payment', 'transfer')
        AND MONTH(t.transaction_date) = MONTH(CURRENT_DATE())
        AND YEAR(t.transaction_date) = YEAR(CURRENT_DATE())
        AND t.category IS NOT NULL
        GROUP BY t.category
        ORDER BY total_spent DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $spendingByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total spending this month
    $stmt = $conn->prepare("
        SELECT 
            SUM(ABS(t.amount)) as total_spent
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        WHERE a.user_id = ? 
        AND t.transaction_type IN ('withdrawal', 'payment', 'transfer')
        AND MONTH(t.transaction_date) = MONTH(CURRENT_DATE())
        AND YEAR(t.transaction_date) = YEAR(CURRENT_DATE())
    ");
    $stmt->execute([$userId]);
    $monthlySpending = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get unread notifications count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread_count
        FROM notifications 
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->execute([$userId]);
    $notificationCount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get credit score (latest) - Optional, won't fail if table doesn't exist
    $creditScore = null;
    $scoreChange = 0;
    $scoreStatus = 'excellent';
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                score,
                score_date,
                previous_score,
                factors
            FROM credit_scores 
            WHERE user_id = ? 
            ORDER BY score_date DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $creditScore = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculate credit score change
        if ($creditScore) {
            $scoreChange = $creditScore['previous_score'] ? ($creditScore['score'] - $creditScore['previous_score']) : 0;
            
            // Determine score status
            if ($creditScore['score'] >= 740) {
                $scoreStatus = 'excellent';
            } elseif ($creditScore['score'] >= 670) {
                $scoreStatus = 'good';
            } elseif ($creditScore['score'] >= 580) {
                $scoreStatus = 'fair';
            } else {
                $scoreStatus = 'poor';
            }
        }
    } catch (PDOException $e) {
        // Credit score table might not exist, use defaults
        error_log("Credit score query failed: " . $e->getMessage());
        $creditScore = null;
    }
    
    // Prepare response data
    $responseData = [
        'user' => [
            'id' => $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'full_name' => $user['first_name'] . ' ' . $user['last_name'],
            'phone_number' => $user['phone_number'],
            'date_of_birth' => $user['date_of_birth'],
            'address' => $user['address'],
            'profile_image' => $user['profile_image'] ? '../../' . $user['profile_image'] : 'https://i.pravatar.cc/150?u=' . $user['user_id'],
            'member_type' => $user['member_type'],
            'member_type_display' => ucfirst($user['member_type']) . ' Member',
            'status' => $user['status'],
            'created_at' => $user['created_at'],
            'last_login' => $user['last_login']
        ],
        'accounts' => [
            'total_balance' => number_format($totalBalance, 2),
            'checking' => [
                'balance' => number_format($checkingBalance, 2),
                'account_number' => $checkingAccount ? '**** ' . substr($checkingAccount, -4) : 'N/A'
            ],
            'savings' => [
                'balance' => number_format($savingsBalance, 2),
                'account_number' => $savingsAccount ? '**** ' . substr($savingsAccount, -4) : 'N/A'
            ],
            'credit' => [
                'balance' => number_format($creditBalance, 2),
                'account_number' => $creditAccount ? '**** ' . substr($creditAccount, -4) : 'N/A',
                'credit_limit' => '15,000' // Default for now
            ],
            'all_accounts' => $accounts
        ],
        'transactions' => $transactions,
        'savings_goals' => $savingsGoals,
        'spending' => [
            'by_category' => $spendingByCategory,
            'total_month' => $monthlySpending['total_spent'] ?? 0,
            'budget' => 4500, // Default budget
            'percentage' => $monthlySpending['total_spent'] ? round(($monthlySpending['total_spent'] / 4500) * 100, 0) : 0
        ],
        'notifications' => [
            'unread_count' => $notificationCount['unread_count'] ?? 0
        ],
        'credit_score' => [
            'score' => $creditScore['score'] ?? 742, // Default score if none exists
            'status' => $scoreStatus,
            'change' => $scoreChange,
            'date' => $creditScore['score_date'] ?? date('Y-m-d'),
            'factors' => $creditScore['factors'] ? json_decode($creditScore['factors'], true) : null
        ]
    ];
    
    sendResponse(true, 'User data retrieved successfully', $responseData);
    
} catch (Exception $e) {
    error_log("Error in get_user_data.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Send more detailed error in development
    $errorMessage = 'An error occurred while fetching user data';
    if (defined('DEBUG') && DEBUG) {
        $errorMessage .= ': ' . $e->getMessage();
    }
    sendResponse(false, $errorMessage);
}
?>