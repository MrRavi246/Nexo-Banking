// Loans Page JavaScript - Frontend Only

document.addEventListener('DOMContentLoaded', function() {
    initializeLoansPage();
});

function initializeLoansPage() {
    setupFormValidation();
    setupLoanCalculator();
}

function setupFormValidation() {
    const form = document.getElementById('loanForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        processLoanApplication();
    });
}

function setupLoanCalculator() {
    const amountInput = document.getElementById('loanAmount');
    const termSelect = document.getElementById('loanTerm');
    
    [amountInput, termSelect].forEach(element => {
        element.addEventListener('change', calculatePayment);
        element.addEventListener('input', calculatePayment);
    });
}

function selectLoanType(type) {
    const loanTypeSelect = document.getElementById('loanType');
    loanTypeSelect.value = type;
    updateLoanDetails();
    
    // Scroll to application panel
    document.querySelector('.loan-application-panel').scrollIntoView({ 
        behavior: 'smooth', 
        block: 'start' 
    });
    
    // Focus on amount input
    setTimeout(() => {
        document.getElementById('loanAmount').focus();
    }, 500);
}

function updateLoanDetails() {
    const loanType = document.getElementById('loanType').value;
    const amountInput = document.getElementById('loanAmount');
    
    // Set default values and limits based on loan type
    switch(loanType) {
        case 'personal':
            amountInput.setAttribute('max', '50000');
            amountInput.setAttribute('placeholder', '5000');
            break;
        case 'auto':
            amountInput.setAttribute('max', '100000');
            amountInput.setAttribute('placeholder', '25000');
            break;
        case 'home':
            amountInput.setAttribute('max', '500000');
            amountInput.setAttribute('placeholder', '200000');
            break;
        case 'business':
            amountInput.setAttribute('max', '250000');
            amountInput.setAttribute('placeholder', '50000');
            break;
        default:
            amountInput.setAttribute('placeholder', '0');
    }
    
    calculatePayment();
}

function calculatePayment() {
    const loanType = document.getElementById('loanType').value;
    const amount = parseFloat(document.getElementById('loanAmount').value);
    const term = parseInt(document.getElementById('loanTerm').value);
    const calculator = document.getElementById('loanCalculator');
    
    if (!loanType || !amount || !term) {
        calculator.style.display = 'none';
        return;
    }
    
    // Get estimated APR based on loan type and credit score
    const apr = getEstimatedAPR(loanType, amount);
    const monthlyRate = apr / 100 / 12;
    
    // Calculate monthly payment using loan formula
    const monthlyPayment = amount * (monthlyRate * Math.pow(1 + monthlyRate, term)) / 
                          (Math.pow(1 + monthlyRate, term) - 1);
    
    const totalPayment = monthlyPayment * term;
    const totalInterest = totalPayment - amount;
    
    // Update calculator display
    document.getElementById('estimatedAPR').textContent = apr.toFixed(2) + '%';
    document.getElementById('monthlyPayment').textContent = '$' + monthlyPayment.toFixed(2);
    document.getElementById('totalInterest').textContent = '$' + totalInterest.toFixed(2);
    
    calculator.style.display = 'block';
}

function getEstimatedAPR(loanType, amount) {
    // Simulate APR calculation based on loan type and amount
    let baseRate;
    
    switch(loanType) {
        case 'personal':
            baseRate = amount > 25000 ? 6.5 : 8.2;
            break;
        case 'auto':
            baseRate = amount > 50000 ? 3.9 : 4.8;
            break;
        case 'home':
            baseRate = amount > 300000 ? 4.2 : 5.1;
            break;
        case 'business':
            baseRate = amount > 100000 ? 7.8 : 9.5;
            break;
        default:
            baseRate = 8.0;
    }
    
    // Add some randomness to simulate credit score impact
    const variation = (Math.random() - 0.5) * 2; // ±1%
    return Math.max(2.5, baseRate + variation);
}

