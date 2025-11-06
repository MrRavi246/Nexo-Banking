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
                        <!-- Sample rows (frontend-only) -->
                        <tr data-account="checking-001" data-type="deposit" data-date="2025-08-22">
                            <td>2025-08-22</td>
                            <td>Direct deposit - Acme Corp</td>
                            <td>Primary Checking **** 4892</td>
                            <td>Income</td>
                            <td class="positive">+$3,500.00</td>
                            <td>$12,450.75</td>
                            <td><button class="action-small" onclick="viewTransaction(this)"><i class="ri-eye-line"></i></button></td>
                        </tr>
                        <tr data-account="savings-001" data-type="transfer" data-date="2025-08-21">
                            <td>2025-08-21</td>
                            <td>Automatic savings transfer</td>
                            <td>High Yield Savings **** 7321</td>
                            <td>Transfer</td>
                            <td class="positive">+$500.00</td>
                            <td>$28,750.00</td>
                            <td><button class="action-small" onclick="viewTransaction(this)"><i class="ri-eye-line"></i></button></td>
                        </tr>
                        <tr data-account="credit-001" data-type="payment" data-date="2025-08-21">
                            <td>2025-08-21</td>
                            <td>Online purchase - Amazon</td>
                            <td>Nexo Platinum Card **** 9876</td>
                            <td>Shopping</td>
                            <td class="negative">-$89.99</td>
                            <td>$6,381.75</td>
                            <td><button class="action-small" onclick="viewTransaction(this)"><i class="ri-eye-line"></i></button></td>
                        </tr>
                        <tr data-account="checking-001" data-type="withdrawal" data-date="2025-08-19">
                            <td>2025-08-19</td>
                            <td>ATM withdrawal</td>
                            <td>Primary Checking **** 4892</td>
                            <td>Cash</td>
                            <td class="negative">-$200.00</td>
                            <td>$8,950.75</td>
                            <td><button class="action-small" onclick="viewTransaction(this)"><i class="ri-eye-line"></i></button></td>
                        </tr>
                        <tr data-account="credit-001" data-type="payment" data-date="2025-07-15">
                            <td>2025-07-15</td>
                            <td>Credit card payment - Employer</td>
                            <td>Nexo Platinum Card **** 9876</td>
                            <td>Payment</td>
                            <td class="positive">+$1,200.00</td>
                            <td>$5,181.75</td>
                            <td><button class="action-small" onclick="viewTransaction(this)"><i class="ri-eye-line"></i></button></td>
                        </tr>
                        <tr data-account="checking-001" data-type="payment" data-date="2025-06-30">
                            <td>2025-06-30</td>
                            <td>Utility bill payment - PowerCo</td>
                            <td>Primary Checking **** 4892</td>
                            <td>Utilities</td>
                            <td class="negative">-$132.45</td>
                            <td>$9,150.75</td>
                            <td><button class="action-small" onclick="viewTransaction(this)"><i class="ri-eye-line"></i></button></td>
                        </tr>
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