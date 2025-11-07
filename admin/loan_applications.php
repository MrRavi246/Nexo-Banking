<?php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/functions.php';

if (!isAdminLoggedIn()) {
    header('Location: ' . ADMIN_LOGIN_URL);
    exit();
}

$conn = getDBConnection();
if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    header('Location: ' . ADMIN_LOGIN_URL);
    exit();
}

?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin - Loan Applications</title>
    <link rel="stylesheet" href="/Nexo-Banking/assets/style/admin.css">
    <script src="/Nexo-Banking/assets/js/admin-loans.js" defer></script>
</head>
<body>
    <div class="admin-container">
        <h1>Loan Applications</h1>
        <div>
            <label for="statusFilter">Status:</label>
            <select id="statusFilter" onchange="loadApplications()">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="paid">Paid</option>
            </select>
        </div>

        <div id="applicationsContainer" style="margin-top:1rem;"></div>
    </div>
</body>
</html>
