// Transfer Money Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeTransferPage();
    loadTransferData(); // Load accounts and contacts from backend
    loadRecentTransfers(); // Load recent transfers from backend
});

function initializeTransferPage() {
    // Initialize event listeners
    setupTransferMethods();
    setupRecipientToggle();
    setupContactSelection();
    setupAmountSuggestions();
    setupSecurityMethods();
    setupFormValidation();
    setupFormCalculations();
    setupSidebarNavigation();
    
    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('transferDate').value = today;
}

// Load recent transfers from backend
async function loadRecentTransfers() {
    try {
        const response = await fetch('../../backend/get_recent_transfers.php', {
            method: 'GET',
            credentials: 'include'
        });

        const result = await response.json();

        if (result.success && result.data.transfers) {
            console.log('Recent transfers loaded:', result.data.transfers);
            updateRecentTransfersList(result.data.transfers);
        } else {
            console.error('Failed to load recent transfers:', result.message);
            showEmptyTransfersMessage();
        }
    } catch (error) {
        console.error('Error loading recent transfers:', error);
        showEmptyTransfersMessage();
    }
}

// Update recent transfers list with real data
function updateRecentTransfersList(transfers) {
    const transfersList = document.getElementById('recentTransfersList');
    
    if (!transfersList) return;
    
    if (transfers.length === 0) {
        showEmptyTransfersMessage();
        return;
    }
    
    transfersList.innerHTML = '';
    
    transfers.forEach((transfer, index) => {
        const transferItem = document.createElement('div');
        transferItem.className = 'transfer-item';
        
        // Determine avatar
        let avatarHtml = '';
        if (transfer.type === 'external' || transfer.type === 'wire') {
            avatarHtml = `
                <div class="transfer-avatar">
                    <div class="avatar-placeholder">
                        <i class="ri-building-line"></i>
                    </div>
                </div>
            `;
        } else {
            avatarHtml = `
                <div class="transfer-avatar">
                    <img src="https://i.pravatar.cc/40?img=${(index % 10) + 1}" alt="${transfer.name}">
                </div>
            `;
        }
        
        // Determine amount class
        const amountClass = transfer.is_sent ? 'sent' : 'received';
        const amountPrefix = transfer.is_sent ? '-' : '+';
        
        // Determine status
        const statusClass = transfer.status === 'completed' ? 'completed' : 'pending';
        const statusIcon = transfer.status === 'completed' ? 'ri-check-line' : 'ri-time-line';
        const statusText = transfer.status === 'completed' ? 'Completed' : 'Pending';
        
        transferItem.innerHTML = `
            ${avatarHtml}
            <div class="transfer-info">
                <h4>${transfer.name}</h4>
                <p>${transfer.time}</p>
            </div>
            <div class="transfer-amount ${amountClass}">
                ${amountPrefix}$${transfer.amount.toFixed(2)}
            </div>
            <div class="transfer-status ${statusClass}">
                <i class="${statusIcon}"></i> ${statusText}
            </div>
        `;
        
        transfersList.appendChild(transferItem);
    });
}

// Show empty message if no transfers
function showEmptyTransfersMessage() {
    const transfersList = document.getElementById('recentTransfersList');
    if (transfersList) {
        transfersList.innerHTML = `
            <div class="transfer-item" style="justify-content: center; color: #888; padding: 40px 20px;">
                <div style="text-align: center;">
                    <i class="ri-exchange-line" style="font-size: 48px; display: block; margin-bottom: 10px; opacity: 0.3;"></i>
                    <p>No transfers yet</p>
                    <p style="font-size: 12px; margin-top: 5px;">Your recent transfers will appear here</p>
                </div>
            </div>
        `;
    }
}

// Load transfer data from backend
async function loadTransferData() {
    try {
        const response = await fetch('../../backend/get_transfer_data.php', {
            method: 'GET',
            credentials: 'include'
        });

        const result = await response.json();

        if (result.success) {
            console.log('Transfer data loaded:', result.data);
            
            // Update contacts list if we got any
            if (result.data.contacts && result.data.contacts.length > 0) {
                updateContactsList(result.data.contacts);
            }
        } else {
            console.error('Failed to load transfer data:', result.message);
        }
    } catch (error) {
        console.error('Error loading transfer data:', error);
    }
}

