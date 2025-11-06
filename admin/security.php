<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/_header.php';
?>

    <section class="admin-content">
        <h1>Security & Compliance</h1>
        <p>Manage security policies, blocked IPs, audit rules and compliance settings.</p>

        <div class="admin-info">Security center placeholder — implement blocklists, MFA settings, and policy toggles here.</div>

        <div style="margin-top:16px">
            <h3>Blocked IPs</h3>
            <div style="overflow:auto">
                <table class="admin-table">
                    <thead><tr><th>IP</th><th>Reason</th><th>Blocked At</th><th>Actions</th></tr></thead>
                    <tbody><tr><td colspan="4">No blocked IPs in demo mode.</td></tr></tbody>
                </table>
            </div>
        </div>
    </section>

<?php include __DIR__ . '/_footer.php';
