<?php
// Authentication check
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/../../backend/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Validate session
$conn = getDBConnection();
if (!validateSession($conn, $_SESSION['user_id'], $_SESSION['session_token'])) {
    session_unset();
    session_destroy();
    header('Location: ../auth/login.php');
    exit();
}

// Check if user account is still active
$stmt = $conn->prepare("SELECT status FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['status'] !== 'active') {
    session_unset();
    session_destroy();
    header('Location: ../auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../../assets/style/nav.css">
    <link rel="stylesheet" href="../../assets/style/Dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <title>NEXO Dashboard - Your Banking Overview</title>
</head>

<body>
    <!-- Dashboard Navbar -->
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
                                <button class="mark-read" title="Mark read"><i class="ri-checkbox-blank-circle-line"></i></button>
                            </div>

                            <div class="notification-item unread" data-id="2">
                                <div class="notif-left"><i class="ri-shopping-bag-line"></i></div>
                                <div class="notif-body">
                                    <div class="notif-title">Payment to Amazon</div>
                                    <div class="notif-time">Today • 4h ago</div>
                                </div>
                                <button class="mark-read" title="Mark read"><i class="ri-checkbox-blank-circle-line"></i></button>
                            </div>

                            <div class="notification-item" data-id="3">
                                <div class="notif-left"><i class="ri-lock-line"></i></div>
                                <div class="notif-body">
                                    <div class="notif-title">Password changed</div>
                                    <div class="notif-time">1 day ago</div>
                                </div>
                                <button class="mark-read" title="Mark read"><i class="ri-checkbox-blank-circle-line"></i></button>
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
                                <button class="email-mark-read" title="Mark read"><i class="ri-checkbox-blank-circle-line"></i></button>
                            </div>

                            <div class="email-item unread" data-id="e2">
                                <div class="notif-left"><i class="ri-gift-line"></i></div>
                                <div class="notif-body">
                                    <div class="notif-title">Special offer inside</div>
                                    <div class="notif-time">Yesterday</div>
                                </div>
                                <button class="email-mark-read" title="Mark read"><i class="ri-checkbox-blank-circle-line"></i></button>
                            </div>

                            <div class="email-item" data-id="e3">
                                <div class="notif-left"><i class="ri-info-line"></i></div>
                                <div class="notif-body">
                                    <div class="notif-title">Security notice</div>
                                    <div class="notif-time">2 days ago</div>
                                </div>
                                <button class="email-mark-read" title="Mark read"><i class="ri-checkbox-blank-circle-line"></i></button>
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
                    <span class="username">John Doe</span>
                    <span class="user-type">Premium Member</span>
                </div>
                <i class="ri-arrow-down-s-line"></i>
            </div>
        </div>
    </nav>

    <!-- Dashboard Sidebar -->
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-menu">
                <div class="menu-item active" onclick="window.location.href='Dashboard.php'">
                    <i class="ri-dashboard-3-line"></i>
                    <span>Dashboard</span>
                </div>
                <div class="menu-item " onclick="window.location.href='accounts.php'">
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
                <div class="menu-item" onclick="window.location.href='analytics.php'">
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

        <!-- Main Dashboard Content -->
        <main class="dashboard-main">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <!-- User Profile -->
                <div class="user-profile">
                    <div class="profile-image">
                        <img src="https://i.pravatar.cc/" alt="User Profile" id="profileImage">
                    </div>
                    <div class="profile-info">
                        <h1>Welcome back, Alex!</h1>
                        <p>Here's your financial overview for today</p>
                    </div>
                    <div class="profile-notifications">
                        <!-- <button class="notification-btn">
              <i class="ri-notification-3-line"></i>
              <span class="notification-badge">3</span>
            </button> -->
                    </div>
                </div>
                <div class="header-actions">
                    <button class="btn-primary" onclick="window.location.href='transfer-money.php'">
            <i class="ri-send-plane-line"></i>
            Transfer Money
          </button>
                    <button class="btn-secondary">
            <i class="ri-download-line"></i>
            Download Report
          </button>
                </div>
            </div>

            <!-- Account Summary Cards -->
            <div class="account-summary">
                <div class="summary-card total-balance">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="ri-wallet-3-line"></i>
                        </div>
                        <div class="card-menu">
                            <i class="ri-more-line"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3>Total Balance</h3>
                        <div class="balance-amount">$47,582.50</div>
                        <div class="balance-change positive">
                            <i class="ri-arrow-up-line"></i> +2.5% from last month
                        </div>
                    </div>
                </div>

                <div class="summary-card checking">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="ri-bank-card-line"></i>
                        </div>
                        <div class="card-menu">
                            <i class="ri-more-line"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3>Checking Account</h3>
                        <div class="balance-amount">$12,450.75</div>
                        <div class="account-number">**** 4892</div>
                    </div>
                </div>

                <div class="summary-card savings">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="ri-bank-line"></i>
                        </div>
                        <div class="card-menu">
                            <i class="ri-more-line"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3>Savings Account</h3>
                        <div class="balance-amount">$28,750.00</div>
                        <div class="interest-rate">2.5% APY</div>
                    </div>
                </div>

                <div class="summary-card credit">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="ri-mastercard-fill"></i>
                        </div>
                        <div class="card-menu">
                            <i class="ri-more-line"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3>Credit Card</h3>
                        <div class="balance-amount">$6,381.75</div>
                        <div class="credit-limit">$15,000 limit</div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content Grid -->
            <div class="dashboard-grid">
                <div class="dashboard-widget quick-actions">
                    <div class="widget-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="widget-content">
                        <div class="action-grid">
                            <div class="action-item send-money">
                                <div class="action-icon">
                                    <i class="ri-send-plane-line"></i>
                                </div>
                                <span>Send Money</span>
                            </div>
                            <div class="action-item request-money">
                                <div class="action-icon">
                                    <i class="ri-qr-code-line"></i>
                                </div>
                                <span>Request Money</span>
                            </div>
                            <div class="action-item pay-bills">
                                <div class="action-icon">
                                    <i class="ri-bill-line"></i>
                                </div>
                                <span>Pay Bills</span>
                            </div>
                            <div class="action-item mobile-recharge">
                                <div class="action-icon">
                                    <i class="ri-smartphone-line"></i>
                                </div>
                                <span>Mobile Recharge</span>
                            </div>
                            <div class="action-item currency-exchange">
                                <div class="action-icon">
                                    <i class="ri-exchange-line"></i>
                                </div>
                                <span>Currency Exchange</span>
                            </div>

                            <div class="action-item auto-pay">
                                <div class="action-icon">
                                    <i class="ri-repeat-line"></i>
                                </div>
                                <span>Auto Pay</span>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- Spending Overview Chart -->
                <div class="dashboard-widget spending-chart">
                    <div class="widget-header">
                        <h3>Spending Overview</h3>
                        <div class="widget-controls">
                            <select class="time-filter">
                <option>This Month</option>
                <option>Last Month</option>
                <option>Last 3 Months</option>
              </select>
                        </div>
                    </div>
                    <div class="widget-content">
                        <div class="chart-container">
                            <canvas id="spendingChart"></canvas>
                        </div>
                        <div class="spending-categories">
                            <div class="category-item">
                                <div class="category-color food"></div>
                                <span>Food & Dining</span>
                                <span class="amount">$1,245</span>
                            </div>
                            <div class="category-item">
                                <div class="category-color shopping"></div>
                                <span>Shopping</span>
                                <span class="amount">$890</span>
                            </div>
                            <div class="category-item">
                                <div class="category-color transport"></div>
                                <span>Transportation</span>
                                <span class="amount">$456</span>
                            </div>
                            <div class="category-item">
                                <div class="category-color bills"></div>
                                <span>Bills & Utilities</span>
                                <span class="amount">$2,100</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="dashboard-widget transactions">
                    <div class="widget-header">
                        <h3>Recent Transactions</h3>
                        <a href="#" class="view-all">View All</a>
                    </div>
                    <div class="widget-content">
                        <div class="transaction-list">
                            <div class="transaction-item">
                                <div class="transaction-icon amazon">
                                    <i class="ri-shopping-bag-line"></i>
                                </div>
                                <div class="transaction-details">
                                    <div class="transaction-title">Amazon Purchase</div>
                                    <div class="transaction-date">Today, 2:30 PM</div>
                                </div>
                                <div class="transaction-amount negative">-$89.99</div>
                            </div>

                            <div class="transaction-item">
                                <div class="transaction-icon gas">
                                    <i class="ri-gas-station-line"></i>
                                </div>
                                <div class="transaction-details">
                                    <div class="transaction-title">Shell Gas Station</div>
                                    <div class="transaction-date">Yesterday, 8:15 AM</div>
                                </div>
                                <div class="transaction-amount negative">-$45.20</div>
                            </div>

                            <div class="transaction-item">
                                <div class="transaction-icon salary">
                                    <i class="ri-arrow-down-line"></i>
                                </div>
                                <div class="transaction-details">
                                    <div class="transaction-title">Salary Deposit</div>
                                    <div class="transaction-date">2 days ago</div>
                                </div>
                                <div class="transaction-amount positive">+$3,500.00</div>
                            </div>

                            <div class="transaction-item">
                                <div class="transaction-icon coffee">
                                    <i class="ri-cup-line"></i>
                                </div>
                                <div class="transaction-details">
                                    <div class="transaction-title">Starbucks Coffee</div>
                                    <div class="transaction-date">3 days ago</div>
                                </div>
                                <div class="transaction-amount negative">-$12.45</div>
                            </div>

                            <div class="transaction-item">
                                <div class="transaction-icon transfer">
                                    <i class="ri-send-plane-line"></i>
                                </div>
                                <div class="transaction-details">
                                    <div class="transaction-title">Transfer to Sarah</div>
                                    <div class="transaction-date">4 days ago</div>
                                </div>
                                <div class="transaction-amount negative">-$250.00</div>
                            </div>

                            <div class="transaction-item">
                                <div class="transaction-icon netflix">
                                    <i class="ri-tv-line"></i>
                                </div>
                                <div class="transaction-details">
                                    <div class="transaction-title">Netflix Subscription</div>
                                    <div class="transaction-date">5 days ago</div>
                                </div>
                                <div class="transaction-amount negative">-$15.99</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->

                <!-- Savings Goals -->
                <div class="dashboard-widget savings-goals">
                    <div class="widget-header">
                        <h3>Savings Goals</h3>
                        <button class="add-goal-btn">
              <i class="ri-add-line"></i>
            </button>
                    </div>
                    <div class="widget-content">
                        <div class="goal-item vacation">
                            <div class="goal-info">
                                <div class="goal-title">Vacation Fund</div>
                                <div class="goal-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill vacation-progress" style="width: 75%"></div>
                                    </div>
                                    <div class="progress-text">$3,750 / $5,000</div>
                                </div>
                            </div>
                            <div class="goal-percentage vacation-color">75%</div>
                        </div>

                        <div class="goal-item emergency">
                            <div class="goal-info">
                                <div class="goal-title">Emergency Fund</div>
                                <div class="goal-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill emergency-progress" style="width: 45%"></div>
                                    </div>
                                    <div class="progress-text">$4,500 / $10,000</div>
                                </div>
                            </div>
                            <div class="goal-percentage emergency-color">45%</div>
                        </div>

                        <div class="goal-item car">
                            <div class="goal-info">
                                <div class="goal-title">New Car</div>
                                <div class="goal-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill car-progress" style="width: 20%"></div>
                                    </div>
                                    <div class="progress-text">$6,000 / $30,000</div>
                                </div>
                            </div>
                            <div class="goal-percentage car-color">20%</div>
                        </div>
                    </div>
                </div>

                <!-- Credit Score -->

                <!-- Monthly Budget -->
                <div class="dashboard-widget monthly-budget">
                    <div class="widget-header">
                        <h3>Monthly Budget</h3>
                        <div class="budget-status good">On Track</div>
                    </div>
                    <div class="widget-content">
                        <div class="budget-overview">
                            <div class="budget-amount">
                                <div class="spent">$3,120</div>
                                <div class="total">/ $4,500 budgeted</div>
                            </div>
                            <div class="budget-percentage">69% used</div>
                        </div>
                        <div class="budget-progress">
                            <div class="budget-bar">
                                <div class="budget-fill" style="width: 69%"></div>
                            </div>
                        </div>
                        <div class="budget-categories">
                            <div class="budget-category">
                                <span class="category-name">Groceries</span>
                                <span class="category-spent">$820</span>
                                <span class="category-budget">/ $1,000</span>
                            </div>
                            <div class="budget-category">
                                <span class="category-name">Entertainment</span>
                                <span class="category-spent">$345</span>
                                <span class="category-budget">/ $500</span>
                            </div>
                            <div class="budget-category">
                                <span class="category-name">Transport</span>
                                <span class="category-spent">$280</span>
                                <span class="category-budget">/ $400</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-widget credit-score">
                    <div class="widget-header">
                        <h3>Credit Score</h3>
                        <div class="score-date">Updated 2 days ago</div>
                    </div>
                    <div class="widget-content">
                        <div class="score-display">
                            <div class="score-circle">
                                <svg viewBox="0 0 100 100">
                  <circle cx="50" cy="50" r="45" fill="none" stroke="#333" stroke-width="6" />
                  <circle cx="50" cy="50" r="45" fill="none" stroke="#10b981" stroke-width="6" stroke-dasharray="283"
                    stroke-dashoffset="70" transform="rotate(-90 50 50)" />
                </svg>
                                <div class="score-number">742</div>
                                <div class="score-label">Excellent</div>
                            </div>
                        </div>
                        <div class="score-insights">
                            <div class="insight-item positive">
                                <i class="ri-arrow-up-line"></i>
                                <span>Score increased by 12 points</span>
                            </div>
                            <div class="insight-item tip">
                                <i class="ri-lightbulb-line"></i>
                                <span>Pay down credit card balance to improve score</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Quick Transfer Modal -->
    <div id="transferModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Quick Transfer</h2>
                <span class="close" onclick="closeTransferModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="transferForm">
                    <!-- From Account Selection -->
                    <div class="form-group">
                        <label for="fromAccount">From Account</label>
                        <select id="fromAccount" class="form-control">
              <option value="checking">Checking Account - **** 4892 ($12,450.75)</option>
              <option value="savings">Savings Account - **** 7321 ($28,750.00)</option>
            </select>
                    </div>

                    <!-- Transfer Type -->
                    <div class="form-group">
                        <label>Transfer Type</label>
                        <div class="transfer-type-options">
                            <div class="transfer-option active" data-type="contact">
                                <i class="ri-user-line"></i>
                                <span>To Contact</span>
                            </div>
                            <div class="transfer-option" data-type="account">
                                <i class="ri-bank-line"></i>
                                <span>To Account</span>
                            </div>
                            <div class="transfer-option" data-type="phone">
                                <i class="ri-smartphone-line"></i>
                                <span>Phone Number</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recipient Section -->
                    <div id="contactSection" class="recipient-section">
                        <div class="form-group">
                            <label for="recipient">Send to</label>
                            <div class="recipient-search">
                                <input type="text" id="recipient" class="form-control" placeholder="Search contacts or enter name">
                                <i class="ri-search-line"></i>
                            </div>
                            <div class="recent-contacts">
                                <div class="contact-item" onclick="selectContact('Sarah Johnson', '+1 (555) 123-4567')">
                                    <div class="contact-avatar">SJ</div>
                                    <div class="contact-info">
                                        <span class="contact-name">Sarah Johnson</span>
                                        <span class="contact-phone">+1 (555) 123-4567</span>
                                    </div>
                                </div>
                                <div class="contact-item" onclick="selectContact('Mike Chen', '+1 (555) 987-6543')">
                                    <div class="contact-avatar">MC</div>
                                    <div class="contact-info">
                                        <span class="contact-name">Mike Chen</span>
                                        <span class="contact-phone">+1 (555) 987-6543</span>
                                    </div>
                                </div>
                                <div class="contact-item" onclick="selectContact('Emma Davis', '+1 (555) 456-7890')">
                                    <div class="contact-avatar">ED</div>
                                    <div class="contact-info">
                                        <span class="contact-name">Emma Davis</span>
                                        <span class="contact-phone">+1 (555) 456-7890</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="accountSection" class="recipient-section" style="display: none;">
                        <div class="form-group">
                            <label for="accountNumber">Account Number</label>
                            <input type="text" id="accountNumber" class="form-control" placeholder="Enter account number">
                        </div>
                        <div class="form-group">
                            <label for="routingNumber">Routing Number</label>
                            <input type="text" id="routingNumber" class="form-control" placeholder="Enter routing number">
                        </div>
                        <div class="form-group">
                            <label for="recipientName">Recipient Name</label>
                            <input type="text" id="recipientName" class="form-control" placeholder="Enter recipient name">
                        </div>
                    </div>

                    <div id="phoneSection" class="recipient-section" style="display: none;">
                        <div class="form-group">
                            <label for="phoneNumber">Phone Number</label>
                            <input type="tel" id="phoneNumber" class="form-control" placeholder="+1 (555) 123-4567">
                        </div>
                    </div>

                    <!-- Amount -->
                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <div class="amount-input">
                            <span class="currency">$</span>
                            <input type="number" id="amount" class="form-control" placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="quick-amounts">
                            <button type="button" class="quick-amount" onclick="setAmount(50)">$50</button>
                            <button type="button" class="quick-amount" onclick="setAmount(100)">$100</button>
                            <button type="button" class="quick-amount" onclick="setAmount(250)">$250</button>
                            <button type="button" class="quick-amount" onclick="setAmount(500)">$500</button>
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="form-group">
                        <label for="message">Message (Optional)</label>
                        <textarea id="message" class="form-control" placeholder="What's this for?" rows="3"></textarea>
                    </div>

                    <!-- Transfer Summary -->
                    <div class="transfer-summary">
                        <div class="summary-item">
                            <span>Transfer Amount</span>
                            <span id="summaryAmount">$0.00</span>
                        </div>
                        <div class="summary-item">
                            <span>Transfer Fee</span>
                            <span class="free">Free</span>
                        </div>
                        <div class="summary-item total">
                            <span>Total</span>
                            <span id="summaryTotal">$0.00</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeTransferModal()">Cancel</button>
                <button type="button" class="btn-transfer" onclick="processTransfer()">
          <i class="ri-send-plane-line"></i>
          Send Transfer
        </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Dashboard functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts
            initializeSpendingChart();

            // Add menu interactions
            initializeMenuInteractions();
        });

        function initializeSpendingChart() {
            const ctx = document.getElementById('spendingChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Food & Dining', 'Shopping', 'Transportation', 'Bills & Utilities'],
                    datasets: [{
                        data: [1245, 890, 456, 2100],
                        backgroundColor: ['#eb7ef2', '#9333ea', '#6366f1', '#3b82f6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        function initializeMenuInteractions() {
            const menuItems = document.querySelectorAll('.menu-item');

            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    menuItems.forEach(mi => mi.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        }

        // Quick Transfer Modal Functions
        function openTransferModal() {
            document.getElementById('transferModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeTransferModal() {
            document.getElementById('transferModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            resetTransferForm();
        }

        function resetTransferForm() {
            document.getElementById('transferForm').reset();
            document.getElementById('summaryAmount').textContent = '$0.00';
            document.getElementById('summaryTotal').textContent = '$0.00';

            // Reset transfer type to contact
            const transferOptions = document.querySelectorAll('.transfer-option');
            transferOptions.forEach(option => option.classList.remove('active'));
            transferOptions[0].classList.add('active');

            // Show contact section, hide others
            document.getElementById('contactSection').style.display = 'block';
            document.getElementById('accountSection').style.display = 'none';
            document.getElementById('phoneSection').style.display = 'none';
        }

        function selectContact(name, phone) {
            document.getElementById('recipient').value = name;
        }

        function setAmount(amount) {
            document.getElementById('amount').value = amount;
            updateSummary();
        }

        function updateSummary() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            document.getElementById('summaryAmount').textContent = `$${amount.toFixed(2)}`;
            document.getElementById('summaryTotal').textContent = `$${amount.toFixed(2)}`;
        }

        function processTransfer() {
            const amount = document.getElementById('amount').value;
            const fromAccount = document.getElementById('fromAccount').value;
            const recipient = document.getElementById('recipient').value;

            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            if (!recipient.trim()) {
                alert('Please select a recipient');
                return;
            }

            // Simulate transfer processing
            const btn = document.querySelector('.btn-transfer');
            const originalText = btn.innerHTML;

            btn.innerHTML = '<i class="ri-loader-line spin"></i> Processing...';
            btn.disabled = true;

            setTimeout(() => {
                btn.innerHTML = '<i class="ri-check-line"></i> Transfer Sent!';
                btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';

                setTimeout(() => {
                    closeTransferModal();
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    btn.style.background = '';

                    // Show success notification
                    showNotification('Transfer sent successfully!', 'success');
                }, 1500);
            }, 2000);
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
        <i class="ri-check-circle-line"></i>
        <span>${message}</span>
      `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.classList.add('show');
            }, 100);

            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Initialize modal interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Transfer type switching
            const transferOptions = document.querySelectorAll('.transfer-option');
            transferOptions.forEach(option => {
                option.addEventListener('click', function() {
                    transferOptions.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');

                    const type = this.getAttribute('data-type');
                    document.getElementById('contactSection').style.display = type === 'contact' ? 'block' : 'none';
                    document.getElementById('accountSection').style.display = type === 'account' ? 'block' : 'none';
                    document.getElementById('phoneSection').style.display = type === 'phone' ? 'block' : 'none';
                });
            });

            // Amount input listener
            document.getElementById('amount').addEventListener('input', updateSummary);

            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('transferModal');
                if (event.target === modal) {
                    closeTransferModal();
                }
            });
        });

        // Notification dropdown behavior
        (function() {
            const toggle = document.getElementById('notifToggle');
            const dropdown = document.getElementById('notifDropdown');
            const badge = document.getElementById('notifBadge');
            const clearBtn = document.getElementById('clearAll');

            // Mail elements
            const mailToggle = document.getElementById('mailToggle');
            const mailDropdown = document.getElementById('mailDropdown');
            const mailBadge = document.getElementById('mailBadge');
            const mailClear = document.getElementById('mailClear');

            // Settings
            const settingsToggle = document.getElementById('settingsToggle');
            const settingsDropdown = document.getElementById('settingsDropdown');
            const emailNotifToggle = document.getElementById('toggleEmailNotif');

            function updateBadge() {
                if (!badge || !dropdown) return;
                const unread = dropdown.querySelectorAll('.notification-item.unread').length;
                badge.textContent = unread > 0 ? unread : '';
                badge.style.display = unread > 0 ? 'inline-flex' : 'none';
            }

            if (toggle && dropdown) {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const expanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', String(!expanded));
                    dropdown.classList.toggle('open');
                    dropdown.setAttribute('aria-hidden', String(expanded));
                    // close others
                    if (mailDropdown) mailDropdown.classList.remove('open');
                    if (settingsDropdown) settingsDropdown.classList.remove('open');
                });
            }

            if (mailToggle && mailDropdown) {
                mailToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const expanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', String(!expanded));
                    mailDropdown.classList.toggle('open');
                    mailDropdown.setAttribute('aria-hidden', String(expanded));
                    if (dropdown) dropdown.classList.remove('open');
                    if (settingsDropdown) settingsDropdown.classList.remove('open');
                });
            }

            if (settingsToggle && settingsDropdown) {
                settingsToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const expanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', String(!expanded));
                    settingsDropdown.classList.toggle('open');
                    settingsDropdown.setAttribute('aria-hidden', String(expanded));
                    if (dropdown) dropdown.classList.remove('open');
                    if (mailDropdown) mailDropdown.classList.remove('open');
                });
            }

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                [dropdown, mailDropdown, settingsDropdown].forEach(d => {
                    if (!d) return;
                    if (!d.classList.contains('open')) return;
                    const parentToggle = d === dropdown ? toggle : d === mailDropdown ? mailToggle : settingsToggle;
                    if (!d.contains(e.target) && e.target !== parentToggle) {
                        d.classList.remove('open');
                        if (parentToggle) parentToggle.setAttribute('aria-expanded', 'false');
                        d.setAttribute('aria-hidden', 'true');
                    }
                });
            });

            // Mark individual as read for notifications
            document.addEventListener('click', function(e) {
                if (e.target.closest('.mark-read')) {
                    const item = e.target.closest('.notification-item');
                    if (!item) return;
                    item.classList.remove('unread');
                    const icon = item.querySelector('.mark-read i');
                    if (icon) {
                        icon.classList.remove('ri-checkbox-blank-circle-line');
                        icon.classList.add('ri-checkbox-circle-fill');
                    }
                    updateBadge();
                }

                // emails
                if (e.target.closest('.email-mark-read')) {
                    const item = e.target.closest('.email-item');
                    if (!item) return;
                    item.classList.remove('unread');
                    const icon = item.querySelector('.email-mark-read i');
                    if (icon) {
                        icon.classList.remove('ri-checkbox-blank-circle-line');
                        icon.classList.add('ri-checkbox-circle-fill');
                    }
                    updateMailBadge();
                }
            });

            // Clear all notifications
            if (clearBtn) {
                clearBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!dropdown) return;
                    const items = dropdown.querySelectorAll('.notification-item');
                    items.forEach(i => i.remove());
                    updateBadge();
                    // collapse dropdown and update aria
                    if (dropdown.classList.contains('open')) dropdown.classList.remove('open');
                    if (toggle) toggle.setAttribute('aria-expanded', 'false');
                    dropdown.setAttribute('aria-hidden', 'true');
                });
            }

            // Mail clear
            if (mailClear) {
                mailClear.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!mailDropdown) return;
                    const items = mailDropdown.querySelectorAll('.email-item');
                    items.forEach(i => i.remove());
                    updateMailBadge();
                    if (mailDropdown.classList.contains('open')) mailDropdown.classList.remove('open');
                    if (mailToggle) mailToggle.setAttribute('aria-expanded', 'false');
                    mailDropdown.setAttribute('aria-hidden', 'true');
                });
            }

            // Update mail badge
            function updateMailBadge() {
                if (!mailBadge || !mailDropdown) return;
                const unread = mailDropdown.querySelectorAll('.email-item.unread').length;
                mailBadge.textContent = unread > 0 ? unread : '';
                mailBadge.style.display = unread > 0 ? 'inline-flex' : 'none';
            }

            // Settings: persist email notification toggle
            if (emailNotifToggle) {
                try {
                    const saved = localStorage.getItem('nexo_email_notifications');
                    emailNotifToggle.checked = saved === null ? true : saved === '1';
                } catch (e) {
                    emailNotifToggle.checked = true;
                }
                emailNotifToggle.addEventListener('change', function() {
                    try {
                        localStorage.setItem('nexo_email_notifications', this.checked ? '1' : '0');
                    } catch (e) {}
                });
            }

            // initial badge updates
            updateBadge();
            updateMailBadge();
        })();
    </script>

    <!-- Dashboard User Data Handler -->
    <script src="../../assets/js/dashboard.js"></script>

</body>

</html>