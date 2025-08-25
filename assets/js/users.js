// User Management JavaScript

// Sample user data
const userData = [
    {
        id: 'USR001',
        firstName: 'John',
        lastName: 'Smith',
        email: 'john.smith@email.com',
        phone: '+1 (555) 123-4567',
        accountType: 'premium',
        status: 'active',
        registrationDate: '2024-01-15',
        lastLogin: '2024-08-20',
        balance: 15420.50,
        avatar: 'https://i.pravatar.cc/150?img=1'
    },
    {
        id: 'USR002',
        firstName: 'Sarah',
        lastName: 'Johnson',
        email: 'sarah.johnson@email.com',
        phone: '+1 (555) 234-5678',
        accountType: 'business',
        status: 'active',
        registrationDate: '2024-02-10',
        lastLogin: '2024-08-19',
        balance: 85340.25,
        avatar: 'https://i.pravatar.cc/150?img=2'
    },
    {
        id: 'USR003',
        firstName: 'Michael',
        lastName: 'Chen',
        email: 'michael.chen@email.com',
        phone: '+1 (555) 345-6789',
        accountType: 'personal',
        status: 'inactive',
        registrationDate: '2024-03-05',
        lastLogin: '2024-08-10',
        balance: 2340.75,
        avatar: 'https://i.pravatar.cc/150?img=3'
    },
    {
        id: 'USR004',
        firstName: 'Emily',
        lastName: 'Rodriguez',
        email: 'emily.rodriguez@email.com',
        phone: '+1 (555) 456-7890',
        accountType: 'premium',
        status: 'suspended',
        registrationDate: '2024-01-28',
        lastLogin: '2024-08-15',
        balance: 5670.00,
        avatar: 'https://i.pravatar.cc/150?img=4'
    },
    {
        id: 'USR005',
        firstName: 'David',
        lastName: 'Wilson',
        email: 'david.wilson@email.com',
        phone: '+1 (555) 567-8901',
        accountType: 'business',
        status: 'pending',
        registrationDate: '2024-08-18',
        lastLogin: 'Never',
        balance: 0.00,
        avatar: 'https://i.pravatar.cc/150?img=5'
    },
    {
        id: 'USR006',
        firstName: 'Lisa',
        lastName: 'Anderson',
        email: 'lisa.anderson@email.com',
        phone: '+1 (555) 678-9012',
        accountType: 'personal',
        status: 'active',
        registrationDate: '2024-04-12',
        lastLogin: '2024-08-21',
        balance: 8920.30,
        avatar: 'https://i.pravatar.cc/150?img=6'
    },
    {
        id: 'USR007',
        firstName: 'Robert',
        lastName: 'Brown',
        email: 'robert.brown@email.com',
        phone: '+1 (555) 789-0123',
        accountType: 'premium',
        status: 'active',
        registrationDate: '2024-02-20',
        lastLogin: '2024-08-20',
        balance: 45280.90,
        avatar: 'https://i.pravatar.cc/150?img=7'
    },
    {
        id: 'USR008',
        firstName: 'Jennifer',
        lastName: 'Davis',
        email: 'jennifer.davis@email.com',
        phone: '+1 (555) 890-1234',
        accountType: 'business',
        status: 'active',
        registrationDate: '2024-05-08',
        lastLogin: '2024-08-19',
        balance: 127500.00,
        avatar: 'https://i.pravatar.cc/150?img=8'
    }
];

let currentPage = 1;
let usersPerPage = 10;
let filteredUsers = [...userData];
let selectedUsers = new Set();

document.addEventListener('DOMContentLoaded', function() {
    initializeUserManagement();
    populateUsersTable();
    setupEventListeners();
});

function initializeUserManagement() {
    // Initialize filters
    setupFilters();
    
    // Initialize search
    setupSearch();
    
    // Initialize pagination
    updatePagination();
}

function setupEventListeners() {
    // Filter change listeners
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('typeFilter').addEventListener('change', applyFilters);
    document.getElementById('dateFilter').addEventListener('change', applyFilters);
    document.getElementById('sortBy').addEventListener('change', applySorting);
    
    // View toggle listeners
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const view = this.dataset.view;
            toggleView(view);
        });
    });
    
    // Select all checkbox
    document.getElementById('selectAllCheckbox').addEventListener('change', function() {
        toggleSelectAll(this.checked);
    });
    
    // Search functionality
    document.getElementById('userSearch').addEventListener('input', function() {
        performSearch(this.value);
    });
    
    // Add user form
    document.getElementById('addUserForm').addEventListener('submit', handleAddUser);
}

function setupFilters() {
    // Filters are already set up in HTML
    applyFilters();
}

function setupSearch() {
    const searchInput = document.getElementById('userSearch');
    searchInput.addEventListener('input', debounce(function() {
        performSearch(this.value);
    }, 300));
}

