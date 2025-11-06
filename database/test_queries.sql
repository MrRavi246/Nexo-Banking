-- Nexo Banking - Test Data Script
-- This script helps you test the approval workflow

-- After running nexo_schema.sql and update_schema.sql, 
-- you can use these queries to test various scenarios

-- 1. Check if admin user was created
SELECT * FROM admin_users WHERE username = 'admin';

-- If admin doesn't exist, create one:
-- INSERT INTO admin_users (username, email, password_hash, first_name, last_name, role) 
-- VALUES ('admin', 'admin@nexo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Admin', 'super_admin');

-- 2. View all users with their status
SELECT 
    u.user_id,
    u.username,
    u.email,
    CONCAT(u.first_name, ' ', u.last_name) AS full_name,
    u.status,
    u.member_type,
    u.created_at,
    u.approved_at,
    a.account_number,
    a.account_type
FROM users u
LEFT JOIN accounts a ON u.user_id = a.user_id
ORDER BY u.created_at DESC;

-- 3. View only pending users
SELECT 
    u.user_id,
    u.username,
    u.email,
    CONCAT(u.first_name, ' ', u.last_name) AS full_name,
    u.phone_number,
    u.member_type,
    u.created_at,
    a.account_number
FROM users u
LEFT JOIN accounts a ON u.user_id = a.user_id AND a.account_type = 'checking'
WHERE u.status = 'pending'
ORDER BY u.created_at DESC;

-- 4. View user accounts and their status
SELECT 
    u.username,
    u.status as user_status,
    a.account_number,
    a.account_type,
    a.balance,
    a.status as account_status
FROM users u
JOIN accounts a ON u.user_id = a.user_id
ORDER BY u.user_id, a.account_type;

-- 5. View recent sessions
SELECT 
    s.session_id,
    u.username,
    u.email,
    s.ip_address,
    s.created_at,
    s.expires_at,
    s.last_activity
FROM sessions s
JOIN users u ON s.user_id = u.user_id
ORDER BY s.created_at DESC
LIMIT 10;

-- 6. View audit logs
SELECT 
    al.log_id,
    u.username,
    al.action_type,
    al.table_name,
    al.created_at,
    al.ip_address
FROM audit_logs al
LEFT JOIN users u ON al.user_id = u.user_id
ORDER BY al.created_at DESC
LIMIT 20;

-- 7. View notifications
SELECT 
    n.notification_id,
    u.username,
    n.title,
    n.message,
    n.type,
    n.is_read,
    n.created_at
FROM notifications n
JOIN users u ON n.user_id = u.user_id
ORDER BY n.created_at DESC
LIMIT 10;

-- 8. Manually approve a user (if needed for testing)
-- Replace {user_id} with actual user ID
/*
START TRANSACTION;

UPDATE users 
SET status = 'active', 
    approved_by = 1, 
    approved_at = NOW() 
WHERE user_id = {user_id};

UPDATE accounts 
SET status = 'active' 
WHERE user_id = {user_id};

COMMIT;
*/

-- 9. Manually reject a user (if needed for testing)
-- Replace {user_id} with actual user ID
/*
UPDATE users 
SET status = 'rejected', 
    rejection_reason = 'Test rejection',
    approved_by = 1, 
    approved_at = NOW() 
WHERE user_id = {user_id};
*/

-- 10. Reset a user to pending status (for re-testing)
-- Replace {user_id} with actual user ID
/*
START TRANSACTION;

UPDATE users 
SET status = 'pending', 
    approved_by = NULL, 
    approved_at = NULL,
    rejection_reason = NULL
WHERE user_id = {user_id};

UPDATE accounts 
SET status = 'inactive' 
WHERE user_id = {user_id};

COMMIT;
*/

-- 11. Check for orphaned sessions (sessions with expired time)
SELECT 
    s.session_id,
    u.username,
    s.expires_at,
    NOW() as current_time,
    TIMESTAMPDIFF(MINUTE, s.expires_at, NOW()) as expired_minutes_ago
