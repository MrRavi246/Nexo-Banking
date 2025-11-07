// Dashboard User Data Handler
document.addEventListener('DOMContentLoaded', function() {
    fetchUserData();
    
    // Refresh data every 5 minutes
    setInterval(fetchUserData, 300000);
});

async function fetchUserData() {
    try {
        const response = await fetch('../../backend/get_user_data.php', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        console.log('Dashboard Data Response:', data); // Debug log
        
        if (data.success) {
            console.log('Updating dashboard with user:', data.data.user.full_name); // Debug log
            updateDashboard(data.data);
        } else {
            console.error('Failed to fetch user data:', data.message);
            
            // If session expired, redirect to login
            if (response.status === 401 || data.message.includes('session') || data.message.includes('authenticated')) {
                showNotification('Your session has expired. Please login again.', 'error');
                setTimeout(() => {
                    window.location.href = '../auth/login.php';
                }, 2000);
            }
        }
    } catch (error) {
        console.error('Error fetching user data:', error);
    }
}

function updateDashboard(data) {
    const { user, accounts, transactions, savings_goals, spending, notifications, credit_score } = data;
    
    // Update user profile information
    updateUserProfile(user);
    
    // Update account balances
    updateAccountBalances(accounts);
    
    // Update recent transactions
    if (transactions && transactions.length > 0) {
        updateTransactions(transactions);
    }
    
    // Update savings goals
    if (savings_goals && savings_goals.length > 0) {
        updateSavingsGoals(savings_goals);
    }
    
    // Update spending overview
    if (spending) {
        updateSpendingOverview(spending);
    }
    
    // Update notification badge
    if (notifications) {
        updateNotificationBadge(notifications.unread_count);
    }
    
    // Update credit score
    if (credit_score) {
        updateCreditScore(credit_score);
    }
}

function updateUserProfile(user) {
    console.log('Updating user profile with:', user); // Debug log
    
    // Update profile image
    const profileImages = document.querySelectorAll('.avatar, #profileImage, .user-profile img');
    console.log('Found profile images:', profileImages.length); // Debug log
    profileImages.forEach(img => {
        if (img) {
            img.src = user.profile_image;
            img.alt = user.full_name;
        }
    });
    
    // Update username displays
    const usernameElements = document.querySelectorAll('.username');
    console.log('Found username elements:', usernameElements.length, 'Updating to:', user.full_name); // Debug log
    usernameElements.forEach(el => {
        if (el) el.textContent = user.full_name;
    });
    
    // Update member type
    const memberTypeElements = document.querySelectorAll('.user-type');
    console.log('Found member type elements:', memberTypeElements.length); // Debug log
    memberTypeElements.forEach(el => {
        if (el) el.textContent = user.member_type_display;
    });
    
    // Update welcome message
    const welcomeHeader = document.querySelector('.profile-info h1');
    console.log('Found welcome header:', welcomeHeader); // Debug log
    if (welcomeHeader) {
        welcomeHeader.textContent = `Welcome back, ${user.first_name}!`;
    }
}

function updateAccountBalances(accounts) {
    // Update total balance
    const totalBalanceEl = document.querySelector('.total-balance .balance-amount');
    if (totalBalanceEl) {
        totalBalanceEl.textContent = '$' + accounts.total_balance;
    }
    
    // Update checking account
    const checkingBalanceEl = document.querySelector('.checking .balance-amount');
    if (checkingBalanceEl) {
        checkingBalanceEl.textContent = '$' + accounts.checking.balance;
    }
    
    const checkingAccountEl = document.querySelector('.checking .account-number');
    if (checkingAccountEl) {
        checkingAccountEl.textContent = accounts.checking.account_number;
    }
    
    // Update savings account
    const savingsBalanceEl = document.querySelector('.savings .balance-amount');
    if (savingsBalanceEl) {
        savingsBalanceEl.textContent = '$' + accounts.savings.balance;
    }
    
    const savingsAccountEl = document.querySelector('.savings .account-number');
    if (savingsAccountEl && accounts.savings.account_number !== 'N/A') {
        savingsAccountEl.textContent = accounts.savings.account_number;
    }
    
    // Update credit card
    const creditBalanceEl = document.querySelector('.credit .balance-amount');
    if (creditBalanceEl) {
        creditBalanceEl.textContent = '$' + accounts.credit.balance;
    }
    
    const creditLimitEl = document.querySelector('.credit .credit-limit');
    if (creditLimitEl) {
        creditLimitEl.textContent = '$' + accounts.credit.credit_limit + ' limit';
    }
    
    // Update account dropdowns in modals/forms
    updateAccountDropdowns(accounts.all_accounts);
}

function updateAccountDropdowns(accounts) {
    const fromAccountSelect = document.getElementById('fromAccount');
    if (fromAccountSelect && accounts && accounts.length > 0) {
        fromAccountSelect.innerHTML = '';
        
        accounts.forEach(account => {
            if (account.status === 'active' && account.account_type !== 'credit') {
                const option = document.createElement('option');
                option.value = account.account_id;
                const accountTypeName = account.account_type.charAt(0).toUpperCase() + account.account_type.slice(1);
                const maskedNumber = '**** ' + account.account_number.slice(-4);
                const balance = parseFloat(account.balance).toFixed(2);
                option.textContent = `${accountTypeName} Account - ${maskedNumber} ($${balance})`;
                fromAccountSelect.appendChild(option);
            }
        });
    }
}

function updateTransactions(transactions) {
    const transactionList = document.querySelector('.transaction-list');
    if (!transactionList) return;
    
    transactionList.innerHTML = '';
    
    transactions.forEach(transaction => {
        const transactionItem = createTransactionItem(transaction);
        transactionList.appendChild(transactionItem);
    });
}

function createTransactionItem(transaction) {
    const item = document.createElement('div');
    item.className = 'transaction-item';
    
    const icon = getTransactionIcon(transaction.category || transaction.transaction_type);
    const iconClass = getTransactionIconClass(transaction.category || transaction.transaction_type);
    const isNegative = ['withdrawal', 'payment', 'transfer', 'fee'].includes(transaction.transaction_type);
    const amountClass = isNegative ? 'negative' : 'positive';
    const amountPrefix = isNegative ? '-' : '+';
    
    const date = formatTransactionDate(transaction.transaction_date);
    const description = transaction.description || formatTransactionType(transaction.transaction_type);
    
    item.innerHTML = `
        <div class="transaction-icon ${iconClass}">
            <i class="${icon}"></i>
        </div>
        <div class="transaction-details">
            <div class="transaction-title">${description}</div>
            <div class="transaction-date">${date}</div>
        </div>
        <div class="transaction-amount ${amountClass}">${amountPrefix}$${parseFloat(transaction.amount).toFixed(2)}</div>
    `;
    
    return item;
}

function getTransactionIcon(category) {
    const icons = {
        'shopping': 'ri-shopping-bag-line',
        'food': 'ri-cup-line',
        'groceries': 'ri-shopping-cart-line',
        'transport': 'ri-gas-station-line',
        'transportation': 'ri-car-line',
        'utilities': 'ri-tools-line',
        'entertainment': 'ri-tv-line',
        'salary': 'ri-arrow-down-line',
        'deposit': 'ri-arrow-down-line',
        'transfer': 'ri-send-plane-line',
        'payment': 'ri-bill-line',
        'withdrawal': 'ri-arrow-up-line'
    };
    
    return icons[category?.toLowerCase()] || 'ri-exchange-line';
}

function getTransactionIconClass(category) {
    const classes = {
        'shopping': 'amazon',
        'food': 'coffee',
        'groceries': 'amazon',
        'transport': 'gas',
        'transportation': 'gas',
        'salary': 'salary',
        'deposit': 'salary',
        'transfer': 'transfer',
        'entertainment': 'netflix'
    };
    
    return classes[category?.toLowerCase()] || 'transfer';
}

function formatTransactionType(type) {
    const types = {
        'deposit': 'Deposit',
        'withdrawal': 'Withdrawal',
        'transfer': 'Transfer',
        'payment': 'Payment',
        'refund': 'Refund',
        'fee': 'Fee',
        'interest': 'Interest'
    };
    
    return types[type] || type;
}

function formatTransactionDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) {
        const hours = date.getHours();
        const minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        const displayMinutes = minutes.toString().padStart(2, '0');
        return `Today, ${displayHours}:${displayMinutes} ${ampm}`;
    } else if (diffDays === 1) {
        return 'Yesterday';
    } else if (diffDays < 7) {
        return `${diffDays} days ago`;
    } else {
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
}

