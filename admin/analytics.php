<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/_header.php';
?>

    <section class="admin-content">
        <h1>Analytics & Reports</h1>
        <p>System analytics, charts and downloadable reports.</p>

        <div class="admin-info">Placeholder analytics dashboard — wire up with charting and report export (CSV/PDF) when ready.</div>

        <div style="margin-top:16px">
            <canvas id="analyticsChart" style="width:100%;height:320px;background:rgba(255,255,255,0.02);border-radius:8px"></canvas>
        </div>
    </section>

<?php include __DIR__ . '/_footer.php';
