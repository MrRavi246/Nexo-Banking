// Transfer Money Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeTransferPage();
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
function processTransfer() {
    // Show loading state
    const submitBtn = document.querySelector('.btn.primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="ri-loader-4-line"></i> Processing...';
    submitBtn.disabled = true;
    
    // Frontend-only simulation (no backend integration)
    setTimeout(() => {
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        // Show success message
        showNotification('Transfer simulation completed! This is a frontend-only demo.', 'success');
        
        // Add transfer to recent transfers list
        addTransferToRecentList();
        
        // Optionally reset form
        // resetForm();
        
    }, 2000);
}

// Add transfer to recent transfers list (frontend only)
function addTransferToRecentList() {
    const amount = document.getElementById('transferAmount').value;
    const selectedContact = document.querySelector('.contact-item.selected');
    
    if (amount && selectedContact) {
        const transfersList = document.querySelector('.transfers-list');
        const newTransfer = document.createElement('div');
        newTransfer.className = 'transfer-item';
        
        const contactName = selectedContact.querySelector('h4').textContent;
        const contactAvatar = selectedContact.querySelector('img').src;
        
        newTransfer.innerHTML = `
            <div class="transfer-avatar">
                <img src="${contactAvatar}" alt="${contactName}">
            </div>
            <div class="transfer-info">
                <h4>${contactName}</h4>
                <p>Just now</p>
            </div>
            <div class="transfer-amount sent">
                -$${parseFloat(amount).toFixed(2)}
            </div>
            <div class="transfer-status completed">
                <i class="ri-check-line"></i>
                Completed
            </div>
        `;
        
        // Add to top of list
        transfersList.insertBefore(newTransfer, transfersList.firstChild);
    }
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
