<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/_header.php';
?>
<div id="errorContainer"></div>
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
                <div class="card-number" id="totalUsers">
                    <div class="loading-spinner-small"></div>
                </div>
                <div class="card-label">Total Users</div>
                <div class="card-change" id="userGrowth">--</div>
            </div>
        </div>
        <div class="overview-card transactions">
            <div class="card-icon"><i class="ri-exchange-line"></i></div>
            <div class="card-content">
                <div class="card-number" id="totalTransactions">
                    <div class="loading-spinner-small"></div>
                </div>
                <div class="card-label">Total Transactions</div>
                <div class="card-change" id="transactionGrowth">--</div>
            </div>
        </div>
        <div class="overview-card accounts">
            <div class="card-icon"><i class="ri-bank-card-line"></i></div>
            <div class="card-content">
                <div class="card-number" id="activeAccounts">
                    <div class="loading-spinner-small"></div>
                </div>
                <div class="card-label">Active Accounts</div>
                <div class="card-change" id="accountGrowth">--</div>
            </div>
        </div>
        <div class="overview-card revenue">
            <div class="card-icon"><i class="ri-money-dollar-circle-line"></i></div>
            <div class="card-content">
                <div class="card-number" id="monthlyRevenue">
                    <div class="loading-spinner-small"></div>
                </div>
                <div class="card-label">Monthly Revenue</div>
                <div class="card-change" id="revenueGrowth">--</div>
            </div>
        </div>
    </div>

    <div class="admin-grid">
        <!-- Recent Activity -->
        <div class="admin-widget recent-activity">
            <div class="widget-header">
                <h3>Recent Activity</h3>
                <a href="audit-logs.php" class="view-all">View All</a>
            </div>
            <div class="activity-list" id="recentActivityList">
                <div class="loading-spinner-small" style="margin: 20px auto;"></div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="admin-widget quick-stats">
            <div class="widget-header">
                <h3>Quick Stats</h3>
            </div>
            <div class="stats-list">
                <div class="stat-item">
                    <div class="stat-label">Pending Users</div>
                    <div class="stat-value" id="pendingUsers">
                        <div class="loading-spinner-small"></div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Pending Loans</div>
                    <div class="stat-value" id="pendingLoans">
                        <div class="loading-spinner-small"></div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Last Updated</div>
                    <div class="stat-value" id="lastUpdated">--</div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/admin-dashboard.js"></script>
<?php include __DIR__ . '/_footer.php';
