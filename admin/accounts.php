<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/_header.php';
?>

    <section class="admin-content">
        <h1>Account Management</h1>
        <p>Manage user accounts: view, freeze, close or inspect account details.</p>

        <div class="admin-info">This is a placeholder. Connect to your `accounts` table to list real accounts.</div>

        <div style="overflow:auto;margin-top:16px">
            <table class="admin-table">
                <thead>
                    <tr><th>Account No</th><th>User</th><th>Type</th><th>Balance</th><th>Status</th><th>Created</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <tr><td colspan="7">No live data in demo mode. Implement DB query to populate accounts here.</td></tr>
                </tbody>
            </table>
        </div>
    </section>

<?php include __DIR__ . '/_footer.php';
