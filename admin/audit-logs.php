<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/_header.php';
?>

    <section class="admin-content">
        <h1>Audit Logs</h1>
        <p>System audit logs and administrative actions.</p>

        <div class="admin-info">Placeholder audit log viewer. Hook into your logging system (DB, files or SIEM) to populate this table.</div>

        <div style="overflow:auto;margin-top:12px">
            <table class="admin-table">
                <thead><tr><th>Time</th><th>User</th><th>Action</th><th>IP</th><th>Details</th></tr></thead>
                <tbody>
                    <tr><td colspan="5">No logs in demo mode.</td></tr>
                </tbody>
            </table>
        </div>
    </section>

<?php include __DIR__ . '/_footer.php';
