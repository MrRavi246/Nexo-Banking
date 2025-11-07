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

    // Get recent transfers (last 10)
    $stmt = $conn->prepare("
        SELECT 
            t.transaction_id,
            t.transaction_type,
            t.amount,
            t.description,
            t.recipient_name,
            t.recipient_account,
            t.status,
            t.transaction_date,
            t.created_at,
            a.account_type,
            a.account_number
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        WHERE a.user_id = ?
        AND t.transaction_type IN ('transfer', 'deposit')
        ORDER BY t.created_at DESC, t.transaction_id DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the transfers for display
    $formattedTransfers = [];
    foreach ($transfers as $transfer) {
        // Determine if sent or received
        $isSent = $transfer['amount'] < 0;
        
        // Format date/time
        $date = new DateTime($transfer['created_at']);
        $now = new DateTime();
        $interval = $now->diff($date);
        
        if ($interval->days == 0) {
            if ($interval->h == 0) {
                $timeAgo = $interval->i . ' minutes ago';
            } else {
                $timeAgo = 'Today • ' . $date->format('g:i A');
            }
        } elseif ($interval->days == 1) {
            $timeAgo = 'Yesterday • ' . $date->format('g:i A');
        } else {
            $timeAgo = $date->format('M j') . ' • ' . $date->format('g:i A');
        }
        
        // Get recipient/sender name
        $displayName = $transfer['recipient_name'] ?? 'Unknown';
        if ($transfer['transaction_type'] === 'deposit') {
            $displayName = 'Deposit';
        }
        
        // Determine transfer type icon
        $transferType = 'internal'; // Default
        if (stripos($transfer['description'], 'external') !== false) {
            $transferType = 'external';
        } elseif (stripos($transfer['description'], 'wire') !== false) {
            $transferType = 'wire';
        }
        
        $formattedTransfers[] = [
            'id' => $transfer['transaction_id'],
            'name' => $displayName,
            'amount' => abs($transfer['amount']),
            'is_sent' => $isSent,
            'status' => $transfer['status'],
            'time' => $timeAgo,
            'date' => $transfer['transaction_date'],
            'type' => $transferType,
            'account' => $transfer['recipient_account'] ? '**** ' . substr($transfer['recipient_account'], -4) : '',
            'description' => $transfer['description']
        ];
    }

    sendResponse(true, 'Recent transfers retrieved successfully', [
        'transfers' => $formattedTransfers
    ]);

} catch (Exception $e) {
    error_log("Error in get_recent_transfers.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    sendResponse(false, 'An error occurred while fetching recent transfers');
}
