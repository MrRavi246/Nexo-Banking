<?php
/**
 * Get Bill Payment Data
 * Returns user accounts, saved billers, and recent payments
 */

header('Content-Type: application/json');
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

try {
    $conn = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    // Validate session
    if (!validateSession($conn, $userId, $_SESSION['session_token'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid session']);
        exit();
    }
    
    // Get user's active accounts
    $stmt = $conn->prepare("
        SELECT account_id, account_type, account_number, balance, currency
        FROM accounts 
        WHERE user_id = ? AND status = 'active'
        ORDER BY account_type
    ");
    $stmt->execute([$userId]);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get saved billers (unique biller names from past payments)
    $stmt = $conn->prepare("
        SELECT DISTINCT biller_name, bill_type
        FROM bill_payments 
        WHERE user_id = ? 
        GROUP BY biller_name, bill_type
        ORDER BY MAX(created_at) DESC
        LIMIT 20
    ");
    $stmt->execute([$userId]);
    $savedBillers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent bill payments
    $stmt = $conn->prepare("
        SELECT 
            bp.payment_id,
            bp.biller_name,
            bp.bill_type,
            bp.amount,
            bp.due_date,
            bp.payment_date,
            bp.status,
            bp.reference_number,
            a.account_type,
            a.account_number
        FROM bill_payments bp
        JOIN accounts a ON bp.account_id = a.account_id
        WHERE bp.user_id = ?
        ORDER BY bp.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $recentPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get upcoming bills (scheduled payments)
    $stmt = $conn->prepare("
        SELECT 
            bp.payment_id,
            bp.biller_name,
            bp.bill_type,
            bp.amount,
            bp.due_date,
            bp.status,
            DATEDIFF(bp.due_date, CURDATE()) as days_until_due
        FROM bill_payments bp
        WHERE bp.user_id = ? 
        AND bp.status = 'scheduled' 
        AND bp.due_date >= CURDATE()
        ORDER BY bp.due_date ASC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $upcomingBills = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total due
    $totalDue = 0;
    $billsDueCount = 0;
    foreach ($upcomingBills as $bill) {
        $totalDue += $bill['amount'];
        $billsDueCount++;
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'accounts' => $accounts,
            'savedBillers' => $savedBillers,
            'recentPayments' => $recentPayments,
            'upcomingBills' => $upcomingBills,
            'summary' => [
                'totalDue' => $totalDue,
                'billsDueCount' => $billsDueCount,
                'nextDueDate' => $upcomingBills[0]['due_date'] ?? null,
                'nextDueBiller' => $upcomingBills[0]['biller_name'] ?? null,
                'nextDueAmount' => $upcomingBills[0]['amount'] ?? 0
            ]
        ]
    ]);
    exit();
    
} catch (PDOException $e) {
    error_log("Get bill data error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred'
    ]);
    exit();
}
