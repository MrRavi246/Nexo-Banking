// Admin User Management Script
let currentTab = 'pending';

document.addEventListener('DOMContentLoaded', function() {
    // Load pending users by default
    loadPendingUsers();
    
    // Set up reject form
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitRejection();
        });
    }
});

// Show tab
function showTab(tabName) {
    currentTab = tabName;
    
    // Update tab buttons
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Update tab content
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.classList.remove('active'));
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Load data for the tab
    switch(tabName) {
        case 'pending':
            loadPendingUsers();
            break;
        case 'all':
            loadUsers('all');
            break;
        case 'active':
            loadUsers('active');
            break;
        case 'rejected':
            loadUsers('rejected');
            break;
    }
}

// Load pending users
async function loadPendingUsers() {
    try {
        const response = await fetch('../backend/manage_users.php?action=get_pending_users');
        const data = await response.json();
        
        if (data.success) {
            displayPendingUsers(data.data.users);
            updatePendingCount(data.data.users.length);
        } else {
            showError('pendingUsersContainer', data.message);
        }
    } catch (error) {
        console.error('Error loading pending users:', error);
        showError('pendingUsersContainer', 'Failed to load pending users');
    }
}

// Load users by status
async function loadUsers(status) {
    const container = status === 'all' ? 'allUsersContainer' : status + 'UsersContainer';
    
    try {
        const response = await fetch(`../backend/manage_users.php?action=get_all_users&status=${status}`);
        const data = await response.json();
        
        if (data.success) {
            displayUsersTable(container, data.data.users);
        } else {
            showError(container, data.message);
        }
    } catch (error) {
        console.error('Error loading users:', error);
        showError(container, 'Failed to load users');
    }
}

// Display pending users as cards
function displayPendingUsers(users) {
    const container = document.getElementById('pendingUsersContainer');
    
    if (users.length === 0) {
        container.innerHTML = '<p>No pending user approvals.</p>';
        return;
    }
    
    let html = '';
    users.forEach(user => {
        const initials = (user.first_name[0] + user.last_name[0]).toUpperCase();
        const createdDate = new Date(user.created_at).toLocaleDateString();
        
        html += `
            <div class="user-card">
                <div class="user-header">
                    <div class="user-info">
                        ${user.profile_image 
                            ? `<img src="/${user.profile_image}" class="user-avatar" alt="${user.first_name}">`
                            : `<div class="user-avatar">${initials}</div>`
                        }
                        <div class="user-details">
                            <h3>${user.first_name} ${user.last_name}</h3>
                            <p>${user.email} • ${user.phone_number}</p>
                            <p><small>Account: ${user.account_number} • Registered: ${createdDate}</small></p>
                        </div>
                    </div>
                    <span class="status-badge status-pending">Pending</span>
                </div>
                <div class="user-meta">
                    <p><strong>Username:</strong> ${user.username}</p>
                    <p><strong>Member Type:</strong> ${user.member_type}</p>
                    <p><strong>Address:</strong> ${user.address || 'N/A'}</p>
                    <p><strong>Date of Birth:</strong> ${user.date_of_birth || 'N/A'}</p>
                </div>
                <div class="user-actions">
                    <button class="btn btn-success" onclick="approveUser(${user.user_id})">
                        <i class="ri-check-line"></i> Approve
                    </button>
                    <button class="btn btn-danger" onclick="showRejectModal(${user.user_id})">
                        <i class="ri-close-line"></i> Reject
                    </button>
                    <button class="btn btn-info" onclick="viewUserDetails(${user.user_id})">
                        <i class="ri-eye-line"></i> View Details
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Display users as table
function displayUsersTable(containerId, users) {
    const container = document.getElementById(containerId);
    
    if (users.length === 0) {
        container.innerHTML = '<p>No users found.</p>';
        return;
    }
    
    let html = `
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Member Type</th>
                    <th>Status</th>
                    <th>Accounts</th>
                    <th>Registered</th>
                    <th>Last Login</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    users.forEach(user => {
        const statusClass = `status-${user.status}`;
        const registeredDate = new Date(user.created_at).toLocaleDateString();
        const lastLogin = user.last_login ? new Date(user.last_login).toLocaleString() : 'Never';
        
        html += `
            <tr>
                <td>${user.user_id}</td>
                <td>${user.first_name} ${user.last_name}</td>
                <td>${user.email}</td>
                <td>${user.username}</td>
                <td>${user.member_type}</td>
                <td><span class="status-badge ${statusClass}">${user.status}</span></td>
                <td>${user.account_count}</td>
                <td>${registeredDate}</td>
                <td>${lastLogin}</td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
        </table>
    `;
    
    container.innerHTML = html;
}

// Approve user
async function approveUser(userId) {
    if (!confirm('Are you sure you want to approve this user?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('user_id', userId);
        
        const response = await fetch('../backend/manage_users.php?action=approve_user', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message);
            loadPendingUsers(); // Refresh the list
        } else {
            showNotification('error', data.message);
        }
    } catch (error) {
        console.error('Error approving user:', error);
        showNotification('error', 'Failed to approve user');
    }
}

// Show reject modal
function showRejectModal(userId) {
    document.getElementById('rejectUserId').value = userId;
    document.getElementById('rejectionReason').value = '';
    document.getElementById('rejectModal').style.display = 'block';
}

// Close reject modal
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

// Submit rejection
async function submitRejection() {
    const userId = document.getElementById('rejectUserId').value;
    const reason = document.getElementById('rejectionReason').value;
    
    if (!reason.trim()) {
        showNotification('error', 'Please provide a rejection reason');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('rejection_reason', reason);
        
        const response = await fetch('../backend/manage_users.php?action=reject_user', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message);
            closeRejectModal();
            loadPendingUsers(); // Refresh the list
        } else {
            showNotification('error', data.message);
        }
    } catch (error) {
        console.error('Error rejecting user:', error);
        showNotification('error', 'Failed to reject user');
    }
}

// View user details
function viewUserDetails(userId) {
    // TODO: Implement user details modal
    alert('User details view coming soon!');
}

// Update pending count badge
function updatePendingCount(count) {
    const badge = document.getElementById('pendingCount');
    if (badge) {
        badge.textContent = count;
    }
}

// Show error message
function showError(containerId, message) {
    const container = document.getElementById(containerId);
    container.innerHTML = `<div class="error-message">${message}</div>`;
}

// Show notification
function showNotification(type, message) {
    // Remove existing notification
    const existing = document.querySelector('.admin-notification');
    if (existing) {
        existing.remove();
    }
    
    // Create new notification
    const notification = document.createElement('div');
    notification.className = `admin-notification notification-${type}`;
    notification.innerHTML = `
        <i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-line"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Add styles for notification
const style = document.createElement('style');
style.textContent = `
    .admin-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 10000;
        animation: slideInRight 0.3s ease-out;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .notification-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .notification-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .error-message {
        padding: 20px;
        background: #f8d7da;
        color: #721c24;
        border-radius: 4px;
        text-align: center;
    }
    
    .user-meta {
        margin: 15px 0;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 4px;
    }
    
    .user-meta p {
        margin: 5px 0;
        font-size: 14px;
    }
`;
document.head.appendChild(style);

// Close modal when clicking outside
window.onclick = function(event) {
    const rejectModal = document.getElementById('rejectModal');
    if (event.target === rejectModal) {
        closeRejectModal();
    }
}