// Update contacts list dynamically
function updateContactsList(contacts) {
    const contactsContainer = document.querySelector('.saved-contacts');
    
    if (!contactsContainer || contacts.length === 0) return;
    
    contactsContainer.innerHTML = '';
    
    contacts.forEach((contact, index) => {
        const contactItem = document.createElement('div');
        contactItem.className = 'contact-item';
        contactItem.dataset.contactId = contact.beneficiary_id;
        
        const avatarUrl = contact.email 
            ? `https://ui-avatars.com/api/?name=${encodeURIComponent(contact.beneficiary_name)}&background=random`
            : `https://i.pravatar.cc/50?img=${index + 1}`;
        
        contactItem.innerHTML = `
            <div class="contact-avatar">
                <img src="${avatarUrl}" alt="${contact.beneficiary_name}">
            </div>
            <div class="contact-info">
                <h4>${contact.beneficiary_name}</h4>
                <p>${contact.email || 'No email'}</p>
                <span class="contact-bank">${contact.bank_name || 'Nexo Banking'} ${contact.account_number ? '(**** ' + contact.account_number.slice(-4) + ')' : ''}</span>
            </div>
            <i class="ri-check-line contact-selected"></i>
        `;
        
        contactsContainer.appendChild(contactItem);
    });
    
    // Re-setup contact selection listeners
    setupContactSelection();
}

// Transfer Method Selection
function setupTransferMethods() {
    const methods = document.querySelectorAll('.transfer-method');
    
    methods.forEach(method => {
        method.addEventListener('click', function() {
            methods.forEach(m => m.classList.remove('active'));
            this.classList.add('active');
            updateTransferFee();
        });
    });
}

// Recipient Type Toggle
function setupRecipientToggle() {
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    const contactForm = document.querySelector('.contact-form');
    const newForm = document.querySelector('.new-form');
    
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const type = this.dataset.type;
            if (type === 'contact') {
                contactForm.style.display = 'block';
                newForm.style.display = 'none';
            } else {
                contactForm.style.display = 'none';
                newForm.style.display = 'block';
            }
        });
    });
}

