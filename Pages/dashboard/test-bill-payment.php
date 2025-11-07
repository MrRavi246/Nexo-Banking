<?php
/**
 * Test Bill Payment System
 * Quick diagnostic tool to verify backend functionality
 */

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

echo "<h1>Bill Payment System Test</h1>";
echo "<p>Testing for User ID: $userId</p>";
echo "<hr>";

// Test 1: Check if user has accounts
echo "<h2>Test 1: User Accounts</h2>";
$stmt = $conn->prepare("SELECT * FROM accounts WHERE user_id = ? AND status = 'active'");
$stmt->execute([$userId]);
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($accounts) > 0) {
    echo "✅ Found " . count($accounts) . " active account(s)<br>";
    foreach ($accounts as $acc) {
        echo "  - {$acc['account_type']} (*{$acc['account_number']}) - Balance: \${$acc['balance']}<br>";
    }
} else {
    echo "❌ No active accounts found<br>";
}

// Test 2: Check bill_payments table
echo "<h2>Test 2: Bill Payments Table</h2>";
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bill_payments WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Bill payments table exists<br>";
    echo "  - Total payments: {$result['count']}<br>";
} catch (PDOException $e) {
    echo "❌ Bill payments table error: " . $e->getMessage() . "<br>";
}

// Test 3: Test backend API endpoint
echo "<h2>Test 3: Backend API (/backend/get_bill_data.php)</h2>";

// Determine the correct URL based on your setup
$baseUrl = "http://" . $_SERVER['HTTP_HOST'];
$apiUrl = $baseUrl . "/Nexo-Banking/backend/get_bill_data.php";

echo "  - Testing URL: $apiUrl<br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "✅ API endpoint working<br>";
        echo "  - Accounts: " . count($data['data']['accounts']) . "<br>";
        echo "  - Saved Billers: " . count($data['data']['savedBillers']) . "<br>";
        echo "  - Recent Payments: " . count($data['data']['recentPayments']) . "<br>";
        echo "  - Upcoming Bills: " . count($data['data']['upcomingBills']) . "<br>";
        echo "  - Total Due: \${$data['data']['summary']['totalDue']}<br>";
    } else {
        echo "❌ API returned error: " . ($data['message'] ?? 'Unknown') . "<br>";
    }
} else {
    echo "❌ API request failed (HTTP $httpCode)<br>";
}

// Test 4: Create a test scheduled payment
echo "<h2>Test 4: Create Test Payment</h2>";
if (count($accounts) > 0) {
    $testAccount = $accounts[0];
    $testData = [
        'accountId' => $testAccount['account_id'],
        'billerName' => 'Test Utility Company',
        'billType' => 'utilities',
        'amount' => 50.00,
        'paymentDate' => date('Y-m-d', strtotime('+7 days')),
        'dueDate' => date('Y-m-d', strtotime('+7 days')),
        'memo' => 'Test payment from diagnostic tool',
        'paymentType' => 'one-time',
        'frequency' => null,
        'endDate' => null
    ];
    
    $paymentUrl = $baseUrl . "/Nexo-Banking/backend/process_bill_payment.php";
    echo "  - Testing URL: $paymentUrl<br>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $paymentUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if ($result && $result['success']) {
            echo "✅ Test payment created successfully<br>";
            echo "  - Reference: {$result['data']['referenceNumber']}<br>";
            echo "  - Status: {$result['data']['status']}<br>";
            echo "  - Amount: \${$result['data']['amount']}<br>";
            echo "  - Message: {$result['message']}<br>";
        } else {
            echo "❌ Payment creation failed: " . ($result['message'] ?? 'Unknown error') . "<br>";
        }
    } else {
        echo "❌ Payment API request failed (HTTP $httpCode)<br>";
        echo "Response: $response<br>";
    }
} else {
    echo "⚠️ Skipped - No accounts available<br>";
}

// Test 5: Verify payment was created
echo "<h2>Test 5: Verify Payment in Database</h2>";
$stmt = $conn->prepare("
    SELECT * FROM bill_payments 
    WHERE user_id = ? AND biller_name = 'Test Utility Company'
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->execute([$userId]);
$testPayment = $stmt->fetch(PDO::FETCH_ASSOC);

if ($testPayment) {
    echo "✅ Payment found in database<br>";
    echo "  - Payment ID: {$testPayment['payment_id']}<br>";
    echo "  - Biller: {$testPayment['biller_name']}<br>";
    echo "  - Amount: \${$testPayment['amount']}<br>";
    echo "  - Status: {$testPayment['status']}<br>";
    echo "  - Reference: {$testPayment['reference_number']}<br>";
} else {
    echo "⚠️ Test payment not found in database<br>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>✅ All tests completed! Check results above.</p>";
echo "<p><a href='pay-bills.php'>Go to Pay Bills Page</a></p>";
?>

<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #1a1a1a; color: #fff; }
    h1 { color: #7ef29b; }
    h2 { color: #eb7ef2; margin-top: 30px; }
    hr { border: 1px solid #333; margin: 30px 0; }
    a { color: #7ef29b; }
</style>