function updateSavingsGoals(goals) {
    const goalsContainer = document.querySelector('.savings-goals .widget-content');
    if (!goalsContainer) return;
    
    goalsContainer.innerHTML = '';
    
    goals.forEach((goal, index) => {
        const goalItem = createSavingsGoalItem(goal, index);
        goalsContainer.appendChild(goalItem);
    });
}

function createSavingsGoalItem(goal, index) {
    const item = document.createElement('div');
    const percentage = Math.round((goal.current_amount / goal.target_amount) * 100);
    
    const colors = ['vacation', 'emergency', 'car', 'house', 'education'];
    const colorClass = colors[index % colors.length];
    
    item.className = `goal-item ${colorClass}`;
    item.innerHTML = `
        <div class="goal-info">
            <div class="goal-title">${goal.goal_name}</div>
            <div class="goal-progress">
                <div class="progress-bar">
                    <div class="progress-fill ${colorClass}-progress" style="width: ${percentage}%"></div>
                </div>
                <div class="progress-text">$${parseFloat(goal.current_amount).toFixed(0)} / $${parseFloat(goal.target_amount).toFixed(0)}</div>
            </div>
        </div>
        <div class="goal-percentage ${colorClass}-color">${percentage}%</div>
    `;
    
    return item;
}

function updateSpendingOverview(spending) {
    // Update monthly budget
    const budgetSpent = document.querySelector('.budget-amount .spent');
    if (budgetSpent) {
        budgetSpent.textContent = '$' + parseFloat(spending.total_month || 0).toFixed(0);
    }
    
    const budgetTotal = document.querySelector('.budget-amount .total');
    if (budgetTotal) {
        budgetTotal.textContent = '/ $' + spending.budget + ' budgeted';
    }
    
    const budgetPercentage = document.querySelector('.budget-percentage');
    if (budgetPercentage) {
        budgetPercentage.textContent = spending.percentage + '% used';
    }
    
    const budgetFill = document.querySelector('.budget-fill');
    if (budgetFill) {
        budgetFill.style.width = spending.percentage + '%';
    }
    
    // Update budget status
    const budgetStatus = document.querySelector('.budget-status');
    if (budgetStatus) {
        if (spending.percentage < 70) {
            budgetStatus.textContent = 'On Track';
            budgetStatus.className = 'budget-status good';
        } else if (spending.percentage < 90) {
            budgetStatus.textContent = 'Watch Spending';
            budgetStatus.className = 'budget-status warning';
        } else {
            budgetStatus.textContent = 'Over Budget';
            budgetStatus.className = 'budget-status danger';
        }
    }
    
    // Update spending categories in chart
    if (spending.by_category && spending.by_category.length > 0) {
        updateSpendingChart(spending.by_category);
    }
}

