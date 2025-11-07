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

// Fetch display name and member type for navbar
$userInfoStmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
$userInfoStmt->execute([$userId]);
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

// Get user's accounts for the dropdown
$stmt = $conn->prepare("
    SELECT account_id, account_type, account_number, balance, currency
    FROM accounts 
    WHERE user_id = ? AND status = 'active'
    ORDER BY account_type
");
$stmt->execute([$userId]);
$userAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get saved contacts/beneficiaries (if table exists)
$savedContacts = [];
try {
    $stmt = $conn->prepare("
        SELECT beneficiary_id, beneficiary_name, account_number, bank_name, email
        FROM beneficiaries 
        WHERE user_id = ? AND status = 'active'
        ORDER BY beneficiary_name
    ");
    $stmt->execute([$userId]);
    $savedContacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Beneficiaries table might not exist
    error_log("Beneficiaries query failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/style/nav.css">
    <link rel="stylesheet" href="../../assets/style/accounts.css">
    <link rel="stylesheet" href="../../assets/style/transfer-money.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Teko:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap"
        rel="stylesheet">
    <link
        rel="shortcut icon"
        href="../../assets/media/svgs/favicon-white-1.svg"
        type="image/x-icon" />
    <title>NEXO Transfer Money - Send & Receive Funds</title>
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
                <input id="globalSearch" type="text" placeholder="Search contacts, accounts...">
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
                <div class="nav-icon">
                    <i class="ri-settings-3-line"></i>
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

    <!-- Dashboard Sidebar -->
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
                <div class="menu-item active" onclick="window.location.href='transfer-money.php'">
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


        <!-- Main Transfer Money Content -->
        <main class="main-content">
            <!-- Page Hero (title + actions) -->
            <div class="page-hero">
                <div class="hero-left">
                    <h1>Transfer Money</h1>
                    <p>Send money quickly and securely to anyone, anywhere</p>
                </div>
                <div class="hero-actions">
                    <button class="btn export" onclick="/* export placeholder */">
                        <i class="ri-download-line"></i>
                        Export Data
                    </button>
                    <button class="btn primary" onclick="showTransferForm()">
                        <i class="ri-send-plane-line"></i>
                        New Transfer
                    </button>
                </div>
            </div>

            <!-- Transfer Options -->
            <div class="transfer-options">
                <div class="transfer-method active" data-method="internal">
                    <div class="method-icon">
                        <i class="ri-bank-line"></i>
                    </div>
                    <div class="method-info">
                        <h3>Nexo to Nexo</h3>
                        <p>Instant transfer between Nexo accounts</p>
                        <span class="method-tag">Free</span>
                    </div>
                </div>
                <div class="transfer-method" data-method="external">
                    <div class="method-icon">
                        <i class="ri-building-line"></i>
                    </div>
                    <div class="method-info">
                        <h3>External Transfer</h3>
                        <p>Transfer to other banks via ACH</p>
                        <span class="method-tag">$2.99</span>
                    </div>
                </div>
                <div class="transfer-method" data-method="wire">
                    <div class="method-icon">
                        <i class="ri-global-line"></i>
                    </div>
                    <div class="method-info">
                        <h3>Wire Transfer</h3>
                        <p>International transfers worldwide</p>
                        <span class="method-tag">$15.00</span>
                    </div>
                </div>
            </div>

            <!-- Transfer Form and Recent Transfers Container -->
            <div class="transfer-main-container">
                <!-- Transfer Form -->
                <div class="transfer-form-container">
                    <form class="transfer-form" id="transferForm">
                        <div class="form-section">
                            <h3>From Account</h3>
                            <div class="account-selector">
                                <select id="fromAccount" required>
                                    <option value="">Select source account</option>
                                    <?php foreach ($userAccounts as $account): ?>
                                        <option value="<?php echo htmlspecialchars($account['account_id']); ?>"
                                            data-balance="<?php echo $account['balance']; ?>">
                                            <?php echo ucfirst($account['account_type']); ?> Account
                                            (**** <?php echo substr($account['account_number'], -4); ?>) -
                                            $<?php echo number_format($account['balance'], 2); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="ri-arrow-down-s-line"></i>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Recipient Details</h3>
                            <div class="recipient-type-toggle">
                                <button type="button" class="toggle-btn active" data-type="contact">
                                    <i class="ri-user-line"></i>
                                    Saved Contact
                                </button>
                                <button type="button" class="toggle-btn" data-type="new">
                                    <i class="ri-add-line"></i>
                                    New Recipient
                                </button>
                            </div>

                            <div class="recipient-form contact-form">
                                <div class="contact-search">
                                    <i class="ri-search-line"></i>
                                    <input type="text" id="contactSearch" placeholder="Search saved contacts...">
                                </div>
                                <div class="saved-contacts">
                                    <div class="contact-item" data-contact-id="1">
                                        <div class="contact-avatar">
                                            <img src="https://i.pravatar.cc/50?img=1" alt="Sarah Wilson">
                                        </div>
                                        <div class="contact-info">
                                            <h4>Sarah Wilson</h4>
                                            <p>sarah.wilson@email.com</p>
                                            <span class="contact-bank">Chase Bank (**** 5678)</span>
                                        </div>
                                        <i class="ri-check-line contact-selected"></i>
                                    </div>
                                    <div class="contact-item" data-contact-id="2">
                                        <div class="contact-avatar">
                                            <img src="https://i.pravatar.cc/50?img=2" alt="Mike Johnson">
                                        </div>
                                        <div class="contact-info">
                                            <h4>Mike Johnson</h4>
                                            <p>mike.johnson@email.com</p>
                                            <span class="contact-bank">Bank of America (**** 9012)</span>
                                        </div>
                                        <i class="ri-check-line contact-selected"></i>
                                    </div>
                                    <div class="contact-item" data-contact-id="3">
                                        <div class="contact-avatar">
                                            <img src="https://i.pravatar.cc/50?img=3" alt="Emma Davis">
                                        </div>
                                        <div class="contact-info">
                                            <h4>Emma Davis</h4>
                                            <p>emma.davis@email.com</p>
                                            <span class="contact-bank">Nexo Bank (**** 3456)</span>
                                        </div>
                                        <i class="ri-check-line contact-selected"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="recipient-form new-form" style="display: none;">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="recipientName">Full Name</label>
                                        <input type="text" id="recipientName" placeholder="Enter recipient's full name">
                                    </div>
                                    <div class="form-group">
                                        <label for="recipientEmail">Email Address</label>
                                        <input type="email" id="recipientEmail" placeholder="Enter email address">
                                    </div>
                                    <div class="form-group">
                                        <label for="bankName">Bank Name</label>
                                        <input type="text" id="bankName" placeholder="Enter bank name">
                                    </div>
                                    <div class="form-group">
                                        <label for="accountNumber">Account Number</label>
                                        <input type="text" id="accountNumber" placeholder="Enter account number">
                                    </div>
                                    <div class="form-group">
                                        <label for="routingNumber">Routing Number</label>
                                        <input type="text" id="routingNumber" placeholder="Enter routing number">
                                    </div>
                                    <div class="form-group">
                                        <label for="accountType">Account Type</label>
                                        <select id="accountType">
                                            <option value="">Select account type</option>
                                            <option value="checking">Checking</option>
                                            <option value="savings">Savings</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Transfer Details</h3>
                            <div class="form-grid">
                                <div class="form-group amount-group">
                                    <label for="transferAmount">Amount</label>
                                    <div class="amount-input">
                                        <span class="currency">$</span>
                                        <input type="number" id="transferAmount" placeholder="0.00" step="0.01"
                                            min="0.01" required>
                                    </div>
                                    <div class="amount-suggestions">
                                        <button type="button" class="amount-btn" data-amount="100">$100</button>
                                        <button type="button" class="amount-btn" data-amount="500">$500</button>
                                        <button type="button" class="amount-btn" data-amount="1000">$1,000</button>
                                        <button type="button" class="amount-btn" data-amount="2500">$2,500</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="transferDate">Transfer Date</label>
                                    <input type="date" id="transferDate" required>
                                </div>
                                <div class="form-group full-width">
                                    <label for="transferMemo">Memo (Optional)</label>
                                    <input type="text" id="transferMemo" placeholder="What's this transfer for?">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Security Verification</h3>
                            <div class="security-options">
                                <div class="security-method active" data-method="sms">
                                    <i class="ri-smartphone-line"></i>
                                    <span>SMS Verification</span>
                                </div>
                                <div class="security-method" data-method="email">
                                    <i class="ri-mail-line"></i>
                                    <span>Email Verification</span>
                                </div>
                                <div class="security-method" data-method="app">
                                    <i class="ri-shield-check-line"></i>
                                    <span>Authenticator App</span>
                                </div>
                            </div>
                        </div>

                        <div class="transfer-summary">
                            <div class="summary-row">
                                <span>Transfer Amount:</span>
                                <span id="summaryAmount">$0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Transfer Fee:</span>
                                <span id="summaryFee">$0.00</span>
                            </div>
                            <div class="summary-row total">
                                <span>Total Amount:</span>
                                <span id="summaryTotal">$0.00</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn secondary" onclick="resetForm()">
                                <i class="ri-refresh-line"></i>
                                Reset
                            </button>
                            <button type="submit" class="btn primary">
                                <i class="ri-send-plane-line"></i>
                                Send Money
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Recent Transfers -->
                <div class="recent-transfers">
                    <div class="section-header">
                        <h3>Recent Transfers</h3>
                        <button class="view-all-btn">View All</button>
                    </div>
                    <div class="transfers-list" id="recentTransfersList">
                        <!-- Transfers will be loaded dynamically via JavaScript -->
                        <div class="transfer-item" style="justify-content: center; color: #888;">
                            <p>Loading recent transfers...</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/transfer-money.js"></script>
</body>

</html>