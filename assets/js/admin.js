// Minimal admin JS helpers
document.addEventListener('DOMContentLoaded', function(){
  // confirmation on delete buttons (forms use onsubmit confirm too)
  document.querySelectorAll('.admin-table form').forEach(function(f){
    f.addEventListener('submit', function(e){
      if (!confirm('Are you sure? This action cannot be undone.')) {
        e.preventDefault();
      }
    })
  })
});
// Admin Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeDropdowns();
    initializeCharts();
    initializeNotifications();
    
    // Set up event listeners
    setupEventListeners();
});

// Dropdown functionality
function initializeDropdowns() {
    const notifToggle = document.getElementById('notifToggle');
    const notifDropdown = document.getElementById('notifDropdown');
    const settingsToggle = document.getElementById('settingsToggle');
    const settingsDropdown = document.getElementById('settingsDropdown');

    if (notifToggle && notifDropdown) {
        notifToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown(notifDropdown, this);
            closeOtherDropdowns([settingsDropdown]);
        });
    }

    if (settingsToggle && settingsDropdown) {
        settingsToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown(settingsDropdown, this);
            closeOtherDropdowns([notifDropdown]);
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-icon')) {
            closeAllDropdowns();
        }
    });
}

function toggleDropdown(dropdown, toggle) {
    const isOpen = dropdown.classList.contains('open');
    dropdown.classList.toggle('open');
    toggle.setAttribute('aria-expanded', String(!isOpen));
    dropdown.setAttribute('aria-hidden', String(isOpen));
}

function closeOtherDropdowns(dropdowns) {
    dropdowns.forEach(dropdown => {
        if (dropdown) {
            dropdown.classList.remove('open');
            dropdown.setAttribute('aria-hidden', 'true');
        }
    });
}

function closeAllDropdowns() {
    const dropdowns = document.querySelectorAll('.notification-dropdown, .nav-dropdown');
    dropdowns.forEach(dropdown => {
        dropdown.classList.remove('open');
        dropdown.setAttribute('aria-hidden', 'true');
    });
    
    const toggles = document.querySelectorAll('[aria-expanded]');
    toggles.forEach(toggle => {
        toggle.setAttribute('aria-expanded', 'false');
    });
}

// Initialize Charts
function initializeCharts() {
    // Mini charts for overview cards
    initializeOverviewCharts();
    
    // Main analytics chart
    initializeAnalyticsChart();
}

function initializeOverviewCharts() {
    const chartConfigs = [
        { id: 'usersChart', data: [65, 59, 80, 81, 56, 55, 40], color: '#eb7ef2' },
        { id: 'transactionsChart', data: [28, 48, 40, 19, 86, 27, 90], color: '#3b82f6' },
        { id: 'accountsChart', data: [45, 25, 35, 55, 65, 45, 75], color: '#22c55e' },
        { id: 'revenueChart', data: [15, 35, 25, 45, 55, 35, 65], color: '#f59e0b' }
    ];

    chartConfigs.forEach(config => {
        const ctx = document.getElementById(config.id);
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['', '', '', '', '', '', ''],
                    datasets: [{
                        data: config.data,
                        borderColor: config.color,
                        backgroundColor: `${config.color}20`,
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }
    });
}

function initializeAnalyticsChart() {
    const ctx = document.getElementById('analyticsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        label: 'Users',
                        data: [1200, 1350, 1180, 1450, 1680, 1520, 1750, 1890, 1650, 1920, 2100, 2250],
                        borderColor: '#eb7ef2',
                        backgroundColor: 'rgba(235, 126, 242, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Transactions',
                        data: [800, 950, 880, 1020, 1150, 1080, 1280, 1350, 1200, 1420, 1580, 1650],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Revenue ($k)',
                        data: [45, 52, 48, 58, 65, 61, 72, 78, 70, 85, 92, 98],
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: '#888',
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#888'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#888'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
}

// Notification handling
function initializeNotifications() {
    const clearBtn = document.getElementById('clearAll');
    const notifBadge = document.getElementById('notifBadge');
    const notifDropdown = document.getElementById('notifDropdown');

    if (clearBtn) {
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            clearAllNotifications();
        });
    }

    // Handle notification item clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.notification-item')) {
            const item = e.target.closest('.notification-item');
            markNotificationAsRead(item);
        }
    });

    updateNotificationBadge();
}

function clearAllNotifications() {
    const notifications = document.querySelectorAll('.notification-item');
    notifications.forEach(notification => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    });
    
    setTimeout(() => {
        updateNotificationBadge();
        showToast('All notifications cleared', 'info');
    }, 300);
}

function markNotificationAsRead(item) {
    item.classList.remove('unread');
    updateNotificationBadge();
}