function updateSpendingChart(categories) {
    // Update category list under chart
    const categoriesList = document.querySelector('.spending-categories');
    if (!categoriesList) return;
    
    categoriesList.innerHTML = '';
    
    categories.slice(0, 4).forEach((category, index) => {
        const colors = ['food', 'shopping', 'transport', 'bills'];
        const colorClass = colors[index % colors.length];
        
        const categoryItem = document.createElement('div');
        categoryItem.className = 'category-item';
        categoryItem.innerHTML = `
            <div class="category-color ${colorClass}"></div>
            <span>${category.category || 'Other'}</span>
            <span class="amount">$${parseFloat(category.total_spent).toFixed(0)}</span>
        `;
        
        categoriesList.appendChild(categoryItem);
    });
}

function updateNotificationBadge(count) {
    const badge = document.getElementById('notifBadge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline-flex' : 'none';
    }
}

function updateCreditScore(creditScore) {
    // Update score number
    const scoreNumber = document.querySelector('.score-number');
    if (scoreNumber) {
        scoreNumber.textContent = creditScore.score;
    }
    
    // Update score label
    const scoreLabel = document.querySelector('.score-label');
    if (scoreLabel) {
        scoreLabel.textContent = creditScore.status.charAt(0).toUpperCase() + creditScore.status.slice(1);
    }
    
    // Update score date
    const scoreDate = document.querySelector('.score-date');
    if (scoreDate) {
        const date = new Date(creditScore.date);
        const daysAgo = Math.floor((new Date() - date) / (1000 * 60 * 60 * 24));
        scoreDate.textContent = daysAgo === 0 ? 'Updated today' : 
                               daysAgo === 1 ? 'Updated yesterday' : 
                               `Updated ${daysAgo} days ago`;
    }
    
    // Update score circle (SVG progress)
    const scoreCircle = document.querySelector('.score-circle circle:last-child');
    if (scoreCircle) {
        // Credit scores range from 300-850, normalize to 0-100%
        const percentage = ((creditScore.score - 300) / 550) * 100;
        const circumference = 283; // 2 * PI * radius (45)
        const offset = circumference - (percentage / 100) * circumference;
        scoreCircle.style.strokeDashoffset = offset;
        
        // Update color based on score
        if (creditScore.score >= 740) {
            scoreCircle.style.stroke = '#10b981'; // Green - Excellent
        } else if (creditScore.score >= 670) {
            scoreCircle.style.stroke = '#3b82f6'; // Blue - Good
        } else if (creditScore.score >= 580) {
            scoreCircle.style.stroke = '#f59e0b'; // Orange - Fair
        } else {
            scoreCircle.style.stroke = '#ef4444'; // Red - Poor
        }
    }
    
    // Update insights
    const insightsContainer = document.querySelector('.score-insights');
    if (insightsContainer && creditScore.change !== undefined) {
        let insightsHTML = '';
        
        if (creditScore.change !== 0) {
            const changeClass = creditScore.change > 0 ? 'positive' : 'negative';
            const changeIcon = creditScore.change > 0 ? 'ri-arrow-up-line' : 'ri-arrow-down-line';
            const changeText = creditScore.change > 0 ? 'increased' : 'decreased';
            
            insightsHTML += `
                <div class="insight-item ${changeClass}">
                    <i class="${changeIcon}"></i>
                    <span>Score ${changeText} by ${Math.abs(creditScore.change)} points</span>
                </div>
            `;
        }
        
        // Add a tip based on score
        if (creditScore.score < 740) {
            insightsHTML += `
                <div class="insight-item tip">
                    <i class="ri-lightbulb-line"></i>
                    <span>Pay down credit card balance to improve score</span>
                </div>
            `;
        } else {
            insightsHTML += `
                <div class="insight-item tip">
                    <i class="ri-lightbulb-line"></i>
                    <span>Great job! Keep maintaining your excellent credit</span>
                </div>
            `;
        }
        
        insightsContainer.innerHTML = insightsHTML;
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icon = type === 'success' ? 'ri-check-circle-line' : 
                 type === 'error' ? 'ri-error-warning-line' : 
                 'ri-information-line';
    
    notification.innerHTML = `
        <i class="${icon}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Handle logout
document.addEventListener('DOMContentLoaded', function() {
    const logoutLink = document.getElementById('logoutLink');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../../backend/logout.php';
            }
        });
    }
    
    // Also handle sidebar logout
    const sidebarLogout = document.querySelector('.sidebar-footer');
    if (sidebarLogout) {
        sidebarLogout.addEventListener('click', function(e) {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../../backend/logout.php';
            }
        });
    }
});
