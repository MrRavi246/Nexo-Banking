<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../../assets/style/nav.css">
    <link rel="stylesheet" href="../../assets/style/accounts.css">
    <link rel="stylesheet" href="../../assets/style/support.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link
        rel="shortcut icon"
        href="../../assets/media/svgs/favicon-white-1.svg"
        type="image/x-icon" />
    <title>NEXO Support</title>
</head>

<body>
    <?php
    // show logged-in user in navbar when available
    require_once __DIR__ . '/../../backend/config.php';
    require_once __DIR__ . '/../../backend/functions.php';
    $displayName = 'User';
    $memberType = '';
    if (isLoggedIn()) {
        $conn = getDBConnection();
        if (validateSession($conn, $_SESSION['user_id'], $_SESSION['session_token'])) {
            $uStmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
            $uStmt->execute([$_SESSION['user_id']]);
            $u = $uStmt->fetch(PDO::FETCH_ASSOC);
            if ($u) {
                $first = trim($u['first_name'] ?? '');
                $last = trim($u['last_name'] ?? '');
                if ($first || $last) {
                    $displayName = trim($first . ' ' . $last);
                } elseif (!empty($u['username'])) {
                    $displayName = $u['username'];
                } elseif (!empty($u['email'])) {
                    $displayName = $u['email'];
                }
                $memberType = $u['member_type'] ?? ($u['role'] ?? 'Member');
            }
        }
    }

    ?>
    <nav class="dashboard-nav">
        <div class="nav-left">
            <div class="logo">
                <a href="../../index.php"><span>N</span>exo</a>
            </div>
        </div>
        <div class="nav-center">
            <div class="search-bar">
                <i class="ri-search-line"></i>
                <input type="text" placeholder="Search help articles, tickets...">
            </div>
        </div>
        <div class="nav-right">
            <div class="nav-icons">
                <div class="nav-icon">
                    <i class="ri-notification-3-line"></i>
                    <span class="notification-badge">2</span>
                </div>
                <div class="nav-icon">
                    <i class="ri-mail-line"></i>
                    <span class="notification-badge">1</span>
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

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-menu">
                <div class="menu-item" onclick="window.location.href='Dashboard.php'">
                    <i class="ri-dashboard-3-line"></i>
                    <span>Dashboard</span>
                </div>
                <div class="menu-item" onclick="window.location.href='accounts.php'">
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
                <div class="menu-item active">
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

        <main class="accounts-main">
            <div class="content-wrapper">
                <div class="page-hero">
                    <div class="hero-left">
                        <h1>Support</h1>
                        <p>Help center for account issues, payments and product questions</p>
                    </div>
                    <div class="hero-actions">
                        <button class="btn-secondary" onclick="window.print()">
                            <i class="ri-download-line"></i>
                            Print
                        </button>
                        <button class="btn-primary" onclick="document.getElementById('supportMessage').focus()">
                            <i class="ri-add-line"></i>
                            New Ticket
                        </button>
                    </div>
                </div>

                <div class="hero-stats">
                    <div class="stat-card">
                        <div class="label">Open Tickets</div>
                        <div class="value large danger">3</div>
                        <div class="muted">Assigned to Support</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Avg Response</div>
                        <div class="value">2 hr 15 min</div>
                        <div class="muted">Working hours</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Help Articles</div>
                        <div class="value success">24</div>
                        <div class="muted">Search or browse below</div>
                    </div>
                </div>

                <div class="pay-main-container">
                    <section class="bills-list">
                        <div class="section-header">
                            <h3>Help Articles</h3>
                            <button class="view-all-btn" onclick="showAllArticles()">View All</button>
                        </div>

                        <div class="bill-item">
                            <div class="bill-left">
                                <h4>How to add a bank account</h4>
                                <p>Step-by-step guide for linking external bank accounts</p>
                            </div>
                            <div class="bill-right">
                                <div class="bill-action"><button class="btn-secondary" onclick="openArticle('add-bank')">Open</button></div>
                            </div>
                        </div>

                        <div class="bill-item">
                            <div class="bill-left">
                                <h4>Resolving failed payments</h4>
                                <p>What to check when a transfer or bill fails</p>
                            </div>
                            <div class="bill-right">
                                <div class="bill-action"><button class="btn-secondary" onclick="openArticle('failed-payments')">Open</button></div>
                            </div>
                        </div>

                        <div class="bill-item">
                            <div class="bill-left">
                                <h4>Understanding fees</h4>
                                <p>Learn about our fees and transaction limits</p>
                            </div>
                            <div class="bill-right">
                                <div class="bill-action"><button class="btn-secondary" onclick="openArticle('fees')">Open</button></div>
                            </div>
                        </div>
                    </section>

                    <aside class="pay-panel">
                        <div class="section-header">
                            <h3>Contact Support</h3>
                        </div>

                        <form id="supportForm" class="pay-form">
                            <label for="supportTopic">Topic</label>
                            <select id="supportTopic">
                                <option value="account">Account</option>
                                <option value="payments">Payments</option>
                                <option value="technical">Technical</option>
                                <option value="billing">Billing</option>
                                <option value="other">Other</option>
                            </select>

                            <label for="supportMessage">Message</label>
                            <textarea id="supportMessage" rows="5" placeholder="Describe your issue or question" style="width:100%; padding:0.8rem; border-radius:10px; background: rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); color:#fff;"></textarea>

                            <label for="supportAttachment">Attachment (optional)</label>
                            <input id="supportAttachment" type="file" accept="image/*,.pdf">

                            <div class="form-actions">
                                <button type="button" class="btn secondary" onclick="resetSupportForm()">Reset</button>
                                <button type="submit" class="btn primary">Send Ticket</button>
                            </div>
                        </form>

                        <div class="recent-payments" style="margin-top:1rem;">
                            <div class="section-header">
                                <h4>My Tickets</h4>
                            </div>
                            <div id="ticketList" class="payments-list">
                                <div class="payment-item">
                                    <div class="payment-left">Order #12345
                                        <div class="muted">Open • 1 hr ago</div>
                                    </div>
                                    <div class="payment-right">Technical</div>
                                </div>
                                <div class="payment-item">
                                    <div class="payment-left">Refund request
                                        <div class="muted">Closed • 3 days ago</div>
                                    </div>
                                    <div class="payment-right">Billing</div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>

            </div>
        </main>
    </div>

    <script>
        // Minimal interactivity for support page (frontend demo)
        document.getElementById('supportForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const topic = document.getElementById('supportTopic').value;
            const msg = document.getElementById('supportMessage').value.trim();
            if (!msg) {
                alert('Please enter a message');
                return;
            }
            const list = document.getElementById('ticketList');
            const item = document.createElement('div');
            item.className = 'payment-item';
            item.innerHTML = `<div class="payment-left">New ticket<div class="muted">Open • Just now</div></div><div class="payment-right">${topic}</div>`;
            list.insertBefore(item, list.firstChild);
            alert('Ticket submitted (demo). Support will contact you soon.');
            this.reset();
        });

        function resetSupportForm() {
            document.getElementById('supportForm').reset();
        }

        function showAllArticles() {
            alert('Open articles list (demo)');
        }

        function openArticle(id) {
            alert('Open article: ' + id);
        }
    </script>

</body>

</html>