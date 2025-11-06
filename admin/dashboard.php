<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/_header.php';
?>
<div class="container">
    <div class="admin-header">
        <div class="header-content">
            <h1>Admin Dashboard</h1>
            <p>System overview and management console</p>
        </div>
        <div class="header-actions">
            <button class="btn-secondary" onclick="exportSystemReport()"><i class="ri-download-line"></i>Export Report</button>
            <button class="btn-primary" onclick="openQuickActions()"><i class="ri-add-line"></i>Quick Actions</button>
        </div>
    </div>

    <div class="overview-cards">
        <div class="overview-card users">
            <div class="card-icon"><i class="ri-group-line"></i></div>
            <div class="card-content">
                <div class="card-number">12,485</div>
                <div class="card-label">Total Users</div>
                <div class="card-change positive">+8.2% from last month</div>
            </div>
        </div>
        <div class="overview-card transactions">
            <div class="card-icon"><i class="ri-exchange-line"></i></div>
            <div class="card-content">
                <div class="card-number">$2.4M</div>
                <div class="card-label">Total Transactions</div>
                <div class="card-change positive">+12.5% from last month</div>
            </div>
        </div>
        <div class="overview-card accounts">
            <div class="card-icon"><i class="ri-bank-card-line"></i></div>
            <div class="card-content">
                <div class="card-number">8,927</div>
                <div class="card-label">Active Accounts</div>
                <div class="card-change positive">+5.7% from last month</div>
            </div>
        </div>
        <div class="overview-card revenue">
            <div class="card-icon"><i class="ri-money-dollar-circle-line"></i></div>
            <div class="card-content">
                <div class="card-number">$48,920</div>
                <div class="card-label">Monthly Revenue</div>
                <div class="card-change positive">+15.3% from last month</div>
            </div>
        </div>
    </div>

    <div class="admin-grid">
        <!-- Recent Activity, System Health, etc. could be inserted here -->
        <div class="admin-widget recent-activity">
            <div class="widget-header">
                <h3>Recent Activity</h3><a href="audit-logs.php" class="view-all">View All</a>
            </div>
            <div class="activity-list">
                <p>No live data in demo mode.</p>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/_footer.php';
