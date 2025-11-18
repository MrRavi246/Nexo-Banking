// Admin Dashboard JavaScript
let refreshInterval;
const REFRESH_INTERVAL = 30000; // Refresh every 30 seconds

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    
    // Auto-refresh every 30 seconds
    refreshInterval = setInterval(loadDashboardData, REFRESH_INTERVAL);
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
});

// Load dashboard data
async function loadDashboardData() {
    try {
        const response = await fetch('../backend/admin_get_dashboard.php', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response received:', text);
            throw new Error('Server returned non-JSON response');
        }
        
        const data = await response.json();
        
        if (data.success && data.data) {
            updateDashboard(data.data);
        } else {
            const errorMsg = data.message || 'Unknown error';
            console.error('API error:', errorMsg, data);
            showError('Failed to load dashboard data: ' + errorMsg);
        }
    } catch (error) {
        console.error('Dashboard data fetch error:', error);
        showError('Unable to load dashboard data: ' + error.message);
    }
}

// Update dashboard with data
function updateDashboard(data) {
    // Update total users
    const totalUsersEl = document.getElementById('totalUsers');
    if (totalUsersEl) {
        totalUsersEl.textContent = formatNumber(data.total_users);
    }
    
    // Update user growth
    const userGrowthEl = document.getElementById('userGrowth');
    if (userGrowthEl) {
        updateGrowthIndicator(userGrowthEl, data.user_growth);
    }
    
    // Update total transactions
    const totalTransactionsEl = document.getElementById('totalTransactions');
    if (totalTransactionsEl) {
        totalTransactionsEl.textContent = '$' + formatMoney(data.total_transactions);
    }
    
    // Update transaction growth
    const transactionGrowthEl = document.getElementById('transactionGrowth');
    if (transactionGrowthEl) {
        updateGrowthIndicator(transactionGrowthEl, data.transaction_growth);
    }
    
    // Update active accounts
    const activeAccountsEl = document.getElementById('activeAccounts');
    if (activeAccountsEl) {
        activeAccountsEl.textContent = formatNumber(data.active_accounts);
    }
    
    // Update account growth
    const accountGrowthEl = document.getElementById('accountGrowth');
    if (accountGrowthEl) {
        updateGrowthIndicator(accountGrowthEl, data.account_growth);
    }
    
    // Update monthly revenue
    const monthlyRevenueEl = document.getElementById('monthlyRevenue');
    if (monthlyRevenueEl) {
        monthlyRevenueEl.textContent = '$' + formatMoney(data.monthly_revenue);
    }
    
    // Update revenue growth
    const revenueGrowthEl = document.getElementById('revenueGrowth');
    if (revenueGrowthEl) {
        updateGrowthIndicator(revenueGrowthEl, data.revenue_growth);
    }
    
    // Update recent activities
    if (data.recent_activities && data.recent_activities.length > 0) {
        updateRecentActivities(data.recent_activities);
    } else {
        const activityList = document.getElementById('recentActivityList');
        if (activityList) {
            activityList.innerHTML = '<p style="text-align: center; color: #888;">No recent activities</p>';
        }
    }
    
    // Update pending users
    const pendingUsersEl = document.getElementById('pendingUsers');
    if (pendingUsersEl) {
        pendingUsersEl.textContent = data.pending_users;
        if (data.pending_users > 0) {
            pendingUsersEl.style.color = '#f39c12';
            pendingUsersEl.style.fontWeight = 'bold';
        } else {
            pendingUsersEl.style.color = '';
            pendingUsersEl.style.fontWeight = '';
        }
    }
    
    // Update pending loans
    const pendingLoansEl = document.getElementById('pendingLoans');
    if (pendingLoansEl) {
        pendingLoansEl.textContent = data.pending_loans;
        if (data.pending_loans > 0) {
            pendingLoansEl.style.color = '#f39c12';
            pendingLoansEl.style.fontWeight = 'bold';
        } else {
            pendingLoansEl.style.color = '';
            pendingLoansEl.style.fontWeight = '';
        }
    }
    
    // Update last updated timestamp
    const lastUpdatedEl = document.getElementById('lastUpdated');
    if (lastUpdatedEl && data.timestamp) {
        const timestamp = new Date(data.timestamp);
        lastUpdatedEl.textContent = timestamp.toLocaleTimeString();
    }
}