function performSearch(searchTerm) {
    if (!searchTerm.trim()) {
        filteredUsers = [...userData];
    } else {
        const term = searchTerm.toLowerCase();
        filteredUsers = userData.filter(user => 
            user.firstName.toLowerCase().includes(term) ||
            user.lastName.toLowerCase().includes(term) ||
            user.email.toLowerCase().includes(term) ||
            user.id.toLowerCase().includes(term) ||
            user.phone.includes(term)
        );
    }
    
    currentPage = 1;
    populateUsersTable();
    updatePagination();
}

function applyFilters() {
    const statusFilter = document.getElementById('statusFilter').value;
    const typeFilter = document.getElementById('typeFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    
    filteredUsers = userData.filter(user => {
        // Status filter
        if (statusFilter !== 'all' && user.status !== statusFilter) {
            return false;
        }
        
        // Type filter
        if (typeFilter !== 'all' && user.accountType !== typeFilter) {
            return false;
        }
        
        // Date filter
        if (dateFilter !== 'all') {
            const userDate = new Date(user.registrationDate);
            const now = new Date();
            
            switch (dateFilter) {
                case 'today':
                    if (userDate.toDateString() !== now.toDateString()) return false;
                    break;
                case 'week':
                    const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                    if (userDate < weekAgo) return false;
                    break;
                case 'month':
                    if (userDate.getMonth() !== now.getMonth() || userDate.getFullYear() !== now.getFullYear()) return false;
                    break;
                case 'year':
                    if (userDate.getFullYear() !== now.getFullYear()) return false;
                    break;
            }
        }
        
        return true;
    });
    
    currentPage = 1;
    populateUsersTable();
    updatePagination();
}

function applySorting() {
    const sortBy = document.getElementById('sortBy').value;
    
    filteredUsers.sort((a, b) => {
        switch (sortBy) {
            case 'name':
                return (a.firstName + ' ' + a.lastName).localeCompare(b.firstName + ' ' + b.lastName);
            case 'email':
                return a.email.localeCompare(b.email);
            case 'date':
                return new Date(b.registrationDate) - new Date(a.registrationDate);
            case 'status':
                return a.status.localeCompare(b.status);
            case 'type':
                return a.accountType.localeCompare(b.accountType);
            default:
                return 0;
        }
    });
    
    populateUsersTable();
}

function clearFilters() {
    document.getElementById('statusFilter').value = 'all';
    document.getElementById('typeFilter').value = 'all';
    document.getElementById('dateFilter').value = 'all';
    document.getElementById('userSearch').value = '';
    
    filteredUsers = [...userData];
    currentPage = 1;
    populateUsersTable();
    updatePagination();
    
    showToast('Filters cleared', 'info');
}

function populateUsersTable() {
    const tbody = document.getElementById('usersTableBody');
    const startIndex = (currentPage - 1) * usersPerPage;
    const endIndex = startIndex + usersPerPage;
    const pageUsers = filteredUsers.slice(startIndex, endIndex);
    
    tbody.innerHTML = '';
    
    pageUsers.forEach(user => {
        const row = createUserRow(user);
        tbody.appendChild(row);
    });
    
    updateDisplayInfo();
}

function createUserRow(user) {
    const row = document.createElement('tr');
    row.className = 'user-row';
    row.dataset.userId = user.id;
    
    row.innerHTML = `
        <td>
            <input type="checkbox" class="user-checkbox" data-user-id="${user.id}">
        </td>
        <td>
            <div class="user-info">
                <img src="${user.avatar}" alt="${user.firstName} ${user.lastName}" class="user-avatar">
                <div class="user-details">
                    <div class="user-name">${user.firstName} ${user.lastName}</div>
                    <div class="user-id">ID: ${user.id}</div>
                </div>
            </div>
        </td>
        <td>${user.email}</td>
        <td>
            <span class="account-type ${user.accountType}">${formatAccountType(user.accountType)}</span>
        </td>
        <td>
            <span class="status-badge ${user.status}">${formatStatus(user.status)}</span>
        </td>
        <td>${formatDate(user.registrationDate)}</td>
        <td>${user.lastLogin === 'Never' ? 'Never' : formatDate(user.lastLogin)}</td>
        <td>
            <div class="action-buttons">
                <button class="action-btn view" onclick="viewUser('${user.id}')" title="View Details">
                    <i class="ri-eye-line"></i>
                </button>
                <button class="action-btn edit" onclick="editUser('${user.id}')" title="Edit User">
                    <i class="ri-edit-line"></i>
                </button>
                <button class="action-btn ${user.status === 'suspended' ? 'activate' : 'suspend'}" 
                        onclick="${user.status === 'suspended' ? 'activateUser' : 'suspendUser'}('${user.id}')" 
                        title="${user.status === 'suspended' ? 'Activate User' : 'Suspend User'}">
                    <i class="ri-${user.status === 'suspended' ? 'play' : 'pause'}-line"></i>
                </button>
                <button class="action-btn delete" onclick="deleteUser('${user.id}')" title="Delete User">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </td>
    `;
    
    return row;
}

function formatAccountType(type) {
    const types = {
        'personal': 'Personal',
        'business': 'Business',
        'premium': 'Premium'
    };
    return types[type] || type;
}

function formatStatus(status) {
    const statuses = {
        'active': 'Active',
        'inactive': 'Inactive',
        'suspended': 'Suspended',
        'pending': 'Pending'
    };
    return statuses[status] || status;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function updateDisplayInfo() {
    const startIndex = (currentPage - 1) * usersPerPage + 1;
    const endIndex = Math.min(currentPage * usersPerPage, filteredUsers.length);
    
    document.getElementById('showingStart').textContent = startIndex;
    document.getElementById('showingEnd').textContent = endIndex;
    document.getElementById('totalUsers').textContent = filteredUsers.length;
}

function updatePagination() {
    const totalPages = Math.ceil(filteredUsers.length / usersPerPage);
    const paginationNumbers = document.getElementById('paginationNumbers');
    
    paginationNumbers.innerHTML = '';
    
    // Add page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            const pageBtn = document.createElement('button');
            pageBtn.className = `pagination-btn ${i === currentPage ? 'active' : ''}`;
            pageBtn.textContent = i;
            pageBtn.onclick = () => goToPage(i);
            paginationNumbers.appendChild(pageBtn);
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            const ellipsis = document.createElement('span');
            ellipsis.className = 'pagination-ellipsis';
            ellipsis.textContent = '...';
            paginationNumbers.appendChild(ellipsis);
        }
    }
}

