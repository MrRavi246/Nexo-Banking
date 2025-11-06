<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/_header.php';
?>

    <section class="admin-content">
        <h1>Settings</h1>
        <p>Site-level settings will be here. This is a placeholder page.</p>
    </section>

<?php include __DIR__ . '/_footer.php';
