<?php
/**
 * Database Verification Script
 * Checks if all required tables and data exist for dashboard functionality
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Verification - Nexo Banking</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .check-group {
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }
        .check-header {
            background: #f5f5f5;
            padding: 15px;
            font-weight: bold;
            border-bottom: 1px solid #e0e0e0;
        }
        .check-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
        .status.warning {
            background: #fff3cd;
            color: #856404;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .details {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        .summary {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .summary h2 {
            margin-top: 0;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Database Verification</h1>
        <p class="subtitle">Nexo Banking System - Dashboard Integration Check</p>

        <?php
        try {
            $conn = getDBConnection();
            $checks = [];
            $errors = 0;
            $warnings = 0;
            $success = 0;

            // Check 1: Required Tables
            echo '<div class="check-group">';
            echo '<div class="check-header">📋 Required Tables</div>';
            
            $requiredTables = ['users', 'accounts', 'transactions', 'savings_goals', 'notifications', 'sessions', 'admin_sessions'];
            foreach ($requiredTables as $table) {
                $stmt = $conn->query("SHOW TABLES LIKE '$table'");
                $exists = $stmt->rowCount() > 0;
                
                echo '<div class="check-item">';
                echo '<span>' . ucfirst($table) . ' table</span>';
                if ($exists) {
                    echo '<span class="status success">✓ EXISTS</span>';
                    $success++;
                } else {
                    echo '<span class="status error">✗ MISSING</span>';
                    $errors++;
                }
                echo '</div>';
            }
            echo '</div>';

            // Check 2: Users with Accounts
            echo '<div class="check-group">';
            echo '<div class="check-header">👥 User Accounts</div>';
            
            $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
            $activeUsers = $stmt->fetch()['count'];
            
            echo '<div class="check-item">';
            echo '<span>Active users</span>';
            if ($activeUsers > 0) {
                echo '<span class="status success">✓ ' . $activeUsers . ' users</span>';
                $success++;
            } else {
                echo '<span class="status warning">⚠ No active users</span>';
                $warnings++;
            }
            echo '</div>';
            
            // Check for users with accounts
            $stmt = $conn->query("
                SELECT COUNT(DISTINCT u.user_id) as count 
                FROM users u 
                INNER JOIN accounts a ON u.user_id = a.user_id 
                WHERE u.status = 'active'
            ");
            $usersWithAccounts = $stmt->fetch()['count'];
            
            echo '<div class="check-item">';
            echo '<span>Users with bank accounts</span>';
            if ($usersWithAccounts > 0) {
                echo '<span class="status success">✓ ' . $usersWithAccounts . ' users</span>';
                $success++;
            } else {
                echo '<span class="status warning">⚠ No users have accounts</span>';
                $warnings++;
            }
            echo '</div>';
            
            echo '</div>';

            // Check 3: Account Balances
            echo '<div class="check-group">';
            echo '<div class="check-header">💰 Account Data</div>';
            
            $stmt = $conn->query("SELECT COUNT(*) as count FROM accounts WHERE balance > 0");
            $accountsWithBalance = $stmt->fetch()['count'];
            
            echo '<div class="check-item">';
            echo '<span>Accounts with balance > $0</span>';
            if ($accountsWithBalance > 0) {
                echo '<span class="status success">✓ ' . $accountsWithBalance . ' accounts</span>';
                $success++;
            } else {
                echo '<span class="status warning">⚠ All accounts have $0 balance</span>';
                $warnings++;
            }
            echo '</div>';
            
            $stmt = $conn->query("SELECT account_type, COUNT(*) as count FROM accounts GROUP BY account_type");
            $accountTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($accountTypes as $type) {
                echo '<div class="check-item">';
                echo '<span>' . ucfirst($type['account_type']) . ' accounts</span>';
                echo '<span class="status success">' . $type['count'] . '</span>';
                echo '</div>';
            }
            
            echo '</div>';

            // Check 4: Transactions
            echo '<div class="check-group">';
            echo '<div class="check-header">💳 Transactions</div>';
            
            $stmt = $conn->query("SELECT COUNT(*) as count FROM transactions");
            $totalTransactions = $stmt->fetch()['count'];
            
            echo '<div class="check-item">';
            echo '<span>Total transactions</span>';
            if ($totalTransactions > 0) {
                echo '<span class="status success">✓ ' . $totalTransactions . ' transactions</span>';
                $success++;
            } else {
                echo '<span class="status warning">⚠ No transactions found</span>';
                $warnings++;
            }
            echo '</div>';
            
            if ($totalTransactions > 0) {
                $stmt = $conn->query("
                    SELECT transaction_type, COUNT(*) as count 
                    FROM transactions 
                    GROUP BY transaction_type 
                    ORDER BY count DESC
                ");
                $transactionTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($transactionTypes as $type) {
                    echo '<div class="check-item">';
                    echo '<span>' . ucfirst($type['transaction_type']) . ' transactions</span>';
                    echo '<span class="details">' . $type['count'] . '</span>';
                    echo '</div>';
                }
            }
            
            echo '</div>';

            // Check 5: Savings Goals
            echo '<div class="check-group">';
            echo '<div class="check-header">🎯 Savings Goals</div>';
            
            $stmt = $conn->query("SELECT COUNT(*) as count FROM savings_goals WHERE status = 'active'");
            $activeGoals = $stmt->fetch()['count'];
            
            echo '<div class="check-item">';
            echo '<span>Active savings goals</span>';
            if ($activeGoals > 0) {
                echo '<span class="status success">✓ ' . $activeGoals . ' goals</span>';
                $success++;
            } else {
                echo '<span class="status warning">⚠ No savings goals</span>';
                $warnings++;
            }
            echo '</div>';
            
            echo '</div>';

            // Check 6: Notifications
            echo '<div class="check-group">';
            echo '<div class="check-header">🔔 Notifications</div>';
            
            $stmt = $conn->query("SELECT COUNT(*) as count FROM notifications");
            $totalNotifications = $stmt->fetch()['count'];
            
            echo '<div class="check-item">';
            echo '<span>Total notifications</span>';
            if ($totalNotifications > 0) {
                echo '<span class="status success">✓ ' . $totalNotifications . ' notifications</span>';
                $success++;
            } else {
                echo '<span class="status warning">⚠ No notifications</span>';
                $warnings++;
            }
            echo '</div>';
            
            $stmt = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0");
            $unreadNotifications = $stmt->fetch()['count'];
            
            echo '<div class="check-item">';
            echo '<span>Unread notifications</span>';
            echo '<span class="details">' . $unreadNotifications . '</span>';
            echo '</div>';
            
            echo '</div>';

            // Check 7: API Endpoints
            echo '<div class="check-group">';
            echo '<div class="check-header">🔌 API Files</div>';
            
            $apiFiles = [
                'config.php' => 'Configuration file',
                'functions.php' => 'Helper functions',
                'get_user_data.php' => 'User data API',
                'login.php' => 'Login endpoint',
                'signup.php' => 'Registration endpoint',
                'logout.php' => 'Logout endpoint'
            ];
            
            foreach ($apiFiles as $file => $description) {
                $exists = file_exists(__DIR__ . '/' . $file);
                
                echo '<div class="check-item">';
                echo '<span>' . $description . '</span>';
                if ($exists) {
                    echo '<span class="status success">✓ EXISTS</span>';
                    $success++;
                } else {
                    echo '<span class="status error">✗ MISSING</span>';
                    $errors++;
                }
                echo '</div>';
            }
            
            echo '</div>';

            // Summary
            $total = $success + $warnings + $errors;
            $percentage = $total > 0 ? round(($success / $total) * 100) : 0;
            
            echo '<div class="summary">';
            echo '<h2>📊 Summary</h2>';
            echo '<div class="check-item">';
            echo '<span><strong>Total Checks:</strong></span>';
            echo '<span>' . $total . '</span>';
            echo '</div>';
            echo '<div class="check-item">';
            echo '<span><strong>Passed:</strong></span>';
            echo '<span class="status success">' . $success . '</span>';
            echo '</div>';
            echo '<div class="check-item">';
            echo '<span><strong>Warnings:</strong></span>';
            echo '<span class="status warning">' . $warnings . '</span>';
            echo '</div>';
            echo '<div class="check-item">';
            echo '<span><strong>Errors:</strong></span>';
            echo '<span class="status error">' . $errors . '</span>';
            echo '</div>';
            echo '<div class="check-item">';
            echo '<span><strong>System Health:</strong></span>';
            echo '<span style="font-size: 24px; font-weight: bold; color: ' . ($percentage >= 80 ? '#155724' : ($percentage >= 50 ? '#856404' : '#721c24')) . '">' . $percentage . '%</span>';
            echo '</div>';
            echo '</div>';

            // Recommendations
            if ($warnings > 0 || $errors > 0) {
                echo '<div class="info-box">';
                echo '<h3 style="margin-top: 0;">💡 Recommendations</h3>';
                
                if ($activeUsers == 0) {
                    echo '<p>• Create and approve at least one user through the admin panel</p>';
                }
                
                if ($accountsWithBalance == 0) {
                    echo '<p>• Run <code>database/quick_setup_test_data.sql</code> to add test data</p>';
                }
                
                if ($totalTransactions == 0) {
                    echo '<p>• Add sample transactions to test the dashboard properly</p>';
                }
                
                if ($activeGoals == 0) {
                    echo '<p>• Consider adding savings goals for a complete dashboard experience</p>';
                }
                
                if ($errors > 0) {
                    echo '<p><strong>⚠️ Critical:</strong> Fix missing files/tables before testing the dashboard</p>';
                }
                
                echo '</div>';
            } else {
                echo '<div class="info-box" style="background: #d4edda; border-left-color: #28a745;">';
                echo '<h3 style="margin-top: 0; color: #155724;">✅ System Ready!</h3>';
                echo '<p>All checks passed! Your dashboard is ready to display user data.</p>';
                echo '<p><strong>Next step:</strong> Login with an active user account and view the dashboard.</p>';
                echo '</div>';
            }

        } catch (Exception $e) {
            echo '<div class="info-box" style="background: #f8d7da; border-left-color: #dc3545;">';
            echo '<h3 style="margin-top: 0; color: #721c24;">❌ Database Connection Error</h3>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p>Check your database configuration in <code>backend/config.php</code></p>';
            echo '</div>';
        }
        ?>

        <div style="margin-top: 30px; padding: 15px; background: #f9f9f9; border-radius: 8px; text-align: center; color: #666;">
            <small>Nexo Banking System • Database Verification Tool v1.0</small>
        </div>
    </div>
</body>
</html>