function goToPage(page) {
    const totalPages = Math.ceil(filteredUsers.length / usersPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        populateUsersTable();
        updatePagination();
    }
}

function previousPage() {
    if (currentPage > 1) {
        goToPage(currentPage - 1);
    }
}

function nextPage() {
    const totalPages = Math.ceil(filteredUsers.length / usersPerPage);
    if (currentPage < totalPages) {
        goToPage(currentPage + 1);
    }
}

function toggleSelectAll(checked) {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
        const userId = checkbox.dataset.userId;
        if (checked) {
            selectedUsers.add(userId);
        } else {
            selectedUsers.delete(userId);
        }
    });
    
    updateBulkActionButton();
}

function selectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    selectAllCheckbox.checked = !selectAllCheckbox.checked;
    toggleSelectAll(selectAllCheckbox.checked);
}

function updateBulkActionButton() {
    const bulkActionBtn = document.querySelector('button[onclick="bulkAction()"]');
    if (selectedUsers.size > 0) {
        bulkActionBtn.textContent = `Bulk Actions (${selectedUsers.size})`;
        bulkActionBtn.classList.add('has-selection');
    } else {
        bulkActionBtn.textContent = 'Bulk Actions';
        bulkActionBtn.classList.remove('has-selection');
    }
}

function bulkAction() {
    if (selectedUsers.size === 0) {
        showToast('Please select users first', 'warning');
        return;
    }
    
    showConfirmation(
        'Bulk Actions',
        `Perform actions on ${selectedUsers.size} selected users?`,
        () => {
            showToast(`Bulk action performed on ${selectedUsers.size} users`, 'success');
            selectedUsers.clear();
            updateBulkActionButton();
            document.getElementById('selectAllCheckbox').checked = false;
            document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
        }
    );
}

