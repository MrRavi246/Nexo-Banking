<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/_header.php';
?>

    <section class="admin-content">
        <h1>Support Tickets</h1>
        <p>List and manage user support tickets.</p>

        <div class="admin-info">Placeholder ticket list. Connect to your ticketing table to show real tickets, reply and change status.</div>

        <div style="overflow:auto;margin-top:12px">
            <table class="admin-table">
                <thead><tr><th>Ticket #</th><th>User</th><th>Subject</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    <tr><td colspan="6">No tickets in demo mode.</td></tr>
                </tbody>
            </table>
        </div>
    </section>

<?php include __DIR__ . '/_footer.php';