// Update growth indicator
function updateGrowthIndicator(element, growthValue) {
    const absValue = Math.abs(growthValue);
    const sign = growthValue >= 0 ? '+' : '';
    
    element.textContent = sign + growthValue.toFixed(1) + '% from last month';
    
    // Remove existing classes
    element.classList.remove('positive', 'negative', 'neutral');
    
    // Add appropriate class
    if (growthValue > 0) {
        element.classList.add('positive');
    } else if (growthValue < 0) {
        element.classList.add('negative');
    } else {
        element.classList.add('neutral');
    }
}

// Update recent activities list
function updateRecentActivities(activities) {
    const activityList = document.getElementById('recentActivityList');
    if (!activityList) return;
    
    let html = '<div class="activity-items">';
    
    activities.forEach(activity => {
        const timestamp = new Date(activity.timestamp);
        const timeStr = formatTimeAgo(timestamp);
        
        html += `
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="${getActivityIcon(activity.action)}"></i>
                </div>
                <div class="activity-details">
                    <div class="activity-description">
                        <strong>${escapeHtml(activity.actor)}</strong> 
                        ${formatActionText(activity.action, activity.table)}
                    </div>
                    <div class="activity-time">${timeStr}</div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    activityList.innerHTML = html;
}

// Get appropriate icon for activity type
function getActivityIcon(action) {
    const iconMap = {
        'LOGIN_SUCCESS': 'ri-login-box-line',
        'LOGIN_FAILED': 'ri-error-warning-line',
        'LOGOUT': 'ri-logout-box-line',
        'USER_REGISTRATION': 'ri-user-add-line',
        'USER_APPROVAL': 'ri-checkbox-circle-line',
        'USER_REJECTION': 'ri-close-circle-line',
        'TRANSACTION_CREATE': 'ri-exchange-line',
        'ACCOUNT_CREATE': 'ri-bank-card-line',
        'UPDATE': 'ri-edit-line',
        'DELETE': 'ri-delete-bin-line',
        'CREATE': 'ri-add-line'
    };
    
    return iconMap[action] || 'ri-information-line';
}

// Format action text
function formatActionText(action, table) {
    const actionText = action.toLowerCase().replace(/_/g, ' ');
    const tableText = table ? ' on ' + table : '';
    return actionText + tableText;
}

// Format time ago
function formatTimeAgo(date) {
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'just now';
    
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return minutes + ' minute' + (minutes !== 1 ? 's' : '') + ' ago';
    
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return hours + ' hour' + (hours !== 1 ? 's' : '') + ' ago';
    
    const days = Math.floor(hours / 24);
    if (days < 7) return days + ' day' + (days !== 1 ? 's' : '') + ' ago';
    
    return date.toLocaleDateString();
}

// Format number with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Format money
function formatMoney(amount) {
    if (amount >= 1000000) {
        return (amount / 1000000).toFixed(1) + 'M';
    } else if (amount >= 1000) {
        return (amount / 1000).toFixed(1) + 'K';
    } else {
        return amount.toFixed(2);
    }
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show error message
function showError(message) {
    console.error(message);
    
    // You can implement a toast notification here if desired
    const errorContainer = document.getElementById('errorContainer');
    if (errorContainer) {
        errorContainer.innerHTML = `
            <div class="error-message">
                <i class="ri-error-warning-line"></i>
                ${escapeHtml(message)}
            </div>
        `;
        
        setTimeout(() => {
            errorContainer.innerHTML = '';
        }, 5000);
    }
}

// Export system report function (referenced in dashboard.php)
function exportSystemReport() {
    window.location.href = '../backend/admin_export_report.php';
}

// Open quick actions (placeholder function)
function openQuickActions() {
    alert('Quick actions menu coming soon!');
}
