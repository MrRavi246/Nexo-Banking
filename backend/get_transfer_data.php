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

    // Get user's accounts
    $stmt = $conn->prepare("
        SELECT 
            account_id, 
            account_type, 
            account_number, 
            balance, 
            currency,
            status
        FROM accounts 
        WHERE user_id = ? AND status = 'active'
        ORDER BY account_type
    ");
    $stmt->execute([$userId]);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get saved beneficiaries/contacts
    $contacts = [];
    try {
        $stmt = $conn->prepare("
            SELECT 
                beneficiary_id, 
                beneficiary_name, 
                account_number, 
                bank_name, 
                email,
                phone_number
            FROM beneficiaries 
            WHERE user_id = ? AND status = 'active'
            ORDER BY beneficiary_name
        ");
        $stmt->execute([$userId]);
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Beneficiaries table might not exist, get other Nexo users instead
        error_log("Beneficiaries query failed: " . $e->getMessage());
        
        // Get other active users as potential internal transfer recipients
        $stmt = $conn->prepare("
            SELECT 
                user_id as beneficiary_id,
                CONCAT(first_name, ' ', last_name) as beneficiary_name,
                email,
                'Nexo Banking' as bank_name,
                '' as account_number
            FROM users 
            WHERE user_id != ? AND status = 'active'
            ORDER BY first_name, last_name
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get recent transfer recipients (from transaction history)
    $recentRecipients = [];
    try {
        $stmt = $conn->prepare("
            SELECT DISTINCT
                recipient_name,
                recipient_account,
                COUNT(*) as transfer_count,
                MAX(transaction_date) as last_transfer
            FROM transactions
            WHERE account_id IN (SELECT account_id FROM accounts WHERE user_id = ?)
            AND transaction_type = 'transfer'
            AND recipient_name IS NOT NULL
            GROUP BY recipient_name, recipient_account
            ORDER BY last_transfer DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $recentRecipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Recent recipients query failed: " . $e->getMessage());
    }

    $responseData = [
        'accounts' => $accounts,
        'contacts' => $contacts,
        'recent_recipients' => $recentRecipients
    ];

    sendResponse(true, 'Transfer data retrieved successfully', $responseData);

} catch (Exception $e) {
    error_log("Error in get_transfer_data.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    sendResponse(false, 'An error occurred while fetching transfer data');
}
