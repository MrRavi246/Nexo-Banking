<?php
// Ensure user is shown in navbar; analytics page previously had static markup
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/../../backend/functions.php';

// If the page should be restricted, redirect unauthenticated users
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

$conn = getDBConnection();
if (!validateSession($conn, $_SESSION['user_id'], $_SESSION['session_token'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch display name and member type
$userInfoStmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
$userInfoStmt->execute([$_SESSION['user_id']]);
$userInfo = $userInfoStmt->fetch(PDO::FETCH_ASSOC);
$displayName = 'User';
$memberType = '';
if ($userInfo) {
    $first = trim($userInfo['first_name'] ?? '');
    $last = trim($userInfo['last_name'] ?? '');
    if ($first || $last) {
        $displayName = trim($first . ' ' . $last);
    } elseif (!empty($userInfo['username'])) {
        $displayName = $userInfo['username'];
    } elseif (!empty($userInfo['email'])) {
        $displayName = $userInfo['email'];
    }
    $memberType = $userInfo['member_type'] ?? ($userInfo['role'] ?? 'Member');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Nexo Banking</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="../../assets/style/style.css"> -->
    <link rel="stylesheet" href="../../assets/style/nav.css">
    <link rel="stylesheet" href="../../assets/style/Dashboard.css">
    <link rel="stylesheet" href="../../assets/style/analytics.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <!-- <nav class="sidebar">
            <div class="nav-header">
                <img src="../../assets/media/nexo-high-resolution-logo-transparent.png" alt="Nexo Logo"
                    class="nav-logo">
                <h2>Nexo</h2>
            </div>

            <ul class="nav-links">
                <li>
                    <a href="Dashboard.php">
                        <i class="ri-dashboard-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="accounts.php">
                        <i class="ri-bank-card-line"></i>
                        <span>Accounts</span>
                    </a>
                </li>
                <li>
                    <a href="Transactions.php">
                        <i class="ri-exchange-line"></i>
                        <span>Transactions</span>
                    </a>
                </li>
                <li>
                    <a href="pay-bills.php">
                        <i class="ri-bill-line"></i>
                        <span>Pay Bills</span>
                    </a>
                </li>
                <li>
                    <a href="loans.php">
                        <i class="ri-hand-coin-line"></i>
                        <span>Loans</span>
                    </a>
                </li>
                <li class="active">
                    <a href="analytics.php">
                        <i class="ri-bar-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                </li>
            </ul>

            <div class="nav-footer">
                <a href="../auth/login.php" class="logout-btn">
                    <i class="ri-logout-box-line"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav> -->

        <nav class="dashboard-nav">
            <div class="nav-left">
                <div class="logo">
                    <a href="../../index.php"><span>N</span>exo</a>
                </div>
            </div>
            <div class="nav-center">
                <div class="search-bar">
                    <i class="ri-search-line"></i>
                    <input type="text" placeholder="Search transactions, contacts...">
                </div>
            </div>
            <div class="nav-right">
                <div class="nav-icons">
                    <div class="nav-icon notif-wrapper">
                        <button id="notifToggle" class="notif-btn" aria-expanded="false" aria-haspopup="true">
                            <i class="ri-notification-3-line"></i>
                            <span class="notification-badge" id="notifBadge">3</span>
                        </button>
                        <div id="notifDropdown" class="notification-dropdown" aria-hidden="true">
                            <div class="dropdown-header">
                                <span>Notifications</span>
                                <button id="clearAll" class="clear-btn">Clear</button>
                            </div>
                            <div class="dropdown-list">
                                <div class="notification-item unread" data-id="1">
                                    <div class="notif-left"><i class="ri-arrow-down-line"></i></div>
                                    <div class="notif-body">
                                        <div class="notif-title">Salary deposited</div>
                                        <div class="notif-time">Today • 2h ago</div>
                                    </div>
                                    <button class="mark-read" title="Mark read"><i
                                            class="ri-checkbox-blank-circle-line"></i></button>
                                </div>

                                <div class="notification-item unread" data-id="2">
                                    <div class="notif-left"><i class="ri-shopping-bag-line"></i></div>
                                    <div class="notif-body">
                                        <div class="notif-title">Payment to Amazon</div>
                                        <div class="notif-time">Today • 4h ago</div>
                                    </div>
                                    <button class="mark-read" title="Mark read"><i
                                            class="ri-checkbox-blank-circle-line"></i></button>
                                </div>

                                <div class="notification-item" data-id="3">
                                    <div class="notif-left"><i class="ri-lock-line"></i></div>
                                    <div class="notif-body">
                                        <div class="notif-title">Password changed</div>
                                        <div class="notif-time">1 day ago</div>
                                    </div>
                                    <button class="mark-read" title="Mark read"><i
                                            class="ri-checkbox-blank-circle-line"></i></button>
                                </div>
                            </div>
                            <a href="#" class="view-all-notifs">View all</a>
                        </div>
                    </div>
                    <div class="nav-icon mail-wrapper">
                        <button id="mailToggle" class="notif-btn" aria-expanded="false" aria-haspopup="true">
                            <i class="ri-mail-line"></i>
                            <span class="notification-badge" id="mailBadge">7</span>
                        </button>
                        <div id="mailDropdown" class="nav-dropdown" aria-hidden="true">
                            <div class="dropdown-header">
                                <span>Messages</span>
                                <button id="mailClear" class="clear-btn">Clear</button>
                            </div>
                            <div class="dropdown-list">
                                <div class="email-item unread" data-id="e1">
                                    <div class="notif-left"><i class="ri-user-line"></i></div>
                                    <div class="notif-body">
                                        <div class="notif-title">Statement available</div>
                                        <div class="notif-time">Today • 1h ago</div>
                                    </div>
                                    <button class="email-mark-read" title="Mark read"><i
                                            class="ri-checkbox-blank-circle-line"></i></button>
                                </div>

                                <div class="email-item unread" data-id="e2">
                                    <div class="notif-left"><i class="ri-gift-line"></i></div>
                                    <div class="notif-body">
                                        <div class="notif-title">Special offer inside</div>
                                        <div class="notif-time">Yesterday</div>
                                    </div>
                                    <button class="email-mark-read" title="Mark read"><i
                                            class="ri-checkbox-blank-circle-line"></i></button>
                                </div>

                                <div class="email-item" data-id="e3">
                                    <div class="notif-left"><i class="ri-info-line"></i></div>
                                    <div class="notif-body">
                                        <div class="notif-title">Security notice</div>
                                        <div class="notif-time">2 days ago</div>
                                    </div>
                                    <button class="email-mark-read" title="Mark read"><i
                                            class="ri-checkbox-blank-circle-line"></i></button>
                                </div>
                            </div>
                            <a href="#" class="view-all-notifs">View all messages</a>
                        </div>
                    </div>

                    <div class="nav-icon settings-wrapper">
                        <button id="settingsToggle" class="notif-btn" aria-expanded="false" aria-haspopup="true">
                            <i class="ri-settings-3-line"></i>
                        </button>
                        <div id="settingsDropdown" class="nav-dropdown settings-dropdown" aria-hidden="true">
                            <div class="dropdown-list settings-list">
                                <a href="../../pages/auth/account-type.php" class="settings-item">Profile</a>
                                <a href="#" class="settings-item" id="openAccountSettings">Account Settings</a>
                                <div class="settings-item">
                                    <label class="settings-toggle">
                                        <input type="checkbox" id="toggleEmailNotif">
                                        <span>Email notifications</span>
                                    </label>
                                </div>
                                <a href="#" class="settings-item" id="logoutLink">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="user-profile">
                    <img src="https://i.pravatar.cc/" alt="User Avatar" class="avatar">
                    <div class="user-info">
                        <span class="username"><?php echo htmlspecialchars($displayName); ?></span>
                        <span class="user-type"><?php echo htmlspecialchars($memberType); ?></span>
                    </div>
                    <i class="ri-arrow-down-s-line"></i>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <aside class="sidebar">
                <div class="sidebar-menu">
                    <div class="menu-item" onclick="window.location.href='Dashboard.php'">
                        <i class="ri-dashboard-3-line"></i>
                        <span>Dashboard</span>
                    </div>
                    <div class="menu-item ">
                        <i class="ri-bank-card-line"></i>
                        <span>Accounts</span>
                    </div>
                    <div class="menu-item" onclick="window.location.href='Transactions.php'">
                        <i class="ri-exchange-line"></i>
                        <span>Transactions</span>
                    </div>
                    <div class="menu-item" onclick="window.location.href='transfer-money.php'">
                        <i class="ri-send-plane-line"></i>
                        <span>Transfer Money</span>
                    </div>
                    <div class="menu-item" onclick="window.location.href='pay-bills.php'">
                        <i class="ri-bill-line"></i>
                        <span>Pay Bills</span>
                    </div>
                    <div class="menu-item" onclick="window.location.href='loans.php'">
                        <i class="ri-hand-coin-line"></i>
                        <span>Loans</span>
                    </div>
                    <div class="menu-item active" onclick="window.location.href='analytics.php'">
                        <i class="ri-pie-chart-line"></i>
                        <span>Analytics</span>
                    </div>
                    <div class="menu-item" onclick="window.location.href='support.php'">
                        <i class="ri-customer-service-2-line"></i>
                        <span>Support</span>
                    </div>
                </div>

                <div class="sidebar-footer" onclick="window.location.href='login.php'">
                    <div class="menu-item">
                        <i class="ri-logout-box-line"></i>
                        <span>Logout</span>
                    </div>
                </div>
            </aside>

            <div class="analytics-container">
                <!-- Header Section -->
                <div class="page-hero">
                    <div class="hero-left">
                        <h1>Analytics</h1>
                        <p>Manage your financial insights and performance tracking</p>
                    </div>
                    <div class="hero-actions">
                        <button class="btn export">
                            <i class="ri-download-line"></i>
                            Export Data
                        </button>
                        <div class="time-filter">
                            <select id="timeRange">
                                <option value="7">Last 7 Days</option>
                                <option value="30" selected>Last 30 Days</option>
                                <option value="90">Last 3 Months</option>
                                <option value="365">Last Year</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Key Metrics Overview -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-icon income">
                            <i class="ri-arrow-up-line"></i>
                        </div>
                        <div class="metric-info">
                            <h3>Total Income</h3>
                            <div class="metric-value">$12,450</div>
                            <div class="metric-change positive">
                                <i class="ri-arrow-up-s-line"></i> +12.5% from last month
                            </div>
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-icon expense">
                            <i class="ri-arrow-down-line"></i>
                        </div>
                        <div class="metric-info">
                            <h3>Total Expenses</h3>
                            <div class="metric-value">$8,320</div>
                            <div class="metric-change negative">
                                <i class="ri-arrow-down-s-line"></i> +5.2% from last month
                            </div>
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-icon savings">
                            <i class="ri-safe-line"></i>
                        </div>
                        <div class="metric-info">
                            <h3>Net Savings</h3>
                            <div class="metric-value">$4,130</div>
                            <div class="metric-change positive">
                                <i class="ri-arrow-up-s-line"></i> +22.8% from last month
                            </div>
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-icon rate">
                            <i class="ri-percent-line"></i>
                        </div>
                        <div class="metric-info">
                            <h3>Savings Rate</h3>
                            <div class="metric-value">33.2%</div>
                            <div class="metric-change positive">
                                <i class="ri-arrow-up-s-line"></i> +4.1% from last month
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-section">
                    <div class="chart-row">
                        <!-- Income vs Expenses Chart -->
                        <div class="chart-container main-chart">
                            <div class="chart-header">
                                <h3>Income vs Expenses</h3>
                                <div class="chart-legend">
                                    <div class="legend-item">
                                        <span class="legend-color income"></span>
                                        <span>Income</span>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-color expense"></span>
                                        <span>Expenses</span>
                                    </div>
                                </div>
                            </div>
                            <div class="chart-content">
                                <canvas id="incomeExpenseChart" width="400" height="200"></canvas>
                            </div>
                        </div>

                        <!-- Spending Categories Chart -->
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Spending by Category</h3>
                            </div>
                            <div class="chart-content">
                                <canvas id="categoryChart" width="300" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Cash Flow Chart -->
                    <div class="chart-container full-width">
                        <div class="chart-header">
                            <h3>Cash Flow Trend</h3>
                            <div class="chart-controls">
                                <button class="chart-btn active" data-period="daily">Daily</button>
                                <button class="chart-btn" data-period="weekly">Weekly</button>
                                <button class="chart-btn" data-period="monthly">Monthly</button>
                            </div>
                        </div>
                        <div class="chart-content">
                            <canvas id="cashFlowChart" width="800" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Insights and Recommendations -->
                <div class="insights-section">
                    <div class="insights-grid">
                        <div class="insight-card">
                            <div class="insight-header">
                                <h3>Financial Health Score</h3>
                                <div class="health-score">
                                    <div class="score-circle">
                                        <span class="score-value">85</span>
                                        <span class="score-label">Excellent</span>
                                    </div>
                                </div>
                            </div>
                            <div class="insight-content">
                                <div class="score-breakdown">
                                    <div class="score-item">
                                        <span>Debt-to-Income Ratio</span>
                                        <div class="score-bar">
                                            <div class="score-fill" style="width: 25%"></div>
                                        </div>
                                        <span>25%</span>
                                    </div>
                                    <div class="score-item">
                                        <span>Emergency Fund</span>
                                        <div class="score-bar">
                                            <div class="score-fill" style="width: 75%"></div>
                                        </div>
                                        <span>6 months</span>
                                    </div>
                                    <div class="score-item">
                                        <span>Investment Allocation</span>
                                        <div class="score-bar">
                                            <div class="score-fill" style="width: 60%"></div>
                                        </div>
                                        <span>18%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="insight-card">
                            <div class="insight-header">
                                <h3>Smart Insights</h3>
                            </div>
                            <div class="insight-content">
                                <div class="insight-list">
                                    <div class="insight-item">
                                        <i class="ri-lightbulb-line insight-icon positive"></i>
                                        <div class="insight-text">
                                            <strong>Great job!</strong> Your savings rate increased by 22.8% this month.
                                        </div>
                                    </div>
                                    <div class="insight-item">
                                        <i class="ri-alert-line insight-icon warning"></i>
                                        <div class="insight-text">
                                            <strong>Watch out:</strong> Dining out expenses are 15% higher than usual.
                                        </div>
                                    </div>
                                    <div class="insight-item">
                                        <i class="ri-target-line insight-icon info"></i>
                                        <div class="insight-text">
                                            <strong>Goal Progress:</strong> You're 68% towards your emergency fund goal.
                                        </div>
                                    </div>
                                    <div class="insight-item">
                                        <i class="ri-trending-up-line insight-icon positive"></i>
                                        <div class="insight-text">
                                            <strong>Investment Opportunity:</strong> Consider increasing your retirement contributions.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Analytics Tables -->
                <div class="tables-section">
                    <div class="table-container">
                        <div class="table-header">
                            <h3>Top Spending Categories</h3>
                            <button class="view-all-btn">View All</button>
                        </div>
                        <div class="analytics-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>% of Total</th>
                                        <th>Change</th>
                                        <th>Trend</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="category-item">
                                                <div class="category-color food"></div>
                                                <span>Food & Dining</span>
                                            </div>
                                        </td>
                                        <td>$2,450</td>
                                        <td>29.4%</td>
                                        <td class="change negative">+15%</td>
                                        <td>
                                            <div class="trend-chart">
                                                <canvas class="mini-chart" data-values="20,25,22,28,30,35,25"></canvas>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="category-item">
                                                <div class="category-color transport"></div>
                                                <span>Transportation</span>
                                            </div>
                                        </td>
                                        <td>$1,820</td>
                                        <td>21.9%</td>
                                        <td class="change positive">-5%</td>
                                        <td>
                                            <div class="trend-chart">
                                                <canvas class="mini-chart" data-values="25,20,18,22,19,18,16"></canvas>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="category-item">
                                                <div class="category-color shopping"></div>
                                                <span>Shopping</span>
                                            </div>
                                        </td>
                                        <td>$1,290</td>
                                        <td>15.5%</td>
                                        <td class="change neutral">0%</td>
                                        <td>
                                            <div class="trend-chart">
                                                <canvas class="mini-chart" data-values="15,16,14,15,16,15,15"></canvas>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="category-item">
                                                <div class="category-color utilities"></div>
                                                <span>Utilities</span>
                                            </div>
                                        </td>
                                        <td>$980</td>
                                        <td>11.8%</td>
                                        <td class="change positive">-8%</td>
                                        <td>
                                            <div class="trend-chart">
                                                <canvas class="mini-chart" data-values="12,13,11,10,11,9,10"></canvas>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="category-item">
                                                <div class="category-color entertainment"></div>
                                                <span>Entertainment</span>
                                            </div>
                                        </td>
                                        <td>$780</td>
                                        <td>9.4%</td>
                                        <td class="change positive">-12%</td>
                                        <td>
                                            <div class="trend-chart">
                                                <canvas class="mini-chart" data-values="12,10,8,9,7,8,6"></canvas>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Goal Tracking -->
                <div class="goals-section">
                    <div class="goals-header">
                        <h3>Financial Goals</h3>
                        <button class="add-goal-btn">
                            <i class="ri-add-line"></i>
                            Add Goal
                        </button>
                    </div>
                    <div class="goals-grid">
                        <div class="goal-card">
                            <div class="goal-header">
                                <div class="goal-icon">
                                    <i class="ri-home-line"></i>
                                </div>
                                <div class="goal-info">
                                    <h4>House Down Payment</h4>
                                    <p>Target: $50,000</p>
                                </div>
                            </div>
                            <div class="goal-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 68%"></div>
                                </div>
                                <div class="progress-info">
                                    <span>$34,000 saved</span>
                                    <span>68%</span>
                                </div>
                            </div>
                            <div class="goal-timeline">
                                <span>Est. completion: March 2026</span>
                            </div>
                        </div>

                        <div class="goal-card">
                            <div class="goal-header">
                                <div class="goal-icon">
                                    <i class="ri-shield-check-line"></i>
                                </div>
                                <div class="goal-info">
                                    <h4>Emergency Fund</h4>
                                    <p>Target: $15,000</p>
                                </div>
                            </div>
                            <div class="goal-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 85%"></div>
                                </div>
                                <div class="progress-info">
                                    <span>$12,750 saved</span>
                                    <span>85%</span>
                                </div>
                            </div>
                            <div class="goal-timeline">
                                <span>Est. completion: November 2025</span>
                            </div>
                        </div>

                        <div class="goal-card">
                            <div class="goal-header">
                                <div class="goal-icon">
                                    <i class="ri-plane-line"></i>
                                </div>
                                <div class="goal-info">
                                    <h4>Vacation Fund</h4>
                                    <p>Target: $8,000</p>
                                </div>
                            </div>
                            <div class="goal-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 42%"></div>
                                </div>
                                <div class="progress-info">
                                    <span>$3,360 saved</span>
                                    <span>42%</span>
                                </div>
                            </div>
                            <div class="goal-timeline">
                                <span>Est. completion: June 2026</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/analytics.js"></script>
</body>

</html>