function updateNotificationBadge() {
    const badge = document.getElementById('notifBadge');
    const dropdown = document.getElementById('notifDropdown');
    
    if (!badge || !dropdown) return;
    
    const unreadCount = dropdown.querySelectorAll('.notification-item.unread').length;
    badge.textContent = unreadCount > 0 ? unreadCount : '';
    badge.style.display = unreadCount > 0 ? 'flex' : 'none';
}

// Event listeners
function setupEventListeners() {
    // Menu item active state
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            menuItems.forEach(mi => mi.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Maintenance mode toggle
    const maintenanceToggle = document.getElementById('toggleMaintenanceMode');
    if (maintenanceToggle) {
        maintenanceToggle.addEventListener('change', function() {
            if (this.checked) {
                showConfirmation('Enable Maintenance Mode?', 
                    'This will temporarily disable user access to the system.',
                    () => enableMaintenanceMode(),
                    () => this.checked = false
                );
            } else {
                disableMaintenanceMode();
            }
        });
    }
}

// Admin functions
function exportSystemReport() {
    showToast('Generating system report...', 'info');
    
    // Simulate export process
    setTimeout(() => {
        showToast('System report exported successfully', 'success');
        
        // Create and trigger download
        const link = document.createElement('a');
        link.href = '#'; // In real implementation, this would be the report URL
        link.download = `nexo-system-report-${new Date().toISOString().split('T')[0]}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }, 2000);
}

function openQuickActions() {
    const modal = document.getElementById('quickActionModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeQuickActionModal() {
    const modal = document.getElementById('quickActionModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function refreshSystemHealth() {
    const btn = document.querySelector('.refresh-btn');
    const icon = btn.querySelector('i');
    
    // Animate refresh icon
    icon.style.animation = 'spin 1s linear';
    showToast('Refreshing system health...', 'info');
    
    setTimeout(() => {
        icon.style.animation = '';
        showToast('System health updated', 'success');
        
        // Update health metrics (simulate new data)
        updateHealthMetrics();
    }, 1000);
}

function updateHealthMetrics() {
    const metrics = [
        { selector: '.health-metric:nth-child(1) .metric-value', value: '99.9%' },
        { selector: '.health-metric:nth-child(2) .metric-value', value: Math.floor(Math.random() * 200 + 700) + 'ms' },
        { selector: '.health-metric:nth-child(3) .metric-value', value: Math.floor(Math.random() * 100 + 100) + 'ms' },
        { selector: '.health-metric:nth-child(4) .metric-value', value: Math.floor(Math.random() * 20 + 60) + '%' }
    ];

    metrics.forEach(metric => {
        const element = document.querySelector(metric.selector);
        if (element) {
            element.textContent = metric.value;
        }
    });
}

function updateAnalytics(timeRange) {
    showToast(`Updating analytics for ${timeRange}...`, 'info');
    
    // In a real implementation, this would fetch new data based on the time range
    setTimeout(() => {
        showToast('Analytics updated successfully', 'success');
    }, 1000);
}

// Approval functions
function approveRequest(requestId) {
    showConfirmation('Approve Request?', 
        'Are you sure you want to approve this request?',
        () => processApproval(requestId, true)
    );
}

function rejectRequest(requestId) {
    showConfirmation('Reject Request?', 
        'Are you sure you want to reject this request?',
        () => processApproval(requestId, false)
    );
}

function processApproval(requestId, approved) {
    const status = approved ? 'approved' : 'rejected';
    const action = approved ? 'Approving' : 'Rejecting';
    
    showToast(`${action} request ${requestId}...`, 'info');
    
    // Simulate API call
    setTimeout(() => {
        // Remove the approval item from the list
        const approvalItem = document.querySelector(`[onclick*="${requestId}"]`)?.closest('.approval-item');
        if (approvalItem) {
            approvalItem.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                approvalItem.remove();
                updateApprovalCount();
            }, 300);
        }
        
        showToast(`Request ${requestId} ${status} successfully`, approved ? 'success' : 'warning');
    }, 1000);
}

function updateApprovalCount() {
    const countElement = document.querySelector('.approval-count');
    const approvalItems = document.querySelectorAll('.approval-item');
    
    if (countElement) {
        const count = approvalItems.length;
        countElement.textContent = count;
        
        if (count === 0) {
            const approvalList = document.querySelector('.approval-list');
            if (approvalList) {
                approvalList.innerHTML = '<div style="text-align: center; color: #888; padding: 2rem;">No pending approvals</div>';
            }
        }
    }
}

// Quick action functions
function openUserModal() {
    closeQuickActionModal();
    showToast('Opening user creation form...', 'info');
    // In real implementation, this would open a user creation modal
}

function sendSystemAlert() {
    closeQuickActionModal();
    showToast('Opening system alert composer...', 'info');
    // In real implementation, this would open an alert composition modal
}

function blockSuspiciousAccount() {
    closeQuickActionModal();
    showConfirmation('Block Suspicious Account?', 
        'This will immediately block access for flagged accounts.',
        () => {
            showToast('Suspicious accounts blocked', 'success');
            addActivity('security-action', 'Suspicious accounts blocked', 'Multiple accounts blocked for security', 'Just now');
        }
    );
}

function generateSystemReport() {
    closeQuickActionModal();
    exportSystemReport();
}

function openSystemAlert() {
    showToast('Opening system alert...', 'info');
}

function openMaintenanceModal() {
    closeQuickActionModal();
    showToast('Opening maintenance scheduler...', 'info');
}

// Maintenance mode functions
function enableMaintenanceMode() {
    showToast('Enabling maintenance mode...', 'warning');
    
    setTimeout(() => {
        showToast('Maintenance mode enabled', 'warning');
        updateSystemStatus('maintenance', 'Maintenance Mode');
        addActivity('system-action', 'Maintenance mode enabled', 'System entered maintenance mode', 'Just now');
    }, 1000);
}

function disableMaintenanceMode() {
    showToast('Disabling maintenance mode...', 'info');
    
    setTimeout(() => {
        showToast('Maintenance mode disabled', 'success');
        updateSystemStatus('online', 'System Online');
        addActivity('system-action', 'Maintenance mode disabled', 'System returned to normal operation', 'Just now');
    }, 1000);
}

function updateSystemStatus(status, text) {
    const indicator = document.querySelector('.status-indicator');
    const statusText = document.querySelector('.system-status span');
    
    if (indicator) {
        indicator.className = `status-indicator ${status}`;
    }
    
    if (statusText) {
        statusText.textContent = text;
    }
}

// Utility functions
function addActivity(type, title, detail, time) {
    const activityList = document.querySelector('.activity-list');
    if (!activityList) return;
    
    const activityItem = document.createElement('div');
    activityItem.className = 'activity-item';
    activityItem.innerHTML = `
        <div class="activity-icon ${type}">
            <i class="ri-${getActivityIcon(type)}"></i>
        </div>
        <div class="activity-content">
            <div class="activity-title">${title}</div>
            <div class="activity-detail">${detail}</div>
            <div class="activity-time">${time}</div>
        </div>
    `;
    
    activityList.insertBefore(activityItem, activityList.firstChild);
    
    // Remove last item if more than 4 activities
    const activities = activityList.querySelectorAll('.activity-item');
    if (activities.length > 4) {
        activities[activities.length - 1].remove();
    }
}

function getActivityIcon(type) {
    const icons = {
        'user-action': 'user-add-line',
        'transaction-action': 'exchange-line',
        'security-action': 'shield-check-line',
        'system-action': 'settings-line'
    };
    return icons[type] || 'notification-line';
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="ri-${getToastIcon(type)}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add toast styles if not already added
    if (!document.querySelector('#toast-styles')) {
        const styles = document.createElement('style');
        styles.id = 'toast-styles';
        styles.textContent = `
            .toast {
                position: fixed;
                top: 100px;
                right: 20px;
                background: rgba(15, 15, 16, 0.95);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 8px;
                padding: 1rem 1.5rem;
                color: white;
                z-index: 1001;
                animation: slideInRight 0.3s ease;
                backdrop-filter: blur(10px);
                min-width: 300px;
            }
            .toast-content {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .toast-success { border-left: 4px solid #22c55e; }
            .toast-error { border-left: 4px solid #ef4444; }
            .toast-warning { border-left: 4px solid #f59e0b; }
            .toast-info { border-left: 4px solid #3b82f6; }
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(toast);
    
    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function getToastIcon(type) {
    const icons = {
        'success': 'check-line',
        'error': 'close-line',
        'warning': 'alert-line',
        'info': 'information-line'
    };
    return icons[type] || 'information-line';
}

function showConfirmation(title, message, onConfirm, onCancel) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.style.display = 'block';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>${title}</h3>
            </div>
            <div class="modal-body">
                <p style="color: #888; margin-bottom: 1.5rem;">${message}</p>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button class="btn-secondary" onclick="cancelConfirmation()">Cancel</button>
                    <button class="btn-primary" onclick="confirmAction()">Confirm</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    // Add event handlers
    window.cancelConfirmation = function() {
        document.body.removeChild(modal);
        document.body.style.overflow = 'auto';
        if (onCancel) onCancel();
    };
    
    window.confirmAction = function() {
        document.body.removeChild(modal);
        document.body.style.overflow = 'auto';
        if (onConfirm) onConfirm();
    };
    
    // Close on backdrop click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            window.cancelConfirmation();
        }
    });
}

// Add CSS animations
const animationStyles = document.createElement('style');
animationStyles.textContent = `
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(-100%); opacity: 0; }
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .activity-item, .approval-item {
        transition: all 0.3s ease;
    }
`;
document.head.appendChild(animationStyles);

// Close modal when clicking outside
window.addEventListener('click', function(e) {
    const modal = document.getElementById('quickActionModal');
    if (e.target === modal) {
        closeQuickActionModal();
    }
});
