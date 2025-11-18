<?php
// Test file to check admin dashboard endpoint
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Admin Dashboard Test</h1>";

// Check session
echo "<h2>Session Check:</h2>";
echo "<pre>";
echo "Admin logged in: " . (isAdminLoggedIn() ? "YES" : "NO") . "\n";
if (isAdminLoggedIn()) {
    echo "Admin ID: " . ($_SESSION['admin_id'] ?? 'Not set') . "\n";
    echo "Admin Username: " . ($_SESSION['admin_username'] ?? 'Not set') . "\n";
    echo "Session Token: " . (isset($_SESSION['admin_session_token']) ? 'Set' : 'Not set') . "\n";
}
echo "</pre>";

// Test database connection
echo "<h2>Database Connection:</h2>";
try {
    $conn = getDBConnection();
    echo "<p style='color:green'>✓ Database connected successfully</p>";
    
    // Test queries
    echo "<h2>Test Queries:</h2>";
    
    // Users count
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p style='color:green'>✓ Users table: {$count} records</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Users table error: " . $e->getMessage() . "</p>";
    }
    
    // Transactions count
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM transactions");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p style='color:green'>✓ Transactions table: {$count} records</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Transactions table error: " . $e->getMessage() . "</p>";
    }
    
    // Accounts count
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM accounts");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p style='color:green'>✓ Accounts table: {$count} records</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Accounts table error: " . $e->getMessage() . "</p>";
    }
    
    // Audit logs count
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM audit_logs");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p style='color:green'>✓ Audit logs table: {$count} records</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Audit logs table error: " . $e->getMessage() . "</p>";
    }
    
    // Loan applications (may not exist)
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM loan_applications");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p style='color:green'>✓ Loan applications table: {$count} records</p>";
    } catch (Exception $e) {
        echo "<p style='color:orange'>⚠ Loan applications table: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database error: " . $e->getMessage() . "</p>";
}

// Test API endpoint directly
if (isAdminLoggedIn()) {
    echo "<h2>Test API Endpoint:</h2>";
    echo "<p><a href='admin_get_dashboard.php' target='_blank'>Click here to test admin_get_dashboard.php</a></p>";
}
?>