function processLoanApplication() {
    const loanType = document.getElementById('loanType').value;
    const amount = document.getElementById('loanAmount').value;
    const term = document.getElementById('loanTerm').value;
    const purpose = document.getElementById('purpose').value;
    
    if (!loanType || !amount || !term) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }
    
    if (parseFloat(amount) < 1000) {
        showNotification('Minimum loan amount is $1,000', 'error');
        return;
    }
    
    // Simulate processing
    showNotification('Processing loan application...', 'info');
    
    setTimeout(() => {
        const applicationData = {
            type: getLoanTypeName(loanType),
            amount: parseFloat(amount),
            term: parseInt(term),
            purpose: purpose,
            status: 'pending',
            timestamp: new Date().toISOString(),
            apr: getEstimatedAPR(loanType, parseFloat(amount))
        };
        
        // Add to recent applications
        addToRecentApplications(applicationData);
        
        // Show success message
        showNotification(`Loan application for $${amount} submitted successfully! You'll receive a decision within 24-48 hours.`, 'success');
        
        // Reset form
        resetLoanForm();
    }, 2000);
}

function getLoanTypeName(type) {
    const types = {
        'personal': 'Personal Loan',
        'auto': 'Auto Loan',
        'home': 'Home Loan',
        'business': 'Business Loan'
    };
    return types[type] || type;
}

function addToRecentApplications(applicationData) {
    const recentApplicationsList = document.getElementById('recentApplicationsList');
    const applicationItem = document.createElement('div');
    applicationItem.className = 'application-item';
    
    const timeAgo = 'Just now';
    
    applicationItem.innerHTML = `
        <div class="application-left">
            <div class="application-type">${applicationData.type}</div>
            <div class="muted">${timeAgo}</div>
        </div>
        <div class="application-right">
            <div class="application-amount">$${applicationData.amount.toLocaleString()}</div>
            <div class="application-status ${applicationData.status}">Under Review</div>
        </div>
    `;
    
    // Insert at the beginning
    recentApplicationsList.insertBefore(applicationItem, recentApplicationsList.firstChild);
    
    // Keep only the latest 5 applications
    while (recentApplicationsList.children.length > 5) {
        recentApplicationsList.removeChild(recentApplicationsList.lastChild);
    }
}

function makePayment(loanName, amount) {
    showNotification(`Redirecting to payment for ${loanName} - $${amount}`, 'info');
    
    // Simulate redirect to payment page
    setTimeout(() => {
        showNotification('This would redirect to the payment processing page in a real application', 'info');
    }, 1500);
}

function resetLoanForm() {
    document.getElementById('loanForm').reset();
    document.getElementById('loanCalculator').style.display = 'none';
    
    // Reset placeholders
    const amountInput = document.getElementById('loanAmount');
    amountInput.setAttribute('placeholder', '0');
    amountInput.removeAttribute('max');
}

function showAllLoans() {
    showNotification('View All Loans feature - Frontend demo only', 'info');
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="ri-${type === 'success' ? 'check-circle' : type === 'error' ? 'error-warning' : 'information'}-line"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add notification styles if not already present
    if (!document.querySelector('style[data-notification-styles]')) {
        const style = document.createElement('style');
        style.setAttribute('data-notification-styles', 'true');
        style.textContent = `
            .notification {
                position: fixed;
                top: 90px;
                right: 20px;
                background: rgba(255,255,255,0.1);
                border: 1px solid rgba(255,255,255,0.2);
                border-radius: 8px;
                padding: 1rem 1.5rem;
                color: white;
                z-index: 1000;
                backdrop-filter: blur(10px);
                animation: slideIn 0.3s ease;
                max-width: 400px;
            }
            .notification.success { border-color: #7ef29b; background: rgba(126,242,155,0.1); }
            .notification.error { border-color: #ff7b88; background: rgba(255,123,136,0.1); }
            .notification.info { border-color: #eb7ef2; background: rgba(235,126,242,0.1); }
            .notification-content { display: flex; align-items: center; gap: 0.5rem; }
            @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Credit score simulation (could be called from other parts of the app)
function updateCreditScore() {
    const creditScoreElement = document.querySelector('.stat-card .value.success');
    if (creditScoreElement) {
        // Simulate small credit score changes
        const currentScore = parseInt(creditScoreElement.textContent);
        const change = Math.floor(Math.random() * 21) - 10; // ±10 points
        const newScore = Math.max(300, Math.min(850, currentScore + change));
        
        creditScoreElement.textContent = newScore;
        showNotification(`Credit score updated: ${newScore}`, 'info');
    }
}
