<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$conn = getDBConnection();

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo "Not authenticated";
    exit;
}
if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    http_response_code(401);
    echo "Invalid admin session";
    exit;
}

$days = isset($_GET['days']) ? intval($_GET['days']) : 0; // not used but accepted for parity

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="system-settings-' . date('Ymd') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['setting_id','setting_key','setting_value','description','is_active','created_at','updated_at']);

$sql = "SELECT setting_id, setting_key, setting_value, description, is_active, created_at, updated_at FROM system_settings ORDER BY setting_key";
$stmt = $conn->prepare($sql);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($out, [$row['setting_id'], $row['setting_key'], $row['setting_value'], $row['description'], $row['is_active'], $row['created_at'], $row['updated_at']]);
}

fclose($out);
exit;

?>