FROM sessions s
JOIN users u ON s.user_id = u.user_id
WHERE s.expires_at < NOW();

-- 12. Clean up old sessions (run periodically)
-- DELETE FROM sessions WHERE expires_at < NOW();

-- 13. Statistics queries

-- Count users by status
SELECT 
    status,
    COUNT(*) as user_count
FROM users
GROUP BY status;

-- Count accounts by status
SELECT 
    status,
    account_type,
    COUNT(*) as account_count
FROM accounts
GROUP BY status, account_type;

-- Count users by member type
SELECT 
    member_type,
    COUNT(*) as user_count
FROM users
GROUP BY member_type;

-- 14. Get account number for a user (for login testing)
SELECT 
    u.username,
    u.email,
    a.account_number,
    a.account_type
FROM users u
JOIN accounts a ON u.user_id = a.user_id
WHERE u.username = 'your_test_username'  -- Replace with actual username
AND a.account_type = 'checking';

-- 15. Debug: View all tables structure
SHOW TABLES;
DESCRIBE users;
DESCRIBE admin_users;
DESCRIBE sessions;
DESCRIBE admin_sessions;
DESCRIBE accounts;
DESCRIBE audit_logs;
DESCRIBE notifications;

-- 16. Create a test user manually (optional)
/*
START TRANSACTION;

-- Insert user (password is 'Test@123')
INSERT INTO users 
(username, email, password_hash, first_name, last_name, phone_number, date_of_birth, address, member_type, status) 
VALUES 
('testuser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
'Test', 'User', '+1234567890', '1990-01-01', '123 Test Street', 'basic', 'pending');

SET @user_id = LAST_INSERT_ID();

-- Create checking account
INSERT INTO accounts 
(user_id, account_type, account_number, balance, status) 
VALUES 
(@user_id, 'checking', CONCAT('10', LPAD(FLOOR(RAND() * 100000000), 8, '0')), 0.00, 'inactive');

-- Create savings account
INSERT INTO accounts 
(user_id, account_type, account_number, balance, interest_rate, status) 
VALUES 
(@user_id, 'savings', CONCAT('10', LPAD(FLOOR(RAND() * 100000000), 8, '0')), 0.00, 0.025, 'inactive');

COMMIT;
*/

-- 17. Verify password hash (for debugging login issues)
-- This will show if password verification would work
-- Replace 'your_password' and 'password_hash_from_db' with actual values
/*
SELECT 
    PASSWORD('your_password') as mysql_hash,
    -- Note: PASSWORD() is MySQL's hash, NOT bcrypt which PHP uses
    -- This is just to show the concept
    'Use PHP password_verify() to test bcrypt hashes' as note;
*/

-- 18. Get user info with all related data
SELECT 
    u.user_id,
    u.username,
    u.email,
    u.first_name,
    u.last_name,
    u.status,
    u.member_type,
    u.created_at,
    COUNT(DISTINCT a.account_id) as account_count,
    COUNT(DISTINCT n.notification_id) as notification_count,
    u.last_login
FROM users u
LEFT JOIN accounts a ON u.user_id = a.user_id
LEFT JOIN notifications n ON u.user_id = n.user_id
WHERE u.username = 'your_test_username'  -- Replace with actual username
GROUP BY u.user_id;

-- 19. Monitor login attempts (from audit logs)
SELECT 
    al.created_at,
    al.action_type,
    al.new_values,
    al.ip_address
FROM audit_logs al
WHERE al.action_type IN ('LOGIN_SUCCESS', 'LOGIN_FAILED')
ORDER BY al.created_at DESC
LIMIT 20;

-- 20. Check for duplicate emails or usernames
SELECT email, COUNT(*) as count
FROM users
GROUP BY email
HAVING count > 1;

SELECT username, COUNT(*) as count
FROM users
GROUP BY username
HAVING count > 1;
