<?php
// Start session and check authentication
require_once '../../backend/config.php';
require_once '../../backend/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Validate session
$conn = getDBConnection();
if (!validateSession($conn, $_SESSION['user_id'], $_SESSION['session_token'])) {
    session_destroy();
    header('Location: ../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get user's accounts
$stmt = $conn->prepare("
    SELECT account_id, account_type, account_number, balance, currency
    FROM accounts 
    WHERE user_id = ? AND status = 'active'
    ORDER BY account_type
");
$stmt->execute([$userId]);
$userAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user info
$stmt = $conn->prepare("SELECT first_name, last_name, member_type FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
$userName = $userInfo['first_name'] . ' ' . $userInfo['last_name'];
$memberType = ucfirst($userInfo['member_type']) . ' Member';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../../assets/style/nav.css">
    <link rel="stylesheet" href="../../assets/style/accounts.css">
    <link rel="stylesheet" href="../../assets/style/pay-bills.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link
        rel="shortcut icon"
        href="../../assets/media/svgs/favicon-white-1.svg"
        type="image/x-icon" />
    <title>NEXO Pay Bills</title>
</head>

<body>
    <nav class="dashboard-nav">
        <div class="nav-left">
            <div class="logo">
                <a href="../../index.php"><span>N</span>exo</a>
            </div>
        </div>
        <div class="nav-center">
            <div class="search-bar">
                <i class="ri-search-line"></i>
                <input id="globalSearch" type="text" placeholder="Search bills, payees...">
            </div>
        </div>
        <div class="nav-right">
            <div class="nav-icons">
                <div class="nav-icon">
                    <i class="ri-notification-3-line"></i>
                    <span class="notification-badge">3</span>
                </div>
                <div class="nav-icon">
                    <i class="ri-mail-line"></i>
                    <span class="notification-badge">7</span>
                </div>
            </div>
            <div class="user-profile">
                <img src="https://i.pravatar.cc/" alt="User Avatar" class="avatar">
                <div class="user-info">
                    <span class="username"><?php echo htmlspecialchars($userName); ?></span>
                    <span class="user-type"><?php echo htmlspecialchars($memberType); ?></span>
                </div>
                <i class="ri-arrow-down-s-line"></i>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
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
                <div class="menu-item active" onclick="window.location.href='pay-bills.php'">
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


        <main class="main-content">
            <div class="content-wrapper">
                <div class="page-hero">
                    <div class="hero-left">
                        <h1>Pay Bills</h1>
                        <p>Manage and pay your recurring and one-time bills</p>
                    </div>
                    <div class="hero-actions">
                        <button class="btn export" onclick="/* noop */">
                            <i class="ri-download-line"></i>
                            Export Data
                        </button>
                        <button class="btn primary" onclick="document.getElementById('payFormAmount').focus()">
                            <i class="ri-add-line"></i>
                            Add Payment
                        </button>
                    </div>
                </div>

                <!-- Summary stats -->
                <div class="hero-stats">
                    <div class="stat-card">
                        <div class="label">Total Due</div>
                        <div class="value large danger" id="totalDueAmount">$0.00</div>
                        <div class="muted" id="totalDueText">Loading...</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Next Payment</div>
                        <div class="value" id="nextPaymentInfo">Loading...</div>
                        <div class="muted" id="nextPaymentDate">--</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Recent Payments</div>
                        <div class="value success" id="recentPaymentsCount">--</div>
                        <div class="muted">Last 30 days</div>
                    </div>
                </div>

                <div class="pay-main-container">
                    <section class="bills-list">
                        <div class="section-header">
                            <h3>Upcoming Bills</h3>
                            <button class="view-all-btn" onclick="showAllBills()">View All</button>
                        </div>

                        <div id="upcomingBillsList">
                            <!-- Bills will be loaded dynamically -->
                            <div style="padding: 2rem; text-align: center; color: #888;">
                                <i class="ri-loader-4-line" style="font-size: 2rem; animation: spin 1s linear infinite;"></i>
                                <p>Loading bills...</p>
                            </div>
                        </div>
                    </section>

                    <aside class="pay-panel">
                        <div class="section-header">
                            <h3>Pay a Bill</h3>
                        </div>

                        <form id="payForm" class="pay-form">
                            <label for="fromAccount">From Account</label>
                            <select id="fromAccount" required>
                                <option value="">Select account</option>
                                <?php foreach ($userAccounts as $account): ?>
                                    <option value="<?php echo htmlspecialchars($account['account_id']); ?>"
                                        data-balance="<?php echo $account['balance']; ?>">
                                        <?php echo ucfirst($account['account_type']); ?> Account
                                        (**** <?php echo substr($account['account_number'], -4); ?>) -
                                        $<?php echo number_format($account['balance'], 2); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label for="payeeSelect">Payee</label>
                            <select id="payeeSelect" onchange="handlePayeeChange()">
                                <option value="">Select payee</option>
                                <option value="custom">+ Add Custom Payee</option>
                            </select>

                            <!-- Custom payee input (hidden by default) -->
                            <div id="customPayeeGroup" class="form-group" style="display: none;">
                                <label for="customPayee">Custom Payee Name</label>
                                <input id="customPayee" type="text" placeholder="Enter payee name">

                                <label for="billType">Bill Type</label>
                                <select id="billType">
                                    <option value="utilities">Utilities</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="loan">Loan</option>
                                    <option value="subscription">Subscription</option>
                                    <option value="mobile_recharge">Mobile Recharge</option>
                                    <option value="insurance">Insurance</option>
                                </select>
                            </div>

                            <label for="payFormAmount">Amount</label>
                            <div class="amount-input">
                                <span class="currency">$</span>
                                <input id="payFormAmount" type="number" step="0.01" min="0.01" placeholder="0.00" required>
                            </div>

                            <label for="payDate">Date</label>
                            <input id="payDate" type="date" required>

                            <!-- Payment frequency/duration options -->
                            <div class="payment-frequency">
                                <label for="paymentType">Payment Type</label>
                                <select id="paymentType" onchange="handlePaymentTypeChange()">
                                    <option value="one-time">One-time Payment</option>
                                    <option value="recurring">Recurring Payment</option>
                                </select>
                            </div>

                            <!-- Recurring options (hidden by default) -->
                            <div id="recurringOptions" class="form-group" style="display: none;">
                                <label for="frequency">Frequency</label>
                                <select id="frequency">
                                    <option value="daily">Every Day</option>
                                    <option value="weekly">Every 7 Days</option>
                                    <option value="bi-weekly">Every 14 Days</option>
                                    <option value="monthly">Every Month</option>
                                    <option value="quarterly">Every 3 Months</option>
                                    <option value="yearly">Every Year</option>
                                </select>

                                <label for="endDate">End Date (optional)</label>
                                <input id="endDate" type="date">

                                <div class="recurring-summary" id="recurringSummary" style="margin-top: 0.5rem; color: #bdbdbd; font-size: 0.85rem;"></div>
                            </div>

                            <label for="payMemo">Memo (optional)</label>
                            <input id="payMemo" type="text" placeholder="Invoice #, account etc">

                            <div class="form-actions">
                                <button type="button" class="btn secondary" onclick="resetPayForm()">Reset</button>
                                <button type="submit" class="btn primary">Pay Now</button>
                            </div>
                        </form>

                        <div class="recent-payments">
                            <div class="section-header">
                                <h4>Recent Payments</h4>
                            </div>
                            <div id="recentPaymentsList" class="payments-list">
                                <!-- sample recent payments (static demo) -->
                                <div class="payment-item">
                                    <div class="payment-left">Electric Co.
                                        <div class="muted">Paid 2 days ago</div>
                                    </div>
                                    <div class="payment-right">$120.50</div>
                                </div>
                                <div class="payment-item">
                                    <div class="payment-left">Internet Provider
                                        <div class="muted">Paid 7 days ago</div>
                                    </div>
                                    <div class="payment-right">$45.00</div>
                                </div>
                                <div class="payment-item">
                                    <div class="payment-left">Water Works
                                        <div class="muted">Paid 1 month ago</div>
                                    </div>
                                    <div class="payment-right">$32.20</div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
            <!-- /.content-wrapper -->
        </main>
    </div>

    <script src="../../assets/js/pay-bills.js"></script>
</body>

</html>