<?php
/**
 * Simple Bill Data API - Minimal Version
 */

// Direct database connection
$conn = new PDO("mysql:host=localhost;dbname=nexo_banking;charset=utf8mb4", 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    // Get accounts
    $stmt = $conn->prepare("SELECT account_id, account_type, account_number, balance FROM accounts WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$userId]);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get saved billers
    $stmt = $conn->prepare("SELECT DISTINCT biller_name, bill_type FROM bill_payments WHERE user_id = ? LIMIT 10");
    $stmt->execute([$userId]);
    $billers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get upcoming bills
    $stmt = $conn->prepare("SELECT payment_id, biller_name, amount, due_date, status FROM bill_payments WHERE user_id = ? AND status = 'scheduled' ORDER BY due_date ASC LIMIT 10");
    $stmt->execute([$userId]);
    $upcoming = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent payments
    $stmt = $conn->prepare("SELECT payment_id, biller_name, amount, payment_date, status FROM bill_payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$userId]);
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'accounts' => $accounts,
            'savedBillers' => $billers,
            'upcomingBills' => $upcoming,
            'recentPayments' => $recent,
            'summary' => [
                'totalDue' => 0,
                'billsDueCount' => count($upcoming),
                'nextDueDate' => $upcoming[0]['due_date'] ?? null,
                'nextDueBiller' => $upcoming[0]['biller_name'] ?? null,
                'nextDueAmount' => $upcoming[0]['amount'] ?? 0
            ]
        ]
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit();
