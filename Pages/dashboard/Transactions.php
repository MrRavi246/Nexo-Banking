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

// Fetch user's accounts (for balances and account info)
$acctStmt = $conn->prepare("SELECT account_id, account_number, account_type, balance FROM accounts WHERE user_id = ?");
$acctStmt->execute([$_SESSION['user_id']]);
$accounts = [];
foreach ($acctStmt->fetchAll(PDO::FETCH_ASSOC) as $a) {
    $accounts[$a['account_id']] = $a;
}

// Fetch recent transactions for user's accounts
$placeholders = implode(',', array_fill(0, count($accounts) > 0 ? count($accounts) : 1, '?'));
if (count($accounts) > 0) {
    $accountIds = array_keys($accounts);
    $inClause = implode(',', array_fill(0, count($accountIds), '?'));
    $txSql = "SELECT t.*, a.account_number, a.account_type FROM transactions t JOIN accounts a ON t.account_id = a.account_id WHERE t.account_id IN ($inClause) ORDER BY t.created_at DESC LIMIT 200";
    $txStmt = $conn->prepare($txSql);
    $txStmt->execute($accountIds);
    $transactions = $txStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $transactions = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../../assets/style/nav.css">
    <link rel="stylesheet" href="../../assets/style/accounts.css">
    <link rel="stylesheet" href="../../assets/style/transactions.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link
        rel="shortcut icon"
        href="../../assets/media/svgs/favicon-white-1.svg"
        type="image/x-icon" />
    <title>NEXO Transactions - Transaction History</title>
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
                <input id="globalSearch" type="text" placeholder="Search transactions, accounts...">
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
                <div class="menu-item active" onclick="window.location.href='Transactions.php'">
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


        <!-- Main Transactions Content -->
        <main class="accounts-main">
            <div class="accounts-header">
                <div class="header-content">
                    <h1>Transactions</h1>
                    <p>View and manage your transaction history</p>
                </div>
                <div class="header-actions">
                    <button class="btn-secondary" id="exportBtn">
            <i class="ri-download-line"></i>
            Export CSV
          </button>
                    <button class="btn-primary" onclick="window.location.href='../../pages/dashboard/Dashboard.php'">
            <i class="ri-add-line"></i>
            New Transfer
          </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="account-controls" style="align-items:flex-start; gap:12px;">
                <div class="filters-row">
                    <label class="filter-label">Account</label>
                    <select id="filterAccount">
            <option value="all">All Accounts</option>
            <option value="checking-001">Primary Checking **** 4892</option>
            <option value="savings-001">High Yield Savings **** 7321</option>
            <option value="credit-001">Nexo Platinum Card **** 9876</option>
          </select>
                </div>

                <div class="filters-row">
                    <label class="filter-label">Type</label>
                    <select id="filterType">
            <option value="all">All Types</option>
            <option value="deposit">Deposit</option>
            <option value="withdrawal">Withdrawal</option>
            <option value="payment">Payment</option>
            <option value="transfer">Transfer</option>
          </select>
                </div>

                <div class="filters-row">
                    <label class="filter-label">Date From</label>
                    <input type="date" id="dateFrom">
                </div>

                <div class="filters-row">
                    <label class="filter-label">Date To</label>
                    <input type="date" id="dateTo">
                </div>

                <div class="filters-row buttons-row" style="display:flex; align-items:center; gap:8px;">
                    <button class="btn-secondary" id="applyFilters">Apply</button>
                    <button class="btn-cancel" id="clearFilters">Clear</button>
                </div>

            </div>

            <!-- Transactions Table -->
            <div class="transactions-table">
                <table id="transactionsTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Account</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr><td colspan="7" style="text-align:center;color:#888;padding:1rem;">No transactions found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $t):
                                $acctId = $t['account_id'];
                                $acct = $accounts[$acctId] ?? null;
                                $acctLabel = $acct ? ($acct['account_type'] . ' **** ' . substr($acct['account_number'], -4)) : ('Account ' . $acctId);
                                $type = htmlspecialchars($t['transaction_type'] ?? ($t['category'] ?? ''));
                                $desc = htmlspecialchars($t['description'] ?? $t['recipient_name'] ?? '');
                                $date = htmlspecialchars(substr($t['created_at'], 0, 10));
                                $amountVal = (float)$t['amount'];
                                $isPositive = in_array($t['transaction_type'], ['deposit','refund','interest']) || $amountVal > 0 && $t['transaction_type'] === 'transfer';
                                $sign = $isPositive ? '+' : '-';
                                $amountText = $sign . '$' . number_format(abs($amountVal), 2);
                                $balanceText = isset($acct['balance']) ? '$' . number_format((float)$acct['balance'], 2) : '--';
                            ?>
                            <tr data-account="acct-<?php echo $acctId; ?>" data-type="<?php echo htmlspecialchars($t['transaction_type']); ?>" data-date="<?php echo $date; ?>">
                                <td><?php echo $date; ?></td>
                                <td><?php echo $desc; ?></td>
                                <td><?php echo htmlspecialchars($acctLabel); ?></td>
                                <td><?php echo htmlspecialchars($t['category'] ?? ''); ?></td>
                                <td class="<?php echo $isPositive ? 'positive' : 'negative'; ?>"><?php echo $amountText; ?></td>
                                <td><?php echo $balanceText; ?></td>
                                <td><button class="action-small" onclick="viewTransaction(this)"><i class="ri-eye-line"></i></button></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (simple frontend placeholder) -->
            <div class="pagination">
                <button class="page-btn">&lt; Prev</button>
                <span>Page 1 of 1</span>
                <button class="page-btn">Next &gt;</button>
            </div>

        </main>
    </div>

    <script>
        // Transactions page frontend-only behavior
        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebarNavigation();
            initializeSearch();
            document.getElementById('applyFilters').addEventListener('click', applyFilters);
            document.getElementById('clearFilters').addEventListener('click', clearFilters);
            document.getElementById('exportBtn').addEventListener('click', exportCSV);
        });

        function initializeSidebarNavigation() {
            const menuItems = document.querySelectorAll('.sidebar .menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    const text = this.querySelector('span').textContent.trim();
                    if (text === 'Dashboard') window.location.href = 'Dashboard.php';
                    if (text === 'Accounts') window.location.href = 'accounts.php';
                    if (text === 'Transfer Money') window.location.href = 'transfer-money.php';
                });
            });
        }

        function initializeSearch() {
            const globalSearch = document.getElementById('globalSearch');
            const table = document.getElementById('transactionsTable');
            globalSearch.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                filterTableRows(table, row => {
                    return Array.from(row.cells).some(cell => cell.textContent.toLowerCase().includes(q));
                });
            });
        }

        function filterTableRows(table, predicate) {
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.style.display = predicate(row) ? '' : 'none';
            });
        }

        function applyFilters() {
            const account = document.getElementById('filterAccount').value;
            const type = document.getElementById('filterType').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const table = document.getElementById('transactionsTable');
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const rowAccount = row.getAttribute('data-account');
                const rowType = row.getAttribute('data-type');
                const rowDate = row.getAttribute('data-date');
                let visible = true;

                if (account !== 'all' && rowAccount !== account) visible = false;
                if (type !== 'all' && rowType !== type) visible = false;
                if (dateFrom && rowDate < dateFrom) visible = false;
                if (dateTo && rowDate > dateTo) visible = false;

                row.style.display = visible ? '' : 'none';
            });
        }

        function clearFilters() {
            document.getElementById('filterAccount').value = 'all';
            document.getElementById('filterType').value = 'all';
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';
            document.getElementById('globalSearch').value = '';
            const rows = document.querySelectorAll('#transactionsTable tbody tr');
            rows.forEach(r => r.style.display = '');
        }

        function viewTransaction(btn) {
            const row = btn.closest('tr');
            const desc = row.cells[1].textContent;
            const date = row.cells[0].textContent;
            const amount = row.cells[4].textContent;
            showNotification(`Transaction: ${desc} on ${date} (${amount})`, 'info');
        }

        function exportCSV() {
            const rows = Array.from(document.querySelectorAll('#transactionsTable tbody tr'))
                .filter(r => r.style.display !== 'none');
            if (rows.length === 0) {
                showNotification('No transactions to export', 'error');
                return;
            }

            const csv = [];
            csv.push(['Date', 'Description', 'Account', 'Category', 'Amount', 'Balance']);
            rows.forEach(r => {
                const cols = Array.from(r.cells).slice(0, 6).map(c => '"' + c.textContent.replace(/"/g, '""') + '"');
                csv.push(cols);
            });

            const csvContent = csv.map(c => c.join(',')).join('\n');
            const blob = new Blob([csvContent], {
                type: 'text/csv;charset=utf-8;'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'transactions_export.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showNotification('Export started', 'success');
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            const icon = type === 'success' ? 'ri-check-circle-line' : type === 'error' ? 'ri-error-warning-line' : 'ri-information-line';
            notification.innerHTML = `<i class="${icon}"></i><span>${message}</span>`;
            document.body.appendChild(notification);
            setTimeout(() => notification.classList.add('show'), 100);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (document.body.contains(notification)) document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
    </script>

</body>

</html>