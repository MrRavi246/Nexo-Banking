<?php
/**
 * Simple Bill Payment Test - Direct Testing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../backend/config.php';
require_once '../../backend/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    die("Please login first at <a href='../auth/login.php'>Login Page</a>");
}

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

echo "<h1>Simple Bill Payment Test</h1>";
echo "<p>User ID: $userId</p>";
echo "<hr>";

// Test 1: Direct include of get_bill_data.php logic
echo "<h2>Test 1: Get Bill Data (Direct)</h2>";
try {
    // Get user's active accounts
    $stmt = $conn->prepare("
        SELECT account_id, account_type, account_number, balance, currency
        FROM accounts 
        WHERE user_id = ? AND status = 'active'
        ORDER BY account_type
    ");
    $stmt->execute([$userId]);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Accounts loaded: " . count($accounts) . "<br>";
    foreach ($accounts as $acc) {
        echo "  - {$acc['account_type']} (*{$acc['account_number']}) - \${$acc['balance']}<br>";
    }
    
    // Get saved billers
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
    
    echo "✅ Saved billers: " . count($savedBillers) . "<br>";
    
    // Get recent payments
    $stmt = $conn->prepare("
        SELECT 
            bp.payment_id,
            bp.biller_name,
            bp.bill_type,
            bp.amount,
            bp.status
        FROM bill_payments bp
        WHERE bp.user_id = ?
        ORDER BY bp.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $recentPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Recent payments: " . count($recentPayments) . "<br>";
    
    // Get upcoming bills
    $stmt = $conn->prepare("
        SELECT 
            bp.payment_id,
            bp.biller_name,
            bp.amount,
            bp.due_date,
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
    
    echo "✅ Upcoming bills: " . count($upcomingBills) . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 2: Create a test payment directly
echo "<h2>Test 2: Create Test Payment (Direct)</h2>";
if (count($accounts) > 0) {
    try {
        $testAccount = $accounts[0];
        $billerName = 'Direct Test Utility';
        $amount = 25.00;
        $paymentDate = date('Y-m-d', strtotime('+5 days'));
        
        // Generate reference number
        $referenceNumber = 'BP' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 8));
        
        // Insert payment
        $stmt = $conn->prepare("
            INSERT INTO bill_payments (
                user_id,
                account_id,
                biller_name,
                bill_type,
                amount,
                due_date,
                payment_date,
                status,
                reference_number
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled', ?)
        ");
        
        $result = $stmt->execute([
            $userId,
            $testAccount['account_id'],
            $billerName,
            'utilities',
            $amount,
            $paymentDate,
            $paymentDate,
            $referenceNumber
        ]);
        
        if ($result) {
            $paymentId = $conn->lastInsertId();
            echo "✅ Payment created successfully<br>";
            echo "  - Payment ID: $paymentId<br>";
            echo "  - Reference: $referenceNumber<br>";
            echo "  - Biller: $billerName<br>";
            echo "  - Amount: \$$amount<br>";
            echo "  - Date: $paymentDate<br>";
            echo "  - Status: scheduled<br>";
        } else {
            echo "❌ Failed to create payment<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Error creating payment: " . $e->getMessage() . "<br>";
    }
} else {
    echo "⚠️ No accounts available<br>";
}

// Test 3: Verify payment exists
echo "<h2>Test 3: Verify Payment</h2>";
$stmt = $conn->prepare("
    SELECT * FROM bill_payments 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->execute([$userId]);
$lastPayment = $stmt->fetch(PDO::FETCH_ASSOC);

if ($lastPayment) {
    echo "✅ Latest payment found<br>";
    echo "  - ID: {$lastPayment['payment_id']}<br>";
    echo "  - Biller: {$lastPayment['biller_name']}<br>";
    echo "  - Amount: \${$lastPayment['amount']}<br>";
    echo "  - Status: {$lastPayment['status']}<br>";
} else {
    echo "⚠️ No payments found<br>";
}

echo "<hr>";
echo "<h2>✅ All Direct Tests Complete!</h2>";
echo "<p><a href='pay-bills.php'>Go to Pay Bills Page</a></p>";
?>

<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #1a1a1a; color: #fff; }
    h1 { color: #7ef29b; }
    h2 { color: #eb7ef2; margin-top: 30px; }
    hr { border: 1px solid #333; margin: 30px 0; }
    a { color: #7ef29b; }
</style>
