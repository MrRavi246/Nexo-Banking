<?php
// Enable error reporting to catch all issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Step 1: Checking config.php...\n";
require_once 'config.php';
echo "✓ config.php loaded successfully\n\n";

echo "Step 2: Checking functions.php...\n";
require_once 'functions.php';
echo "✓ functions.php loaded successfully\n\n";

echo "Step 3: Checking session...\n";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✓ Session is active\n";
    echo "Session ID: " . session_id() . "\n";
} else {
    echo "✗ Session is NOT active\n";
}
echo "\n";

echo "Step 4: Checking login status...\n";
echo "user_id in session: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "\n";
echo "session_token in session: " . (isset($_SESSION['session_token']) ? 'SET' : 'NOT SET') . "\n";
echo "isLoggedIn(): " . (isLoggedIn() ? 'TRUE' : 'FALSE') . "\n\n";

echo "Step 5: Testing database connection...\n";
try {
    $conn = getDBConnection();
    echo "✓ Database connection successful\n";
    echo "Database name: " . DB_NAME . "\n\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n\n";
}

echo "Step 6: Testing JSON output...\n";
// Clear any previous output
ob_clean();
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Test successful',
    'data' => [
        'session_active' => session_status() === PHP_SESSION_ACTIVE,
        'logged_in' => isLoggedIn(),
        'user_id' => $_SESSION['user_id'] ?? null
    ]
]);
?>
