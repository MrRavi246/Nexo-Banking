<?php
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/../../backend/functions.php';

// Require login
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

$conn = getDBConnection();
if (!validateSession($conn, $_SESSION['user_id'], $_SESSION['session_token'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch current user display name and member type for navbar
$userStmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
$userStmt->execute([$_SESSION['user_id']]);
$userRow = $userStmt->fetch(PDO::FETCH_ASSOC);
$displayName = 'User';
$memberType = '';
if ($userRow) {
    $first = trim($userRow['first_name'] ?? '');
    $last = trim($userRow['last_name'] ?? '');
    if ($first || $last) {
        $displayName = trim($first . ' ' . $last);
    } elseif (!empty($userRow['username'])) {
        $displayName = $userRow['username'];
    } elseif (!empty($userRow['email'])) {
        $displayName = $userRow['email'];
    }
    $memberType = $userRow['member_type'] ?? ($userRow['role'] ?? 'Member');
}

$acctStmt = $conn->prepare("SELECT account_id, account_number, account_type, balance, currency, status, last_activity, created_at FROM accounts WHERE user_id = ? ORDER BY account_id");
$acctStmt->execute([$_SESSION['user_id']]);
$accounts = $acctStmt->fetchAll(PDO::FETCH_ASSOC);

// Compute totals
$totalBalance = 0.0;
foreach ($accounts as $a) { $totalBalance += (float)$a['balance']; }
$totalAccounts = count($accounts);

// Recent 30 days income/expenses
$incomeStmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) AS income FROM transactions WHERE account_id IN (" . (count($accounts)?implode(',', array_map(function($v){return (int)$v['account_id'];}, $accounts)):0) . ") AND transaction_type IN ('deposit','refund','interest') AND transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$incomeStmt->execute();
$incomeRow = $incomeStmt->fetch(PDO::FETCH_ASSOC);
$monthlyIncome = (float)($incomeRow['income'] ?? 0);

$expenseStmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) AS expense FROM transactions WHERE account_id IN (" . (count($accounts)?implode(',', array_map(function($v){return (int)$v['account_id'];}, $accounts)):0) . ") AND transaction_type IN ('withdrawal','payment','fee') AND transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$expenseStmt->execute();
$expenseRow = $expenseStmt->fetch(PDO::FETCH_ASSOC);
$monthlyExpenses = abs((float)($expenseRow['expense'] ?? 0));

// Recent account activity (last 4 transactions)
$recentTxStmt = $conn->prepare("SELECT t.*, a.account_number, a.account_type FROM transactions t JOIN accounts a ON t.account_id = a.account_id WHERE t.account_id IN (" . (count($accounts)?implode(',', array_map(function($v){return (int)$v['account_id'];}, $accounts)):0) . ") ORDER BY t.created_at DESC LIMIT 4");
$recentTxStmt->execute();
$recentActivity = $recentTxStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../../assets/style/nav.css">
    <link rel="stylesheet" href="../../assets/style/accounts.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link
        rel="shortcut icon"
        href="../../assets/media/svgs/favicon-white-1.svg"
        type="image/x-icon" />
    <title>NEXO Accounts - Manage Your Banking Accounts</title>
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
                <input type="text" placeholder="Search transactions, accounts...">
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
                <img src="" alt="User Avatar" class="avatar">
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
                <div class="menu-item active">
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

        <!-- Main Accounts Content -->
        <main class="accounts-main">
            <!-- Accounts Header -->
            <div class="accounts-header">
                <div class="header-content">
                    <h1>My Accounts</h1>
                    <p>Manage your banking accounts and view detailed information</p>
                </div>
                <div class="header-actions">
                    <button class="btn-secondary" onclick="exportAccountData()">
            <i class="ri-download-line"></i>
            Export Data
          </button>
                    <button class="btn-primary" onclick="openAddAccountModal()">
            <i class="ri-add-line"></i>
            Add Account
          </button>
                </div>
            </div>

            <!-- Account Overview Cards -->
            <div class="account-overview">
                <div class="overview-card total-balance">
                    <div class="card-icon">
                        <i class="ri-wallet-3-line"></i>
                    </div>
                    <div class="card-content">
                        <h3>Total Balance</h3>
                        <div class="balance-amount"><?php echo '$' . number_format($totalBalance,2); ?></div>
                        <div class="balance-change positive">
                            <i class="ri-arrow-up-line"></i> <!-- dynamic change not implemented -->
                        </div>
                    </div>
                </div>

                <div class="overview-card total-accounts">
                    <div class="card-icon">
                        <i class="ri-bank-line"></i>
                    </div>
                    <div class="card-content">
                        <h3>Total Accounts</h3>
                        <div class="balance-amount"><?php echo $totalAccounts; ?></div>
                        <div class="balance-change neutral">
                            Active accounts
                        </div>
                    </div>
                </div>

                <div class="overview-card monthly-income">
                    <div class="card-icon">
                        <i class="ri-arrow-down-line"></i>
                    </div>
                    <div class="card-content">
                        <h3>Monthly Income</h3>
                        <div class="balance-amount"><?php echo '$' . number_format($monthlyIncome,2); ?></div>
                        <div class="balance-change positive">
                            <i class="ri-arrow-up-line"></i> <!-- dynamic percent not implemented -->
                        </div>
                    </div>
                </div>

                <div class="overview-card monthly-expenses">
                    <div class="card-icon">
                        <i class="ri-arrow-up-line"></i>
                    </div>
                    <div class="card-content">
                        <h3>Monthly Expenses</h3>
                        <div class="balance-amount"><?php echo '$' . number_format($monthlyExpenses,2); ?></div>
                        <div class="balance-change negative">
                            <i class="ri-arrow-up-line"></i> <!-- dynamic percent not implemented -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Filters and Sort -->
            <div class="account-controls">
                <div class="account-filters">
                    <button class="filter-btn active" data-filter="all">All Accounts</button>
                    <button class="filter-btn" data-filter="checking">Checking</button>
                    <button class="filter-btn" data-filter="savings">Savings</button>
                    <button class="filter-btn" data-filter="credit">Credit Cards</button>
                </div>
                <div class="account-sort">
                    <select id="accountSort">
            <option value="balance-high">Balance: High to Low</option>
            <option value="balance-low">Balance: Low to High</option>
            <option value="name">Account Name</option>
            <option value="type">Account Type</option>
            <option value="recent">Recently Active</option>
          </select>
                </div>
            </div>

            <!-- Account Cards Grid -->
            <div class="accounts-grid">
                <?php foreach ($accounts as $a):
                    $acctType = htmlspecialchars($a['account_type'] ?? 'account');
                    $acctName = htmlspecialchars($a['account_name'] ?? ucfirst($acctType) . ' Account');
                    $acctNumber = htmlspecialchars($a['account_number'] ?? '');
                    $masked = $acctNumber ? '**** **** **** ' . substr($acctNumber, -4) : '';
                    $balance = '$' . number_format((float)$a['balance'], 2);
                    $dataType = strtolower($acctType);
                    $accountId = htmlspecialchars($a['account_number'] ?: $a['account_id']);
                ?>
                <div class="account-card <?php echo $dataType; ?>" data-type="<?php echo $dataType; ?>">
                    <div class="account-header">
                        <div class="account-icon">
                            <?php if ($dataType === 'savings'): ?>
                                <i class="ri-piggy-bank-line"></i>
                            <?php elseif ($dataType === 'credit'): ?>
                                <i class="ri-credit-card-line"></i>
                            <?php else: ?>
                                <i class="ri-bank-card-line"></i>
                            <?php endif; ?>
                        </div>
                        <div class="account-menu">
                            <i class="ri-more-line" onclick="toggleAccountMenu(this)"></i>
                            <div class="account-dropdown">
                                <div class="dropdown-item" onclick="viewAccountDetails('<?php echo $accountId; ?>')">
                                    <i class="ri-eye-line"></i> View Details
                                </div>
                                <div class="dropdown-item" onclick="downloadStatement('<?php echo $accountId; ?>')">
                                    <i class="ri-download-line"></i> Download Statement
                                </div>
                                <div class="dropdown-item" onclick="manageAccount('<?php echo $accountId; ?>')">
                                    <i class="ri-settings-line"></i> Manage Account
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="account-content">
                        <div class="account-type"><?php echo $acctType; ?> Account</div>
                        <div class="account-name"><?php echo $acctName; ?></div>
                        <div class="account-number"><?php echo $masked; ?></div>
                        <div class="account-balance"><?php echo $balance; ?></div>
                        <div class="account-status available">Available Balance</div>
                    </div>
                    <div class="account-footer">
                        <div class="account-actions">
                            <button class="action-btn" onclick="initiateTransfer('<?php echo $accountId; ?>')">
                <i class="ri-send-plane-line"></i>
                Transfer
              </button>
                            <button class="action-btn" onclick="viewTransactions('<?php echo $accountId; ?>')">
                <i class="ri-list-check-line"></i>
                Transactions
              </button>
                        </div>
                        <div class="account-info">
                            <span class="last-transaction">Last transaction: --</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Recent Account Activity -->
            <div class="account-activity">
                <div class="activity-header">
                    <h2>Recent Account Activity</h2>
                    <button class="view-all-btn">View All Activity</button>
                </div>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon checking">
                            <i class="ri-bank-card-line"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-description">Direct deposit received</div>
                            <div class="activity-account">Primary Checking **** 4892</div>
                            <div class="activity-time">Today, 9:00 AM</div>
                        </div>
                        <div class="activity-amount positive">+$3,500.00</div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon savings">
                            <i class="ri-piggy-bank-line"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-description">Automatic savings transfer</div>
                            <div class="activity-account">High Yield Savings **** 7321</div>
                            <div class="activity-time">Yesterday, 11:30 PM</div>
                        </div>
                        <div class="activity-amount positive">+$500.00</div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon credit">
                            <i class="ri-credit-card-line"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-description">Online purchase - Amazon</div>
                            <div class="activity-account">Nexo Platinum Card **** 9876</div>
                            <div class="activity-time">Yesterday, 3:45 PM</div>
                        </div>
                        <div class="activity-amount negative">-$89.99</div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon checking">
                            <i class="ri-bank-card-line"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-description">ATM withdrawal</div>
                            <div class="activity-account">Primary Checking **** 4892</div>
                            <div class="activity-time">3 days ago, 2:15 PM</div>
                        </div>
                        <div class="activity-amount negative">-$200.00</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Account Modal -->
    <div id="addAccountModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Account</h2>
                <span class="close" onclick="closeAddAccountModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="account-type-selection">
                    <div class="account-type-option" data-type="checking">
                        <div class="type-icon">
                            <i class="ri-bank-card-line"></i>
                        </div>
                        <div class="type-info">
                            <h3>Checking Account</h3>
                            <p>For daily transactions and bill payments</p>
                        </div>
                    </div>
                    <div class="account-type-option" data-type="savings">
                        <div class="type-icon">
                            <!-- <i class="ri-piggy-bank-line"></i> -->

                        </div>
                        <div class="type-info">
                            <h3>Savings Account</h3>
                            <p>Earn interest on your deposits</p>
                        </div>
                    </div>
                    <div class="account-type-option" data-type="credit">
                        <div class="type-icon">
                            <i class="ri-credit-card-line"></i>
                        </div>
                        <div class="type-info">
                            <h3>Credit Card</h3>
                            <p>Build credit and earn rewards</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAddAccountModal()">Cancel</button>
                <button type="button" class="btn-primary" onclick="proceedWithAccountType()">Continue</button>
            </div>
        </div>
    </div>

    <script>
        // Account page functionality
        document.addEventListener('DOMContentLoaded', function() {
            initializeAccountFilters();
            initializeAccountSort();
            initializeMenuInteractions();
        });

        function initializeAccountFilters() {
            const filterBtns = document.querySelectorAll('.filter-btn');
            const accountCards = document.querySelectorAll('.account-card');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const filter = this.getAttribute('data-filter');

                    accountCards.forEach(card => {
                        if (filter === 'all' || card.getAttribute('data-type') === filter) {
                            card.style.display = 'block';
                            card.style.animation = 'fadeIn 0.3s ease-in-out';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
        }

        function initializeAccountSort() {
            const sortSelect = document.getElementById('accountSort');

            sortSelect.addEventListener('change', function() {
                const sortType = this.value;
                const accountsGrid = document.querySelector('.accounts-grid');
                const cards = Array.from(accountsGrid.children);

                cards.sort((a, b) => {
                    switch (sortType) {
                        case 'balance-high':
                            return parseFloat(b.querySelector('.account-balance').textContent.replace(/[$,]/g, '')) -
                                parseFloat(a.querySelector('.account-balance').textContent.replace(/[$,]/g, ''));
                        case 'balance-low':
                            return parseFloat(a.querySelector('.account-balance').textContent.replace(/[$,]/g, '')) -
                                parseFloat(b.querySelector('.account-balance').textContent.replace(/[$,]/g, ''));
                        case 'name':
                            return a.querySelector('.account-name').textContent.localeCompare(b.querySelector('.account-name').textContent);
                        case 'type':
                            return a.querySelector('.account-type').textContent.localeCompare(b.querySelector('.account-type').textContent);
                        default:
                            return 0;
                    }
                });

                cards.forEach(card => accountsGrid.appendChild(card));
            });
        }

        function initializeMenuInteractions() {
            const menuItems = document.querySelectorAll('.sidebar .menu-item');

            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (this.querySelector('span').textContent === 'Dashboard') {
                        window.location.href = 'Dashboard.php';
                    }
                });
            });
        }

        function toggleAccountMenu(element) {
            const dropdown = element.nextElementSibling;
            const allDropdowns = document.querySelectorAll('.account-dropdown');

            allDropdowns.forEach(d => {
                if (d !== dropdown) d.style.display = 'none';
            });

            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        function viewAccountDetails(accountId) {
            showNotification(`Viewing details for account ${accountId}`, 'info');
            // Implementation would navigate to detailed account view
        }

        function downloadStatement(accountId) {
            showNotification(`Downloading statement for account ${accountId}`, 'success');
            // Implementation would generate and download PDF statement
        }

        function manageAccount(accountId) {
            showNotification(`Opening account management for ${accountId}`, 'info');
            // Implementation would open account settings modal
        }

        function initiateTransfer(accountId) {
            showNotification(`Initiating transfer from account ${accountId}`, 'info');
            // Implementation would open transfer modal with pre-selected account
        }

        function viewTransactions(accountId) {
            showNotification(`Loading transactions for account ${accountId}`, 'info');
            // Implementation would navigate to transaction history page
        }

        function payCredit(accountId) {
            showNotification(`Opening payment form for account ${accountId}`, 'info');
            // Implementation would open credit card payment modal
        }

        function exportAccountData() {
            showNotification('Exporting account data...', 'info');
            // Implementation would generate and download account data
        }

        function openAddAccountModal() {
            document.getElementById('addAccountModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeAddAccountModal() {
            document.getElementById('addAccountModal').style.display = 'none';
            document.body.style.overflow = 'auto';

            // Reset selections
            const options = document.querySelectorAll('.account-type-option');
            options.forEach(option => option.classList.remove('selected'));
        }

        function proceedWithAccountType() {
            const selected = document.querySelector('.account-type-option.selected');
            if (!selected) {
                showNotification('Please select an account type', 'error');
                return;
            }

            const accountType = selected.getAttribute('data-type');
            showNotification(`Starting application for ${accountType} account`, 'success');
            closeAddAccountModal();
            // Implementation would navigate to account application form
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;

            const icon = type === 'success' ? 'ri-check-circle-line' :
                type === 'error' ? 'ri-error-warning-line' : 'ri-information-line';

            notification.innerHTML = `
        <i class="${icon}"></i>
        <span>${message}</span>
      `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.classList.add('show');
            }, 100);

            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // Initialize account type selection in modal
        document.addEventListener('DOMContentLoaded', function() {
            const accountTypeOptions = document.querySelectorAll('.account-type-option');

            accountTypeOptions.forEach(option => {
                option.addEventListener('click', function() {
                    accountTypeOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                });
            });

            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('addAccountModal');
                if (event.target === modal) {
                    closeAddAccountModal();
                }
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.account-menu')) {
                    const dropdowns = document.querySelectorAll('.account-dropdown');
                    dropdowns.forEach(dropdown => {
                        dropdown.style.display = 'none';
                    });
                }
            });
        });
    </script>

</body>

</html>