// User Actions
function viewUser(userId) {
    const user = userData.find(u => u.id === userId);
    if (!user) return;
    
    const modal = document.getElementById('userDetailsModal');
    const content = document.getElementById('userDetailsContent');
    
    content.innerHTML = `
        <div class="user-profile">
            <div class="profile-header">
                <img src="${user.avatar}" alt="${user.firstName} ${user.lastName}" class="profile-avatar">
                <div class="profile-info">
                    <h2>${user.firstName} ${user.lastName}</h2>
                    <p class="user-id">User ID: ${user.id}</p>
                    <span class="status-badge ${user.status}">${formatStatus(user.status)}</span>
                </div>
            </div>
            
            <div class="profile-details">
                <div class="detail-section">
                    <h3>Contact Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Email:</label>
                            <span>${user.email}</span>
                        </div>
                        <div class="detail-item">
                            <label>Phone:</label>
                            <span>${user.phone}</span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Account Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Account Type:</label>
                            <span class="account-type ${user.accountType}">${formatAccountType(user.accountType)}</span>
                        </div>
                        <div class="detail-item">
                            <label>Balance:</label>
                            <span class="balance">$${user.balance.toLocaleString()}</span>
                        </div>
                        <div class="detail-item">
                            <label>Registration Date:</label>
                            <span>${formatDate(user.registrationDate)}</span>
                        </div>
                        <div class="detail-item">
                            <label>Last Login:</label>
                            <span>${user.lastLogin === 'Never' ? 'Never' : formatDate(user.lastLogin)}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="profile-actions">
                <button class="btn-secondary" onclick="editUser('${user.id}')">
                    <i class="ri-edit-line"></i>
                    Edit User
                </button>
                <button class="btn-secondary" onclick="viewTransactions('${user.id}')">
                    <i class="ri-exchange-line"></i>
                    View Transactions
                </button>
                <button class="btn-primary" onclick="sendMessage('${user.id}')">
                    <i class="ri-mail-line"></i>
                    Send Message
                </button>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function editUser(userId) {
    showToast(`Opening edit form for user ${userId}`, 'info');
    // Implementation would open edit modal
}

function suspendUser(userId) {
    showConfirmation(
        'Suspend User',
        'Are you sure you want to suspend this user?',
        () => {
            // Update user status in data
            const user = userData.find(u => u.id === userId);
            if (user) {
                user.status = 'suspended';
                populateUsersTable();
                showToast(`User ${userId} suspended`, 'warning');
            }
        }
    );
}

function activateUser(userId) {
    showConfirmation(
        'Activate User',
        'Are you sure you want to activate this user?',
        () => {
            // Update user status in data
            const user = userData.find(u => u.id === userId);
            if (user) {
                user.status = 'active';
                populateUsersTable();
                showToast(`User ${userId} activated`, 'success');
            }
        }
    );
}

function deleteUser(userId) {
    showConfirmation(
        'Delete User',
        'Are you sure you want to permanently delete this user? This action cannot be undone.',
        () => {
            // Remove user from data
            const index = userData.findIndex(u => u.id === userId);
            if (index !== -1) {
                userData.splice(index, 1);
                filteredUsers = filteredUsers.filter(u => u.id !== userId);
                populateUsersTable();
                updatePagination();
                showToast(`User ${userId} deleted`, 'success');
            }
        }
    );
}

function viewTransactions(userId) {
    showToast(`Loading transactions for user ${userId}`, 'info');
    // Implementation would redirect to transactions page with user filter
}

function sendMessage(userId) {
    showToast(`Opening message composer for user ${userId}`, 'info');
    // Implementation would open message modal
}

// Modal functions
function openAddUserModal() {
    const modal = document.getElementById('addUserModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeAddUserModal() {
    const modal = document.getElementById('addUserModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('addUserForm').reset();
}

function closeUserDetailsModal() {
    const modal = document.getElementById('userDetailsModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function handleAddUser(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const userData = {
        id: 'USR' + String(Math.floor(Math.random() * 9000) + 1000),
        firstName: formData.get('firstName'),
        lastName: formData.get('lastName'),
        email: formData.get('email'),
        phone: formData.get('phone') || 'Not provided',
        accountType: formData.get('accountType'),
        status: formData.get('requireVerification') ? 'pending' : 'active',
        registrationDate: new Date().toISOString().split('T')[0],
        lastLogin: 'Never',
        balance: parseFloat(formData.get('initialDeposit')) || 0,
        avatar: `https://i.pravatar.cc/150?img=${Math.floor(Math.random() * 50) + 1}`
    };
    
    // Add user to data
    window.userData.unshift(userData);
    filteredUsers.unshift(userData);
    
    closeAddUserModal();
    populateUsersTable();
    updatePagination();
    
    showToast(`User ${userData.firstName} ${userData.lastName} created successfully`, 'success');
    
    // Send welcome email if checked
    if (formData.get('sendWelcomeEmail')) {
        setTimeout(() => {
            showToast('Welcome email sent', 'info');
        }, 1000);
    }
}

function exportUserData() {
    showToast('Preparing user data export...', 'info');
    
    setTimeout(() => {
        const csvContent = generateUserCSV();
        downloadCSV(csvContent, 'nexo-users-export.csv');
        showToast('User data exported successfully', 'success');
    }, 1500);
}

function generateUserCSV() {
    const headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Account Type', 'Status', 'Registration Date', 'Last Login', 'Balance'];
    const csvRows = [headers.join(',')];
    
    filteredUsers.forEach(user => {
        const row = [
            user.id,
            user.firstName,
            user.lastName,
            user.email,
            user.phone,
            user.accountType,
            user.status,
            user.registrationDate,
            user.lastLogin,
            user.balance
        ];
        csvRows.push(row.join(','));
    });
    
    return csvRows.join('\n');
}

function downloadCSV(content, filename) {
    const blob = new Blob([content], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
}

function toggleView(view) {
    // Implementation for grid/table view toggle
    showToast(`Switched to ${view} view`, 'info');
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Make userData available globally for testing
window.userData = userData;
