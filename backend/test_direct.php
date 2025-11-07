<?php
// Direct API Test - Shows exact error
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Direct API Test</h1>";
echo "<pre>";

// Check if user is logged in
echo "1. Checking login status...\n";
if (!isLoggedIn()) {
    echo "❌ NOT LOGGED IN\n";
    echo "Please login first at: <a href='../Pages/auth/login.php'>Login</a>\n";
    exit;
}
echo "✅ Logged in as User ID: " . $_SESSION['user_id'] . "\n\n";

try {
    $conn = getDBConnection();
    echo "2. Database connection established\n\n";
    
    $userId = $_SESSION['user_id'];
    
    // Test 1: Get user
    echo "3. Testing user query...\n";
    $stmt = $conn->prepare("SELECT user_id, username, email, first_name, last_name FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ User found: " . $user['first_name'] . " " . $user['last_name'] . "\n";
        echo "   Username: " . $user['username'] . "\n";
        echo "   Email: " . $user['email'] . "\n\n";
    } else {
        echo "❌ User not found!\n\n";
    }
    
    // Test 2: Get accounts
    echo "4. Testing accounts query...\n";
    $stmt = $conn->prepare("SELECT account_id, account_type, balance FROM accounts WHERE user_id = ?");
    $stmt->execute([$userId]);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($accounts) {
        echo "✅ Found " . count($accounts) . " accounts:\n";
        foreach ($accounts as $account) {
            echo "   - " . $account['account_type'] . ": $" . $account['balance'] . "\n";
        }
        echo "\n";
    } else {
        echo "⚠️  No accounts found\n\n";
    }
    
    // Test 3: Get transactions
    echo "5. Testing transactions query...\n";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM transactions t JOIN accounts a ON t.account_id = a.account_id WHERE a.user_id = ?");
    $stmt->execute([$userId]);
    $txCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Found " . $txCount['count'] . " transactions\n\n";
    
    // Test 4: Get savings goals
    echo "6. Testing savings goals query...\n";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM savings_goals WHERE user_id = ?");
    $stmt->execute([$userId]);
    $goalsCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Found " . $goalsCount['count'] . " savings goals\n\n";
    
    // Test 5: Get notifications
    echo "7. Testing notifications query...\n";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ?");
    $stmt->execute([$userId]);
    $notifCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Found " . $notifCount['count'] . " notifications\n\n";
    
    // Test 6: Get credit score (this might fail)
    echo "8. Testing credit score query...\n";
    try {
        $stmt = $conn->prepare("SELECT score, score_date FROM credit_scores WHERE user_id = ? ORDER BY score_date DESC LIMIT 1");
        $stmt->execute([$userId]);
        $creditScore = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($creditScore) {
            echo "✅ Credit score found: " . $creditScore['score'] . "\n\n";
        } else {
            echo "⚠️  No credit score found (this is OK)\n\n";
        }
    } catch (PDOException $e) {
        echo "❌ Credit score query failed: " . $e->getMessage() . "\n";
        echo "   (This table might not exist - that's OK)\n\n";
    }
    
    echo "========================================\n";
    echo "ALL TESTS PASSED! ✅\n";
    echo "========================================\n\n";
    
    echo "Now testing the actual API endpoint...\n\n";
    
    // Now call the actual API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost/Nexo-Banking/backend/get_user_data.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    $response = curl_exec($ch);
    curl_close($ch);
    
    echo "API Response:\n";
    $data = json_decode($response, true);
    
    if ($data && $data['success']) {
        echo "✅ API SUCCESS!\n";
        echo "User Name: " . $data['data']['user']['full_name'] . "\n";
        echo "Total Balance: $" . $data['data']['accounts']['total_balance'] . "\n";
    } else {
        echo "❌ API FAILED!\n";
        echo "Response: " . $response . "\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line " . $e->getLine() . ")\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";

// Check PHP error log
echo "<h2>Recent PHP Errors:</h2>";
echo "<pre>";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $lines = file($errorLog);
    $recentErrors = array_slice($lines, -20);
    echo implode("", $recentErrors);
} else {
    echo "Error log location: " . ($errorLog ?: 'Not configured') . "\n";
    echo "Check XAMPP error logs in: C:\\xampp\\apache\\logs\\error.log\n";
}
echo "</pre>";
?>
