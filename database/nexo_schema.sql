-- Nexo Banking System Database Schema
-- Generated based on frontend application analysis

-- Create database
CREATE DATABASE IF NOT EXISTS nexo_banking;
USE nexo_banking;

-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone_number VARCHAR(20),
    date_of_birth DATE,
    address TEXT,
    profile_image VARCHAR(255),
    member_type ENUM('basic', 'premium', 'platinum') DEFAULT 'basic',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Accounts table
CREATE TABLE accounts (
    account_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_type ENUM('checking', 'savings', 'credit', 'loan') NOT NULL,
    account_number VARCHAR(20) UNIQUE NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'USD',
    status ENUM('active', 'inactive', 'frozen', 'closed') DEFAULT 'active',
    credit_limit DECIMAL(15,2) NULL,
    interest_rate DECIMAL(5,4) NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_accounts (user_id),
    INDEX idx_account_number (account_number)
);

-- Transactions table
CREATE TABLE transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT NOT NULL,
    transaction_type ENUM('deposit', 'withdrawal', 'transfer', 'payment', 'refund', 'fee', 'interest') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description VARCHAR(255),
    recipient_info TEXT,
    category VARCHAR(50),
    reference_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE CASCADE,
    INDEX idx_account_transactions (account_id),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_transaction_status (status)
);

-- Transfers table
CREATE TABLE transfers (
    transfer_id INT PRIMARY KEY AUTO_INCREMENT,
    from_account_id INT NOT NULL,
    to_account_id INT,
    amount DECIMAL(15,2) NOT NULL,
    transfer_type ENUM('internal', 'external', 'wire', 'ach') NOT NULL,
    recipient_name VARCHAR(100),
    recipient_phone VARCHAR(20),
    message TEXT,
    fee_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_account_id) REFERENCES accounts(account_id) ON DELETE CASCADE,
    FOREIGN KEY (to_account_id) REFERENCES accounts(account_id) ON DELETE SET NULL,
    INDEX idx_from_account (from_account_id),
    INDEX idx_to_account (to_account_id),
    INDEX idx_transfer_status (status)
);

-- Savings Goals table
CREATE TABLE savings_goals (
    goal_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    goal_name VARCHAR(100) NOT NULL,
    target_amount DECIMAL(15,2) NOT NULL,
    current_amount DECIMAL(15,2) DEFAULT 0.00,
    target_date DATE,
    category VARCHAR(50),
    status ENUM('active', 'completed', 'paused', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_goals (user_id)
);

-- Credit Scores table
CREATE TABLE credit_scores (
    score_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    score_value INT NOT NULL,
    score_range VARCHAR(20),
    factors JSON,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_credit (user_id)
);

-- Bill Payments table
CREATE TABLE bill_payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    biller_name VARCHAR(100) NOT NULL,
    bill_type ENUM('utilities', 'credit_card', 'loan', 'subscription', 'mobile_recharge', 'insurance') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    due_date DATE,
    payment_date TIMESTAMP,
    status ENUM('scheduled', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'scheduled',
    reference_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE CASCADE,
    INDEX idx_user_bills (user_id),
    INDEX idx_bill_status (status),
    INDEX idx_due_date (due_date)
);

-- Contacts table
CREATE TABLE contacts (
    contact_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    contact_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20),
    email VARCHAR(100),
    relationship VARCHAR(50),
    avatar_initials VARCHAR(5),
    is_favorite BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_contacts (user_id),
    INDEX idx_favorites (user_id, is_favorite)
);

-- Budgets table
CREATE TABLE budgets (
    budget_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    month_year VARCHAR(7) NOT NULL, -- Format: YYYY-MM
    total_budget DECIMAL(15,2) NOT NULL,
    spent_amount DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('active', 'completed', 'exceeded') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_month (user_id, month_year),
    INDEX idx_user_budgets (user_id)
);

-- Budget Categories table
CREATE TABLE budget_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    budget_id INT NOT NULL,
    category_name VARCHAR(50) NOT NULL,
    allocated_amount DECIMAL(15,2) NOT NULL,
    spent_amount DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (budget_id) REFERENCES budgets(budget_id) ON DELETE CASCADE,
    INDEX idx_budget_categories (budget_id)
);

-- Notifications table
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT,
    type ENUM('transaction_alert', 'low_balance', 'payment_due', 'goal_achieved', 'security_alert', 'system_maintenance') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_notifications (user_id),
    INDEX idx_unread_notifications (user_id, is_read)
);

