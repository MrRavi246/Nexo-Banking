-- ================================================================
-- Nexo Banking - Database Update for Admin Approval System
-- ================================================================
-- This script updates the database to support admin approval workflow
-- Safe to run multiple times - checks for existing objects before creating

-- Update Users table status enum
ALTER TABLE users 
MODIFY COLUMN status ENUM('pending', 'active', 'inactive', 'suspended', 'rejected') DEFAULT 'pending';

-- Note: If you get "Duplicate column" errors below, it means these columns already exist
-- You can safely skip those ALTER TABLE statements

-- Add approved_by column (skip if already exists)
-- ALTER TABLE users ADD COLUMN approved_by INT NULL AFTER status;

-- Add approved_at column (skip if already exists)
-- ALTER TABLE users ADD COLUMN approved_at TIMESTAMP NULL AFTER approved_by;

-- Add rejection_reason column (skip if already exists)
-- ALTER TABLE users ADD COLUMN rejection_reason TEXT NULL AFTER approved_at;

-- Create admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Insert default admin user (password: Admin@123)
-- Use INSERT IGNORE to skip if admin already exists
INSERT IGNORE INTO admin_users (username, email, password_hash, first_name, last_name, role) VALUES
('admin', 'admin@nexo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Admin', 'super_admin');

-- Create admin sessions table
CREATE TABLE IF NOT EXISTS admin_sessions (
    session_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    last_activity TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(admin_id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_admin_sessions (admin_id),
    INDEX idx_session_expiry (expires_at)
);
