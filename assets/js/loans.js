// Loans Page JavaScript - Frontend Only

document.addEventListener('DOMContentLoaded', function() {
    initializeLoansPage();
});

function initializeLoansPage() {
    setupFormValidation();
    setupLoanCalculator();
    // Load real loans from backend if available
    fetchLoans();
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
    
    // Submit to backend
    showNotification('Submitting loan application...', 'info');

    (async () => {
        try {
            const payload = {
                loan_type: loanType,
                principal: parseFloat(amount),
                term_months: parseInt(term),
                purpose: purpose
            };

            const res = await fetch('/Nexo-Banking/backend/apply_loan.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });

            const result = await res.json();
            if (result.success) {
                showNotification('Loan application submitted. Status: ' + (result.data.status || 'pending'), 'success');
                // Refresh loans list
                fetchLoans();
                resetLoanForm();
            } else {
                showNotification('Failed to submit application: ' + result.message, 'error');
            }
        } catch (err) {
            console.error('Error submitting loan application', err);
            showNotification('Error submitting application', 'error');
        }
    })();
}

async function fetchLoans() {
    try {
        const res = await fetch('/Nexo-Banking/backend/get_loans.php', {credentials: 'same-origin'});
        const json = await res.json();
        if (json.success) {
            renderLoansList(json.data.loans || []);
        } else {
            console.warn('Could not load loans:', json.message);
        }
    } catch (err) {
        console.error('Error fetching loans:', err);
    }
}

function renderLoansList(loans) {
    const container = document.querySelector('.loans-list');
    if (!container) return;

    // Simple rendering: replace the first .loans-list content area
    const listSection = container.querySelector('.loans-list') || container;
    // Find the area that contains loan items
    const items = container.querySelectorAll('.loan-item');
    // If there are placeholder items, remove them
    items.forEach(it => it.remove());

    loans.forEach(loan => {
        const loanItem = document.createElement('div');
        loanItem.className = 'loan-item';
        loanItem.dataset.loanId = loan.loan_id;
        loanItem.dataset.outstanding = loan.outstanding;
        loanItem.dataset.principal = loan.principal;
        loanItem.innerHTML = `
            <div class="loan-left">
                <h4>${getLoanTypeName(loan.loan_type)}</h4>
                <p>${(loan.term_months || '') ? (loan.term_months + ' months remaining') : ''}</p>
                <div class="loan-progress">
                    <div class="progress-bar" style="background:#222; height:10px; border-radius:6px; overflow:hidden;">
                        <div class="progress-fill" style="width:${Math.min(100, ((loan.principal - loan.outstanding) / (loan.principal || 1)) * 100)}%; height:100%; background:linear-gradient(90deg,#ff7ef2,#7ef29b);"></div>
                    </div>
                </div>
            </div>
            <div class="loan-right">
                <div class="loan-balance">$${parseFloat(loan.outstanding).toFixed(2)}</div>
                <div class="loan-payment">$${(loan.monthly_payment || 0).toFixed(2)}/mo</div>
                <div class="loan-action">
                    <button class="btn primary loan-pay-btn" data-loan-id="${loan.loan_id}" data-monthly="${(loan.monthly_payment||0)}">Make Payment</button>
                </div>
            </div>
        `;
        container.appendChild(loanItem);
    });
}

// Listen for clicks on dynamically added Make Payment buttons
document.addEventListener('click', function(e) {
    const btn = e.target.closest && e.target.closest('.loan-pay-btn');
    if (btn) {
        const loanId = btn.dataset.loanId;
        const loanItem = document.querySelector(`.loan-item[data-loan-id="${loanId}"]`);
        const outstanding = loanItem ? loanItem.dataset.outstanding : null;
        const title = loanItem ? loanItem.querySelector('h4').textContent : ('Loan #' + loanId);
        openPaymentModal(loanId, outstanding, title);
    }
});

function openPaymentModal(loanId, outstanding, title) {
    const modal = document.getElementById('loanPaymentModal');
    if (!modal) return;
    document.getElementById('pmLoanTitle').textContent = title;
    document.getElementById('pmOutstanding').textContent = outstanding ? ('$' + parseFloat(outstanding).toFixed(2)) : 'N/A';
    // Prefill amount and wire "Pay full outstanding" checkbox behaviour
    const amountEl = document.getElementById('pmAmount');
    const fullCheckbox = document.getElementById('pmFullPayCheckbox');
    // default to pay full outstanding
    if (fullCheckbox) {
        fullCheckbox.checked = true;
        amountEl.value = outstanding ? parseFloat(outstanding).toFixed(2) : '';
        amountEl.disabled = true;
        // toggle behaviour
        fullCheckbox.onchange = function() {
            if (this.checked) {
                amountEl.value = outstanding ? parseFloat(outstanding).toFixed(2) : '';
                amountEl.disabled = true;
            } else {
                amountEl.disabled = false;
                amountEl.focus();
            }
        };
    } else {
        // fallback: still prefill
        amountEl.value = outstanding ? parseFloat(outstanding).toFixed(2) : '';
        amountEl.disabled = false;
    }
    document.getElementById('pmAccount').value = '';
    document.getElementById('pmProgressFill').style.width = '0%';
    document.getElementById('pmProgressText').textContent = 'Ready';
    modal.style.display = 'block';
    modal.dataset.loanId = loanId;

    // Hook buttons
    document.getElementById('closePaymentModal').onclick = closePaymentModal;
    document.getElementById('pmCancelBtn').onclick = closePaymentModal;
    document.getElementById('pmSubmitBtn').onclick = function() { submitPayment(loanId); };
}

function closePaymentModal() {
    const modal = document.getElementById('loanPaymentModal');
    if (!modal) return;
    modal.style.display = 'none';
}

async function submitPayment(loanId) {
    const amountEl = document.getElementById('pmAmount');
    const acctEl = document.getElementById('pmAccount');
    const progressFill = document.getElementById('pmProgressFill');
    const progressText = document.getElementById('pmProgressText');
    const submitBtn = document.getElementById('pmSubmitBtn');
    const cancelBtn = document.getElementById('pmCancelBtn');

    const amount = parseFloat(amountEl.value);
    const accountId = parseInt(acctEl.value, 10);
    if (!amount || amount <= 0) { showNotification('Enter a valid amount', 'error'); return; }
    if (!accountId || accountId <= 0) { showNotification('Select a valid source account', 'error'); return; }

    // Client-side check: ensure selected account belongs to user and has sufficient balance
    try {
        const accounts = window.userAccounts || [];
        const acct = accounts.find(a => parseInt(a.account_id,10) === accountId);
        if (!acct) {
            showNotification('Selected account not found in your accounts', 'error');
            document.getElementById('pmProgressText').textContent = 'Failed: Account not found locally';
            document.getElementById('pmProgressFill').style.width = '100%';
            return;
        }
        const bal = parseFloat(acct.balance || 0);
        if (bal < amount) {
            showNotification('Insufficient funds in selected account', 'error');
            document.getElementById('pmProgressText').textContent = 'Failed: Insufficient funds';
            document.getElementById('pmProgressFill').style.width = '100%';
            return;
        }
    } catch (e) {
        console.warn('Account validation failed', e);
    }

    // Disable buttons
    submitBtn.disabled = true; cancelBtn.disabled = true;

    // Start progress
    progressText.textContent = 'Initializing payment...';
    progressFill.style.width = '10%';

    try {
        // small delay to let UI update
        await new Promise(r => setTimeout(r, 350));
        progressText.textContent = 'Sending payment request...';
        progressFill.style.width = '40%';

        const res = await fetch('/Nexo-Banking/backend/pay_loan.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({loan_id: parseInt(loanId,10), amount: amount, account_id: accountId})
        });
        progressText.textContent = 'Processing...';
        progressFill.style.width = '70%';

        const json = await res.json();
        if (json.success) {
            progressFill.style.width = '100%';
            progressText.textContent = 'Payment completed';
            showNotification('Payment successful. New outstanding: $' + parseFloat(json.data.new_outstanding).toFixed(2), 'success');
            // refresh loans list after a short delay to show full bar
            setTimeout(() => { fetchLoans(); closePaymentModal(); }, 900);
        } else {
            progressFill.style.width = '100%';
            progressText.textContent = 'Failed: ' + json.message;
            showNotification('Payment failed: ' + json.message, 'error');
            submitBtn.disabled = false; cancelBtn.disabled = false;
        }
    } catch (err) {
        console.error('Payment error', err);
        progressText.textContent = 'Error during payment';
        showNotification('Error processing payment', 'error');
        submitBtn.disabled = false; cancelBtn.disabled = false;
        progressFill.style.width = '100%';
    }
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
