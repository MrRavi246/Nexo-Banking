<?php
/**
 * Minimal Database Test - No dependencies
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Minimal Database Connection Test</h1>";
echo "<p>Testing basic database operations...</p>";
echo "<hr>";

// Test 1: Can we connect to database?
echo "<h2>Test 1: Database Connection</h2>";
try {
    $host = 'localhost';
    $dbname = 'nexo_banking';
    $username = 'root';
    $password = '';
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful<br>";
    
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}

// Test 2: Can we query bill_payments table?
echo "<h2>Test 2: Bill Payments Table</h2>";
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM bill_payments");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Bill payments table exists<br>";
    echo "  - Total records: {$result['count']}<br>";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 3: Can we query accounts?
echo "<h2>Test 3: Accounts Table</h2>";
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM accounts WHERE status = 'active'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Accounts table accessible<br>";
    echo "  - Active accounts: {$result['count']}<br>";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 4: List all accounts
echo "<h2>Test 4: List All Accounts</h2>";
try {
    $stmt = $conn->query("SELECT user_id, account_id, account_type, account_number, balance FROM accounts WHERE status = 'active' LIMIT 5");
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Found " . count($accounts) . " accounts<br>";
    echo "<pre>";
    print_r($accounts);
    echo "</pre>";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 5: Insert a test bill payment
echo "<h2>Test 5: Insert Test Payment</h2>";
try {
    if (count($accounts) > 0) {
        $testAccount = $accounts[0];
        
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
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $refNumber = 'TEST' . time();
        $futureDate = date('Y-m-d', strtotime('+7 days'));
        
        $result = $stmt->execute([
            $testAccount['user_id'],
            $testAccount['account_id'],
            'Minimal Test Biller',
            'utilities',
            99.99,
            $futureDate,
            $futureDate,
            'scheduled',
            $refNumber
        ]);
        
        if ($result) {
            $paymentId = $conn->lastInsertId();
            echo "✅ Test payment inserted successfully<br>";
            echo "  - Payment ID: $paymentId<br>";
            echo "  - Reference: $refNumber<br>";
        } else {
            echo "❌ Insert failed<br>";
        }
    } else {
        echo "⚠️ No accounts to test with<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 6: Query the payment we just created
echo "<h2>Test 6: Verify Payment</h2>";
try {
    $stmt = $conn->query("SELECT * FROM bill_payments ORDER BY created_at DESC LIMIT 1");
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($payment) {
        echo "✅ Latest payment found<br>";
        echo "<pre>";
        print_r($payment);
        echo "</pre>";
    } else {
        echo "⚠️ No payments found<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>✅ All Tests Complete!</h2>";
echo "<p>If you see this, the database is working fine.</p>";
echo "<p>The issue is likely in config.php or functions.php</p>";

?>

<style>
    body { font-family: monospace; max-width: 900px; margin: 20px auto; padding: 20px; background: #000; color: #0f0; }
    h1 { color: #0ff; }
    h2 { color: #ff0; margin-top: 20px; }
    hr { border: 1px solid #333; margin: 20px 0; }
    pre { background: #111; padding: 10px; border: 1px solid #333; overflow-x: auto; }
</style>
