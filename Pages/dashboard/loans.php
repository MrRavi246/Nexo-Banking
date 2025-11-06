<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../../assets/style/nav.css">
    <link rel="stylesheet" href="../../assets/style/accounts.css">
    <link rel="stylesheet" href="../../assets/style/loans.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <title>NEXO Loans</title>
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
                <input id="globalSearch" type="text" placeholder="Search loans, applications...">
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
                    <span class="username">John Doe</span>
                    <span class="user-type">Premium Member</span>
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
                <div class="menu-item " onclick="window.location.href='pay-bills.php'">
                    <i class="ri-bill-line"></i>
                    <span>Pay Bills</span>
                </div>
                <div class="menu-item active" onclick="window.location.href='loans.php'">
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
                        <h1>Loans</h1>
                        <p>Manage your loans and apply for new financing</p>
                    </div>
                    <div class="hero-actions">
                        <button class="btn export" onclick="/* noop */">
              <i class="ri-download-line"></i>
              Export Data
            </button>
                        <button class="btn primary" onclick="document.getElementById('loanAmount').focus()">
              <i class="ri-add-line"></i>
              Apply for Loan
            </button>
                    </div>
                </div>

                <!-- Summary stats -->
                <div class="hero-stats">
                    <div class="stat-card">
                        <div class="label">Total Outstanding</div>
                        <div class="value large danger">$45,230.00</div>
                        <div class="muted">2 active loans • Next payment in 12 days</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Monthly Payment</div>
                        <div class="value">$1,850.75</div>
                        <div class="muted">Due on 5th of each month</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Credit Score</div>
                        <div class="value success">742</div>
                        <div class="muted">Excellent • Updated 3 days ago</div>
                    </div>
                </div>

                <div class="loans-main-container">
                    <section class="loans-list">
                        <div class="section-header">
                            <h3>My Loans</h3>
                            <button class="view-all-btn" onclick="showAllLoans()">View All</button>
                        </div>

                        <div class="loan-item">
                            <div class="loan-left">
                                <h4>Personal Loan</h4>
                                <p>5.2% APR • 36 months remaining</p>
                                <div class="loan-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: 45%"></div>
                                    </div>
                                    <span class="progress-text">45% paid</span>
                                </div>
                            </div>
                            <div class="loan-right">
                                <div class="loan-balance">$22,450.00</div>
                                <div class="loan-payment">$625.75/mo</div>
                                <div class="loan-action">
                                    <button class="btn primary" onclick="makePayment('Personal Loan', '625.75')">Make Payment</button>
                                </div>
                            </div>
                        </div>

                        <div class="loan-item">
                            <div class="loan-left">
                                <h4>Auto Loan</h4>
                                <p>3.8% APR • 42 months remaining</p>
                                <div class="loan-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: 62%"></div>
                                    </div>
                                    <span class="progress-text">62% paid</span>
                                </div>
                            </div>
                            <div class="loan-right">
                                <div class="loan-balance">$22,780.00</div>
                                <div class="loan-payment">$1,225.00/mo</div>
                                <div class="loan-action">
                                    <button class="btn primary" onclick="makePayment('Auto Loan', '1225.00')">Make Payment</button>
                                </div>
                            </div>
                        </div>

                        <!-- Loan Application Options -->
                        <div class="loan-options">
                            <div class="section-header">
                                <h3>Available Loan Products</h3>
                            </div>
                            <div class="loan-products">
                                <div class="product-item" onclick="selectLoanType('personal')">
                                    <div class="product-icon">
                                        <i class="ri-user-line"></i>
                                    </div>
                                    <div class="product-info">
                                        <h4>Personal Loan</h4>
                                        <p>3.5% - 12.9% APR</p>
                                        <span class="product-amount">Up to $50,000</span>
                                    </div>
                                </div>
                                <div class="product-item" onclick="selectLoanType('auto')">
                                    <div class="product-icon">
                                        <i class="ri-car-line"></i>
                                    </div>
                                    <div class="product-info">
                                        <h4>Auto Loan</h4>
                                        <p>2.9% - 8.5% APR</p>
                                        <span class="product-amount">Up to $100,000</span>
                                    </div>
                                </div>
                                <div class="product-item" onclick="selectLoanType('home')">
                                    <div class="product-icon">
                                        <i class="ri-home-line"></i>
                                    </div>
                                    <div class="product-info">
                                        <h4>Home Loan</h4>
                                        <p>3.2% - 7.8% APR</p>
                                        <span class="product-amount">Up to $500,000</span>
                                    </div>
                                </div>
                                <div class="product-item" onclick="selectLoanType('business')">
                                    <div class="product-icon">
                                        <i class="ri-briefcase-line"></i>
                                    </div>
                                    <div class="product-info">
                                        <h4>Business Loan</h4>
                                        <p>4.5% - 15.9% APR</p>
                                        <span class="product-amount">Up to $250,000</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <aside class="loan-application-panel">
                        <div class="section-header">
                            <h3>Loan Application</h3>
                        </div>

                        <form id="loanForm" class="loan-form">
                            <label for="loanType">Loan Type</label>
                            <select id="loanType" onchange="updateLoanDetails()">
                <option value="">Select loan type</option>
                <option value="personal">Personal Loan</option>
                <option value="auto">Auto Loan</option>
                <option value="home">Home Loan</option>
                <option value="business">Business Loan</option>
              </select>

                            <label for="loanAmount">Loan Amount</label>
                            <div class="amount-input">
                                <span class="currency">$</span>
                                <input id="loanAmount" type="number" step="1000" min="1000" placeholder="0" required onchange="calculatePayment()">
                            </div>

                            <label for="loanTerm">Loan Term</label>
                            <select id="loanTerm" onchange="calculatePayment()">
                <option value="">Select term</option>
                <option value="12">12 months</option>
                <option value="24">24 months</option>
                <option value="36">36 months</option>
                <option value="48">48 months</option>
                <option value="60">60 months</option>
                <option value="72">72 months</option>
                <option value="84">84 months</option>
              </select>

                            <div class="loan-calculator" id="loanCalculator" style="display: none;">
                                <div class="calculator-row">
                                    <span>Estimated APR:</span>
                                    <span id="estimatedAPR">--</span>
                                </div>
                                <div class="calculator-row">
                                    <span>Monthly Payment:</span>
                                    <span id="monthlyPayment">--</span>
                                </div>
                                <div class="calculator-row">
                                    <span>Total Interest:</span>
                                    <span id="totalInterest">--</span>
                                </div>
                            </div>

                            <label for="purpose">Purpose (optional)</label>
                            <input id="purpose" type="text" placeholder="e.g., Debt consolidation, Home improvement">

                            <div class="form-actions">
                                <button type="button" class="btn secondary" onclick="resetLoanForm()">Reset</button>
                                <button type="submit" class="btn primary">Submit Application</button>
                            </div>
                        </form>

                        <div class="recent-applications">
                            <div class="section-header">
                                <h4>Recent Applications</h4>
                            </div>
                            <div id="recentApplicationsList" class="applications-list">
                                <!-- sample recent applications -->
                                <div class="application-item">
                                    <div class="application-left">
                                        <div class="application-type">Personal Loan</div>
                                        <div class="muted">Applied 5 days ago</div>
                                    </div>
                                    <div class="application-right">
                                        <div class="application-amount">$15,000</div>
                                        <div class="application-status approved">Approved</div>
                                    </div>
                                </div>
                                <div class="application-item">
                                    <div class="application-left">
                                        <div class="application-type">Auto Loan</div>
                                        <div class="muted">Applied 2 weeks ago</div>
                                    </div>
                                    <div class="application-right">
                                        <div class="application-amount">$35,000</div>
                                        <div class="application-status pending">Under Review</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
            <!-- /.content-wrapper -->
        </main>
    </div>

    <script src="../../assets/js/loans.js"></script>
</body>

</html>