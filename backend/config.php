<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nexo_banking');

// Session Configuration
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
define('REMEMBER_ME_LIFETIME', 3600 * 24 * 30); // 30 days

// Security Configuration
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/profiles/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Application URLs
define('BASE_URL', 'http://localhost/Nexo-Banking');
define('DASHBOARD_URL', BASE_URL . '/Pages/dashboard/Dashboard.php');
define('LOGIN_URL', BASE_URL . '/Pages/auth/login.php');
define('ADMIN_DASHBOARD_URL', BASE_URL . '/admin/dashboard.php');
define('ADMIN_LOGIN_URL', BASE_URL . '/Pages/auth/login.php'); // Admins use the same login page

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Database Connection
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $conn;
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please try again later.'
        ]));
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