-- Sessions table for login tracking
CREATE TABLE sessions (
    session_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_user_sessions (user_id),
    INDEX idx_session_expiry (expires_at)
);

-- Audit Logs table for security and compliance
CREATE TABLE audit_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action_type VARCHAR(50) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_logs (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_table_logs (table_name, record_id)
);

-- System Settings table
CREATE TABLE system_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('transfer_fee_internal', '0.00', 'Fee for internal transfers between user accounts'),
('transfer_fee_external', '2.50', 'Fee for external transfers to other users'),
('min_transfer_amount', '1.00', 'Minimum transfer amount'),
('max_transfer_amount', '10000.00', 'Maximum transfer amount per transaction'),
('daily_transfer_limit', '25000.00', 'Daily transfer limit per user'),
('low_balance_threshold', '100.00', 'Threshold for low balance notifications'),
('currency_default', 'USD', 'Default currency for new accounts'),
('interest_rate_savings', '2.50', 'Default interest rate for savings accounts'),
('credit_score_update_interval', '30', 'Days between credit score updates');

-- Create triggers for automatic balance updates
DELIMITER //

CREATE TRIGGER update_account_balance_after_transaction
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' THEN
        IF NEW.transaction_type IN ('deposit', 'refund', 'interest') THEN
            UPDATE accounts 
            SET balance = balance + NEW.amount,
                last_activity = NEW.transaction_date
            WHERE account_id = NEW.account_id;
        ELSEIF NEW.transaction_type IN ('withdrawal', 'payment', 'fee') THEN
            UPDATE accounts 
            SET balance = balance - NEW.amount,
                last_activity = NEW.transaction_date
            WHERE account_id = NEW.account_id;
        END IF;
    END IF;
END//

CREATE TRIGGER update_budget_spent_amount
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    DECLARE budget_exists INT DEFAULT 0;
    DECLARE current_month_year VARCHAR(7);
    
    IF NEW.status = 'completed' AND NEW.transaction_type IN ('withdrawal', 'payment') THEN
        SET current_month_year = DATE_FORMAT(NEW.transaction_date, '%Y-%m');
        
        SELECT COUNT(*) INTO budget_exists
        FROM budgets b 
        JOIN accounts a ON a.user_id = b.user_id
        WHERE a.account_id = NEW.account_id 
        AND b.month_year = current_month_year;
        
        IF budget_exists > 0 THEN
            UPDATE budgets b
            JOIN accounts a ON a.user_id = b.user_id
            SET b.spent_amount = b.spent_amount + NEW.amount
            WHERE a.account_id = NEW.account_id 
            AND b.month_year = current_month_year;
            
            -- Update category spending if category matches
            IF NEW.category IS NOT NULL THEN
                UPDATE budget_categories bc
                JOIN budgets b ON b.budget_id = bc.budget_id
                JOIN accounts a ON a.user_id = b.user_id
                SET bc.spent_amount = bc.spent_amount + NEW.amount
                WHERE a.account_id = NEW.account_id 
                AND b.month_year = current_month_year
                AND bc.category_name = NEW.category;
            END IF;
        END IF;
    END IF;
END//

DELIMITER ;

-- Sample data for testing (optional)
-- Insert sample user
INSERT INTO users (username, email, password_hash, first_name, last_name, phone_number, member_type) VALUES
('john_doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '+1-555-123-4567', 'premium');

-- Insert sample accounts
INSERT INTO accounts (user_id, account_type, account_number, balance) VALUES
(1, 'checking', '1234567890', 12450.75),
(1, 'savings', '1234567891', 28750.00),
(1, 'credit', '1234567892', 6381.75);

-- Insert sample savings goals
INSERT INTO savings_goals (user_id, goal_name, target_amount, current_amount, target_date, category) VALUES
(1, 'Vacation Fund', 5000.00, 3750.00, '2025-12-31', 'travel'),
(1, 'Emergency Fund', 10000.00, 4500.00, '2026-06-30', 'emergency'),
(1, 'New Car', 30000.00, 6000.00, '2026-12-31', 'vehicle');

-- Insert sample contacts
INSERT INTO contacts (user_id, contact_name, phone_number, avatar_initials, is_favorite) VALUES
(1, 'Sarah Johnson', '+1-555-123-4567', 'SJ', TRUE),
(1, 'Mike Chen', '+1-555-987-6543', 'MC', FALSE),
(1, 'Emma Davis', '+1-555-456-7890', 'ED', TRUE);
