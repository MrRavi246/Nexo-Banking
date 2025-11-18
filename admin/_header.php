<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NEXO Admin</title>
    <link rel="stylesheet" href="../assets/style/nav.css">
    <link rel="stylesheet" href="../assets/style/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/media/svgs/favicon-white-1.svg" type="image/x-icon">
</head>
<body>

<nav class="admin-nav">
    <div class="nav-left">
        <div class="logo">
            <a href="../index.html"><span>N</span>exo</a>
            <span class="admin-badge">ADMIN</span>
        </div>
    </div>
    <div class="nav-center">
        <div class="search-bar">
            <i class="ri-search-line"></i>
            <input type="text" placeholder="Search users, transactions, accounts...">
        </div>
    </div>
    <div class="nav-right">
        <div class="nav-icons">
            <div class="nav-icon notif-wrapper">
                <button id="notifToggle" class="notif-btn" aria-expanded="false" aria-haspopup="true">
                    <i class="ri-notification-3-line"></i>
                    <span class="notification-badge" id="notifBadge">5</span>
                </button>
                <div id="notifDropdown" class="notification-dropdown" aria-hidden="true">
                    <div class="dropdown-header">
                        <span>System Alerts</span>
                        <button id="clearAll" class="clear-btn">Clear</button>
                    </div>
                    <div class="dropdown-list"></div>
                    <div class="dropdown-footer"><a href="#" class="view-all-notifs">View all alerts</a></div>
                </div>
            </div>
            <div class="nav-icon settings-wrapper">
                <button id="settingsToggle" class="notif-btn" aria-expanded="false" aria-haspopup="true">
                    <i class="ri-settings-3-line"></i>
                </button>
                <div id="settingsDropdown" class="nav-dropdown settings-dropdown" aria-hidden="true">
                    <div class="dropdown-list settings-list">
                        <a href="#" class="settings-item">Admin Profile</a>
                        <a href="#" class="settings-item">System Settings</a>
                        <a href="#" class="settings-item">Security Settings</a>
                        <div class="settings-item">
                            <label class="settings-toggle"><input type="checkbox" id="toggleMaintenanceMode"><span>Maintenance Mode</span></label>
                        </div>
                        <a href="logout.php" class="settings-item">Logout</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="user-profile">
            <img src="../assets/media/user-avatar.jpg" alt="Admin Avatar" class="avatar">
            <div class="user-info">
                <span class="username"><?=htmlspecialchars($_SESSION['admin_username'] ?? 'Admin User')?></span>
                <span class="user-type">System Administrator</span>
            </div>
            <i class="ri-arrow-down-s-line"></i>
        </div>
    </div>
</nav>

<div class="admin-container">
    <aside class="sidebar">
        <div class="sidebar-menu">
            <div class="menu-item"><i class="ri-dashboard-3-line"></i><a href="dashboard.php">Dashboard</a></div>
            <div class="menu-item"><i class="ri-group-line"></i><a href="users.php">User Management</a></div>
            <div class="menu-item"><i class="ri-exchange-line"></i><a href="transactions.php">Transactions</a></div>
            <div class="menu-item"><i class="ri-bank-card-line"></i><a href="accounts.php">Account Management</a></div>
            <div class="menu-item"><i class="ri-hand-coin-line"></i><a href="loan_applications.php">Loan Applications</a></div>
            <div class="menu-item"><i class="ri-pie-chart-line"></i><a href="analytics.php">Analytics & Reports</a></div>
            <div class="menu-item"><i class="ri-shield-check-line"></i><a href="security.php">Security & Compliance</a></div>
            <div class="menu-item"><i class="ri-settings-3-line"></i><a href="settings.php">System Settings</a></div>
            <div class="menu-item"><i class="ri-customer-service-2-line"></i><a href="support.php">Support</a></div>
            <div class="menu-item"><i class="ri-file-text-line"></i><a href="audit-logs.php">Audit Logs</a></div>
        </div>
        <div class="sidebar-footer">
            <div class="system-status"><div class="status-indicator online"></div><span>System Online</span></div>
        </div>
    </aside>

    <main class="admin-main">
        <!-- page content starts here -->
