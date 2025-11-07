<?php
require_once '../../backend/config.php';
require_once '../../backend/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo "❌ Not logged in. <a href='../auth/login.php'>Login</a>";
    exit();
}

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Diagnostic Tool</title>
    <style>
        body {
            font-family: monospace;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .positive { color: #28a745; }
        .negative { color: #dc3545; }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🔍 Transfer Diagnostic Tool</h1>
    
    <div class="section">
        <h2>📊 Your Accounts</h2>
        <?php
        $stmt = $conn->prepare("
            SELECT account_id, account_type, account_number, balance, status 
            FROM accounts 
            WHERE user_id = ?
            ORDER BY account_type
        ");
        $stmt->execute([$userId]);
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($accounts) {
            echo "<table>";
            echo "<tr><th>Account ID</th><th>Type</th><th>Number</th><th>Balance</th><th>Status</th></tr>";
            foreach ($accounts as $acc) {
                echo "<tr>";
                echo "<td>{$acc['account_id']}</td>";
                echo "<td>" . ucfirst($acc['account_type']) . "</td>";
                echo "<td>**** " . substr($acc['account_number'], -4) . "</td>";
                echo "<td>$" . number_format($acc['balance'], 2) . "</td>";
                echo "<td>{$acc['status']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='error'>❌ No accounts found!</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>💸 Recent Transactions</h2>
        <?php
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
                a.account_type
            FROM transactions t
            JOIN accounts a ON t.account_id = a.account_id
            WHERE a.user_id = ?
            ORDER BY t.transaction_date DESC, t.transaction_id DESC
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($transactions) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Type</th><th>Amount</th><th>Description</th><th>Recipient</th><th>Status</th><th>Date</th></tr>";
            foreach ($transactions as $txn) {
                $amountClass = $txn['amount'] >= 0 ? 'positive' : 'negative';
                echo "<tr>";
                echo "<td>{$txn['transaction_id']}</td>";
                echo "<td>{$txn['transaction_type']}</td>";
                echo "<td class='$amountClass'>$" . number_format($txn['amount'], 2) . "</td>";
                echo "<td>{$txn['description']}</td>";
                echo "<td>" . ($txn['recipient_name'] ?? '-') . "</td>";
                echo "<td>{$txn['status']}</td>";
                echo "<td>{$txn['transaction_date']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warning'>⚠️ No transactions found!</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>👥 Other Users (Potential Recipients)</h2>
        <?php
        $stmt = $conn->prepare("
            SELECT user_id, username, email, first_name, last_name, status 
            FROM users 
            WHERE user_id != ?
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($users) {
            echo "<table>";
            echo "<tr><th>User ID</th><th>Name</th><th>Email</th><th>Status</th><th>Has Accounts?</th></tr>";
            foreach ($users as $user) {
                // Check if user has accounts
                $stmt2 = $conn->prepare("SELECT COUNT(*) as count FROM accounts WHERE user_id = ? AND status = 'active'");
                $stmt2->execute([$user['user_id']]);
                $accountCount = $stmt2->fetch(PDO::FETCH_ASSOC)['count'];
                
                echo "<tr>";
                echo "<td>{$user['user_id']}</td>";
                echo "<td>{$user['first_name']} {$user['last_name']}</td>";
                echo "<td>{$user['email']}</td>";
                echo "<td>{$user['status']}</td>";
                echo "<td>" . ($accountCount > 0 ? "✅ Yes ($accountCount)" : "❌ No") . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warning'>⚠️ No other users found!</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>📞 Saved Contacts/Beneficiaries</h2>
        <?php
        try {
            $stmt = $conn->prepare("
                SELECT beneficiary_id, beneficiary_name, account_number, bank_name, email, status 
                FROM beneficiaries 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $beneficiaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($beneficiaries) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Name</th><th>Account</th><th>Bank</th><th>Email</th><th>Status</th></tr>";
                foreach ($beneficiaries as $ben) {
                    echo "<tr>";
                    echo "<td>{$ben['beneficiary_id']}</td>";
                    echo "<td>{$ben['beneficiary_name']}</td>";
                    echo "<td>**** " . substr($ben['account_number'], -4) . "</td>";
                    echo "<td>{$ben['bank_name']}</td>";
                    echo "<td>" . ($ben['email'] ?? '-') . "</td>";
                    echo "<td>{$ben['status']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠️ No beneficiaries found!</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>❌ Beneficiaries table doesn't exist: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>🔍 Recent PHP Error Log</h2>
        <?php
        $errorLog = ini_get('error_log');
        if (!$errorLog) {
            $errorLog = 'C:/xampp/apache/logs/error.log'; // Default XAMPP location
        }
        
        if (file_exists($errorLog)) {
            $lines = file($errorLog);
            $recentLines = array_slice($lines, -50); // Last 50 lines
            
            echo "<pre>";
            foreach ($recentLines as $line) {
                if (stripos($line, 'transfer') !== false || stripos($line, 'transaction') !== false) {
                    echo htmlspecialchars($line);
                }
            }
            echo "</pre>";
            
            if (count($recentLines) === 0) {
                echo "<p>No recent transfer-related errors found.</p>";
            }
        } else {
            echo "<p class='warning'>Error log not found at: $errorLog</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>🎯 Quick Actions</h2>
        <p><a href="transfer-money.php" style="color: #007bff; text-decoration: none;">← Back to Transfer Money</a></p>
        <p><a href="test-transfer-new-recipient.php" style="color: #007bff; text-decoration: none;">🧪 Run Transfer Test</a></p>
    </div>
</body>
</html>