// Contact Selection
function setupContactSelection() {
    const contactItems = document.querySelectorAll('.contact-item');
    const contactSearch = document.getElementById('contactSearch');
    
    contactItems.forEach(item => {
        item.addEventListener('click', function() {
            contactItems.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    
    // Contact search functionality
    contactSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        contactItems.forEach(item => {
            const name = item.querySelector('h4').textContent.toLowerCase();
            const email = item.querySelector('p').textContent.toLowerCase();
            
            if (name.includes(searchTerm) || email.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });
}

// Amount Suggestions
function setupAmountSuggestions() {
    const amountBtns = document.querySelectorAll('.amount-btn');
    const amountInput = document.getElementById('transferAmount');
    
    amountBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const amount = this.dataset.amount;
            amountInput.value = amount;
            updateSummary();
        });
    });
    
    // Update summary when amount changes
    amountInput.addEventListener('input', updateSummary);
}

// Security Method Selection
function setupSecurityMethods() {
    const methods = document.querySelectorAll('.security-method');
    
    methods.forEach(method => {
        method.addEventListener('click', function() {
            methods.forEach(m => m.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// Form Validation
function setupFormValidation() {
    const form = document.getElementById('transferForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            processTransfer();
        }
    });
}

function validateForm() {
    const fromAccount = document.getElementById('fromAccount').value;
    const transferAmount = document.getElementById('transferAmount').value;
    const transferDate = document.getElementById('transferDate').value;
    
    let isValid = true;
    let errors = [];
    
    if (!fromAccount) {
        errors.push('Please select a source account');
        isValid = false;
    }
    
    if (!transferAmount || parseFloat(transferAmount) <= 0) {
        errors.push('Please enter a valid transfer amount');
        isValid = false;
    }
    
    if (!transferDate) {
        errors.push('Please select a transfer date');
        isValid = false;
    }
    
    // Check if recipient is selected (for contact form) or filled (for new form)
    const isContactForm = document.querySelector('.contact-form').style.display !== 'none';
    
    if (isContactForm) {
        const selectedContact = document.querySelector('.contact-item.selected');
        if (!selectedContact) {
            errors.push('Please select a recipient');
            isValid = false;
        }
    } else {
        const recipientName = document.getElementById('recipientName').value;
        const accountNumber = document.getElementById('accountNumber').value;
        const routingNumber = document.getElementById('routingNumber').value;
        
        if (!recipientName || !accountNumber || !routingNumber) {
            errors.push('Please fill in all recipient details');
            isValid = false;
        }
    }
    
    if (!isValid) {
        showNotification('Please correct the following errors:\n' + errors.join('\n'), 'error');
    }
    
    return isValid;
}

// Form Calculations
function setupFormCalculations() {
    const amountInput = document.getElementById('transferAmount');
    amountInput.addEventListener('input', updateSummary);
}

function updateTransferFee() {
    const activeMethod = document.querySelector('.transfer-method.active');
    const methodType = activeMethod.dataset.method;
    
    let fee = 0;
    switch(methodType) {
        case 'internal':
            fee = 0;
            break;
        case 'external':
            fee = 2.99;
            break;
        case 'wire':
            fee = 15.00;
            break;
    }
    
    updateSummary();
}

function updateSummary() {
    const amount = parseFloat(document.getElementById('transferAmount').value) || 0;
    const activeMethod = document.querySelector('.transfer-method.active');
    const methodType = activeMethod.dataset.method;
    
    let fee = 0;
    switch(methodType) {
        case 'internal':
            fee = 0;
            break;
        case 'external':
            fee = 2.99;
            break;
        case 'wire':
            fee = 15.00;
            break;
    }
    
    const total = amount + fee;
    
    document.getElementById('summaryAmount').textContent = '$' + amount.toFixed(2);
    document.getElementById('summaryFee').textContent = '$' + fee.toFixed(2);
    document.getElementById('summaryTotal').textContent = '$' + total.toFixed(2);
}

// Process Transfer
async function processTransfer() {
    // Show loading state
    const submitBtn = document.querySelector('.btn.primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="ri-loader-4-line"></i> Processing...';
    submitBtn.disabled = true;
    
    try {
        // Gather form data
        const fromAccount = document.getElementById('fromAccount').value;
        const amount = parseFloat(document.getElementById('transferAmount').value);
        const transferDate = document.getElementById('transferDate').value;
        const memo = document.getElementById('transferMemo').value;
        
        // Get selected transfer method
        const activeMethod = document.querySelector('.transfer-method.active');
        const transferMethod = activeMethod.dataset.method;
        
        // Get recipient type and details
        const isContactForm = document.querySelector('.contact-form').style.display !== 'none';
        const recipientType = isContactForm ? 'contact' : 'new';
        
        let transferData = {
            fromAccount: fromAccount,
            amount: amount,
            transferDate: transferDate,
            memo: memo,
            transferMethod: transferMethod,
            recipientType: recipientType
        };
        
        // Add recipient details based on type
        if (recipientType === 'contact') {
            const selectedContact = document.querySelector('.contact-item.selected');
            if (selectedContact) {
                transferData.contactId = selectedContact.dataset.contactId;
            }
        } else {
            transferData.recipientName = document.getElementById('recipientName').value;
            transferData.recipientEmail = document.getElementById('recipientEmail').value;
            transferData.bankName = document.getElementById('bankName').value;
            transferData.accountNumber = document.getElementById('accountNumber').value;
            transferData.routingNumber = document.getElementById('routingNumber').value;
            transferData.accountType = document.getElementById('accountType').value;
        }
        
        // Get selected security method
        const activeSecurityMethod = document.querySelector('.security-method.active');
        transferData.securityMethod = activeSecurityMethod.dataset.method;
        
        console.log('Sending transfer data:', transferData);
        
        // Send to backend
        const response = await fetch('../../backend/process_transfer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify(transferData)
        });
        
        const result = await response.json();
        console.log('Transfer result:', result);
        
        if (result.success) {
            // Show success message
            showNotification('Transfer completed successfully! Amount: $' + result.data.amount.toFixed(2), 'success');
            
            // Add transfer to recent transfers list (prepend to top)
            addTransferToRecentList(result.data);
            
            // Update account balance in the dropdown
            updateAccountBalance(fromAccount, result.data.new_balance);
            
            // Reload recent transfers from server to get fresh data
            setTimeout(() => {
                loadRecentTransfers();
            }, 500);
            
            // If it was a new recipient, reload contacts to show the newly saved contact
            if (recipientType === 'new') {
                setTimeout(() => {
                    loadTransferData(); // This will reload contacts
                }, 500);
            }
            
            // Reset form
            setTimeout(() => {
                resetForm();
            }, 1500);
        } else {
            showNotification('Transfer failed: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error processing transfer:', error);
        showNotification('An error occurred while processing the transfer. Please try again.', 'error');
    } finally {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Update account balance in dropdown after transfer
function updateAccountBalance(accountId, newBalance) {
    const accountSelect = document.getElementById('fromAccount');
    const option = accountSelect.querySelector(`option[value="${accountId}"]`);
    
    if (option) {
        const text = option.textContent;
        const updatedText = text.replace(/\$[\d,]+\.\d{2}/, '$' + newBalance.toFixed(2));
        option.textContent = updatedText;
        option.dataset.balance = newBalance;
    }
}

// Add transfer to recent list
function addTransferToRecentList(transferData) {
    const transfersList = document.querySelector('.transfers-list');
    if (!transfersList) return;
    
    const transferItem = document.createElement('div');
    transferItem.className = 'transfer-item';
    
    const statusClass = transferData.status === 'completed' ? 'completed' : 'pending';
    const statusIcon = transferData.status === 'completed' ? 'ri-check-line' : 'ri-time-line';
    const statusText = transferData.status === 'completed' ? 'Completed' : 'Pending';
    
    transferItem.innerHTML = `
        <div class="transfer-avatar">
            <div class="avatar-placeholder">
                <i class="ri-send-plane-line"></i>
            </div>
        </div>
        <div class="transfer-info">
            <h4>${transferData.recipient}</h4>
            <p>Just now</p>
        </div>
        <div class="transfer-amount sent">
            -$${transferData.total.toFixed(2)}
        </div>
        <div class="transfer-status ${statusClass}">
            <i class="${statusIcon}"></i> ${statusText}
        </div>
    `;
    
    // Add to top of list
    transfersList.insertBefore(transferItem, transfersList.firstChild);
}

// Reset Form
function resetForm() {
    document.getElementById('transferForm').reset();
    
    // Reset selections
    document.querySelectorAll('.contact-item').forEach(item => {
        item.classList.remove('selected');
    });
    
    // Reset toggle to default
    document.querySelector('.toggle-btn[data-type="contact"]').click();
    
    // Reset transfer method to default
    document.querySelector('.transfer-method[data-method="internal"]').click();
    
    // Reset security method to default
    document.querySelector('.security-method[data-method="sms"]').click();
    
    // Reset date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('transferDate').value = today;
    
    // Reset summary
    updateSummary();
}

// Sidebar Navigation
function setupSidebarNavigation() {
    const menuItems = document.querySelectorAll('.sidebar .menu-item');
    
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            const text = this.querySelector('span').textContent.trim();
            navigateToPage(text);
        });
    });
}

function navigateToPage(page) {
    const pageMap = {
        'Dashboard': 'Dashboard.html',
        'Accounts': 'accounts.html',
        'Transactions': 'Transactions.html',
        'Transfer Money': 'transfer-money.html',
        'Pay Bills': '#',
        'Loans': '#',
        'Analytics': '#',
        'Support': '#',
        'Logout': '../../index.html'
    };
    
    if (pageMap[page] && pageMap[page] !== '#' && page !== 'Transfer Money') {
        window.location.href = pageMap[page];
    }
}

// Utility Functions
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="ri-${type === 'success' ? 'check-line' : type === 'error' ? 'error-warning-line' : 'information-line'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add styles if not already present
    if (!document.querySelector('#notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.innerHTML = `
            .notification {
                position: fixed;
                top: 90px;
                right: 20px;
                background: rgba(255,255,255,0.1);
                border: 1px solid rgba(255,255,255,0.2);
                border-radius: 12px;
                padding: 1rem;
                backdrop-filter: blur(10px);
                z-index: 10000;
                animation: slideIn 0.3s ease;
                max-width: 400px;
            }
            
            .notification.success {
                border-color: #4ce39a;
                background: rgba(75,227,154,0.1);
            }
            
            .notification.error {
                border-color: #ff7b88;
                background: rgba(255,123,136,0.1);
            }
            
            .notification-content {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: #ffffff;
            }
            
            .notification.success .notification-content i {
                color: #4ce39a;
            }
            
            .notification.error .notification-content i {
                color: #ff7b88;
            }
            
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(notification);
    
    // Remove notification after 5 seconds
    setTimeout(() => {
        notification.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Quick Actions (referenced in HTML)
function showTransferForm() {
    document.querySelector('.transfer-form-container').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
    });
}

function showRecentTransfers() {
    document.querySelector('.recent-transfers').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
    });
}
