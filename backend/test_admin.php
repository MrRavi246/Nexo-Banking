<?php
// Test admin login - Run this file to verify admin setup
require_once __DIR__ . '/config.php';

echo "<h2>Admin Login Test</h2>";

try {
    $conn = getDBConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check if admin_users table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'admin_users'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ admin_users table exists</p>";
        
        // Check if admin exists
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = 'admin'");
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "<p style='color: green;'>✓ Admin user exists</p>";
            echo "<pre>";
            echo "Admin ID: " . $admin['admin_id'] . "\n";
            echo "Username: " . $admin['username'] . "\n";
            echo "Email: " . $admin['email'] . "\n";
            echo "Role: " . $admin['role'] . "\n";
            echo "Status: " . $admin['status'] . "\n";
            echo "</pre>";
            
            // Test password
            $testPassword = 'Admin@123';
            if (password_verify($testPassword, $admin['password_hash'])) {
                echo "<p style='color: green;'>✓ Password 'Admin@123' is correct</p>";
            } else {
                echo "<p style='color: red;'>✗ Password 'Admin@123' does NOT match</p>";
                echo "<p>Generating new password hash for 'Admin@123':</p>";
                echo "<code>" . password_hash($testPassword, PASSWORD_BCRYPT) . "</code>";
                echo "<p>Update the admin_users table with this hash.</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Admin user does NOT exist</p>";
            echo "<p>Creating admin user...</p>";
            
            // Create admin user
            $passwordHash = password_hash('Admin@123', PASSWORD_BCRYPT);
            $stmt = $conn->prepare("
                INSERT INTO admin_users (username, email, password_hash, first_name, last_name, role) 
                VALUES ('admin', 'admin@nexo.com', ?, 'System', 'Admin', 'super_admin')
            ");
            
            if ($stmt->execute([$passwordHash])) {
                echo "<p style='color: green;'>✓ Admin user created successfully!</p>";
                echo "<p>Username: admin</p>";
                echo "<p>Password: Admin@123</p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to create admin user</p>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>✗ admin_users table does NOT exist</p>";
        echo "<p>Please run database/update_schema.sql</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='../admin/login.php'>Go to Admin Login</a></p>";
?>
