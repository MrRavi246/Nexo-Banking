// Pay Bills Page JavaScript - Backend Integrated

document.addEventListener('DOMContentLoaded', function() {
    initializePayBillsPage();
});

async function initializePayBillsPage() {
    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('payDate').value = today;
    
    // Setup form event listeners
    setupFormValidation();
    updateRecurringSummary();
    
    // Load bill data from backend
    await loadBillData();
}

async function loadBillData() {
    try {
        const response = await fetch('/Nexo-Banking/backend/get_bill_data.php', {
            method: 'GET',
            credentials: 'same-origin'
        });
        
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            
            // Update summary stats
            updateSummaryStats(data.summary);
            
            // Populate saved billers dropdown
            populateBillersDropdown(data.savedBillers);
            
            // Display upcoming bills
            displayUpcomingBills(data.upcomingBills);
            
            // Display recent payments
            displayRecentPayments(data.recentPayments);
            
        } else {
            showNotification('Failed to load bill data: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error loading bill data:', error);
        showNotification('Error loading bill data', 'error');
    }
}

function updateSummaryStats(summary) {
    document.getElementById('totalDueAmount').textContent = '$' + parseFloat(summary.totalDue || 0).toFixed(2);
    
    const billsText = summary.billsDueCount === 1 ? '1 bill' : summary.billsDueCount + ' bills';
    
    if (summary.nextDueDate) {
        const daysUntil = Math.ceil((new Date(summary.nextDueDate) - new Date()) / (1000 * 60 * 60 * 24));
        const daysText = daysUntil === 1 ? 'tomorrow' : `in ${daysUntil} days`;
        document.getElementById('totalDueText').textContent = `${billsText} • next due ${daysText}`;
        
        document.getElementById('nextPaymentInfo').textContent = 
            `${summary.nextDueBiller} • $${parseFloat(summary.nextDueAmount).toFixed(2)}`;
        document.getElementById('nextPaymentDate').textContent = `Due ${daysText}`;
    } else {
        document.getElementById('totalDueText').textContent = 'No upcoming bills';
        document.getElementById('nextPaymentInfo').textContent = 'No bills due';
        document.getElementById('nextPaymentDate').textContent = '--';
    }
}

function populateBillersDropdown(billers) {
    const select = document.getElementById('payeeSelect');
    
    // Keep the default options
    const defaultOptions = select.querySelectorAll('option[value=""], option[value="custom"]');
    select.innerHTML = '';
    defaultOptions.forEach(opt => select.appendChild(opt));
    
    // Add saved billers
    if (billers && billers.length > 0) {
        billers.forEach(biller => {
            const option = document.createElement('option');
            option.value = biller.biller_name;
            option.textContent = biller.biller_name;
            option.dataset.billType = biller.bill_type;
            select.insertBefore(option, select.querySelector('option[value="custom"]'));
        });
    }
}

function displayUpcomingBills(bills) {
    const container = document.getElementById('upcomingBillsList');
    
    if (!bills || bills.length === 0) {
        container.innerHTML = `
            <div style="padding: 2rem; text-align: center; color: #888;">
                <i class="ri-file-list-line" style="font-size: 2rem;"></i>
                <p>No upcoming bills</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '';
    
    bills.forEach(bill => {
        const daysUntil = bill.days_until_due;
        const dueText = daysUntil === 0 ? 'Due today' : 
                        daysUntil === 1 ? 'Due tomorrow' : 
                        `Due in ${daysUntil} days`;
        
        const billItem = document.createElement('div');
        billItem.className = 'bill-item';
        billItem.innerHTML = `
            <div class="bill-left">
                <h4>${bill.biller_name}</h4>
                <p>${bill.bill_type.replace('_', ' ')} • ${dueText}</p>
            </div>
            <div class="bill-right">
                <span class="bill-amount">$${parseFloat(bill.amount).toFixed(2)}</span>
                <div class="bill-action">
                    <button class="btn ${daysUntil <= 7 ? 'primary' : ''}" 
                            onclick="quickPayBill('${bill.biller_name}', ${bill.amount}, '${bill.bill_type}')">
                        ${daysUntil <= 7 ? 'Pay Now' : 'Pay'}
                    </button>
                </div>
            </div>
        `;
        container.appendChild(billItem);
    });
}

function displayRecentPayments(payments) {
    const container = document.getElementById('recentPaymentsList');
    
    if (!payments || payments.length === 0) {
        container.innerHTML = `
            <div style="padding: 1rem; text-align: center; color: #888;">
                <p>No recent payments</p>
            </div>
        `;
        
        document.getElementById('recentPaymentsCount').textContent = '0';
        return;
    }
    
    container.innerHTML = '';
    
    // Count payments in last 30 days
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
    const recentCount = payments.filter(p => new Date(p.payment_date) >= thirtyDaysAgo).length;
    document.getElementById('recentPaymentsCount').textContent = recentCount;
    
    payments.slice(0, 5).forEach(payment => {
        const paymentDate = new Date(payment.payment_date);
        const timeAgo = getTimeAgo(paymentDate);
        
        const statusBadge = payment.status === 'scheduled' ? 
            `<span style="background: #eb7ef2; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; margin-left: 0.5rem;">Scheduled</span>` : '';
        
        const paymentItem = document.createElement('div');
        paymentItem.className = 'payment-item';
        paymentItem.innerHTML = `
            <div class="payment-left">
                ${payment.biller_name}${statusBadge}
                <div class="muted">${timeAgo}</div>
            </div>
            <div class="payment-right">$${parseFloat(payment.amount).toFixed(2)}</div>
        `;
        container.appendChild(paymentItem);
    });
}

function quickPayBill(billerName, amount, billType) {
    document.getElementById('payeeSelect').value = billerName;
    document.getElementById('payFormAmount').value = amount;
    document.getElementById('billType').value = billType || 'utilities';
    document.getElementById('payFormAmount').focus();
}

function getTimeAgo(date) {
    const seconds = Math.floor((new Date() - date) / 1000);
    
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + ' minutes ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
    if (seconds < 2592000) return Math.floor(seconds / 86400) + ' days ago';
    return Math.floor(seconds / 2592000) + ' months ago';
}

function handlePayeeChange() {
    const payeeSelect = document.getElementById('payeeSelect');
    const customPayeeGroup = document.getElementById('customPayeeGroup');
    
    if (payeeSelect.value === 'custom') {
        customPayeeGroup.style.display = 'block';
        document.getElementById('customPayee').focus();
    } else {
        customPayeeGroup.style.display = 'none';
        document.getElementById('customPayee').value = '';
    }
}

function handlePaymentTypeChange() {
    const paymentType = document.getElementById('paymentType');
    const recurringOptions = document.getElementById('recurringOptions');
    
    if (paymentType.value === 'recurring') {
        recurringOptions.style.display = 'block';
        updateRecurringSummary();
    } else {
        recurringOptions.style.display = 'none';
    }
}

function updateRecurringSummary() {
    const frequency = document.getElementById('frequency').value;
    const startDate = document.getElementById('payDate').value;
    const endDate = document.getElementById('endDate').value;
    const amount = document.getElementById('payFormAmount').value;
    const summaryDiv = document.getElementById('recurringSummary');
    
    if (!startDate || !amount) {
        summaryDiv.innerHTML = '';
        return;
    }
    
    let frequencyText = '';
    let nextPayments = [];
    
    switch(frequency) {
        case 'daily':
            frequencyText = 'daily';
            nextPayments = getNextPaymentDates(startDate, 1, 3);
            break;
        case 'weekly':
            frequencyText = 'weekly';
            nextPayments = getNextPaymentDates(startDate, 7, 3);
            break;
        case 'bi-weekly':
            frequencyText = 'bi-weekly';
            nextPayments = getNextPaymentDates(startDate, 14, 3);
            break;
        case 'monthly':
            frequencyText = 'monthly';
            nextPayments = getNextMonthlyDates(startDate, 3);
            break;
        case 'quarterly':
            frequencyText = 'quarterly';
            nextPayments = getNextMonthlyDates(startDate, 3, 3);
            break;
        case 'yearly':
            frequencyText = 'yearly';
            nextPayments = getNextYearlyDates(startDate, 3);
            break;
    }
    
    const nextDatesText = nextPayments.map(date => new Date(date).toLocaleDateString()).join(', ');
    summaryDiv.innerHTML = `
        <i class="ri-information-line"></i> 
        This will charge $${amount} ${frequencyText}. 
        Next payments: ${nextDatesText}
        ${endDate ? ` (until ${new Date(endDate).toLocaleDateString()})` : ''}
    `;
}

function getNextPaymentDates(startDate, dayInterval, count) {
    const dates = [];
    const start = new Date(startDate);
    
    for (let i = 0; i < count; i++) {
        const nextDate = new Date(start);
        nextDate.setDate(start.getDate() + (dayInterval * (i + 1)));
        dates.push(nextDate.toISOString().split('T')[0]);
    }
    
    return dates;
}

function getNextMonthlyDates(startDate, count, monthInterval = 1) {
    const dates = [];
    const start = new Date(startDate);
    
    for (let i = 0; i < count; i++) {
        const nextDate = new Date(start);
        nextDate.setMonth(start.getMonth() + (monthInterval * (i + 1)));
        dates.push(nextDate.toISOString().split('T')[0]);
    }
    
    return dates;
}

function getNextYearlyDates(startDate, count) {
    const dates = [];
    const start = new Date(startDate);
    
    for (let i = 0; i < count; i++) {
        const nextDate = new Date(start);
        nextDate.setFullYear(start.getFullYear() + (i + 1));
        dates.push(nextDate.toISOString().split('T')[0]);
    }
    
    return dates;
}

function setupFormValidation() {
    const form = document.getElementById('payForm');
    const frequencySelect = document.getElementById('frequency');
    const payDateInput = document.getElementById('payDate');
    const amountInput = document.getElementById('payFormAmount');
    
    // Update summary when values change
    [frequencySelect, payDateInput, amountInput].forEach(element => {
        element.addEventListener('change', updateRecurringSummary);
        element.addEventListener('input', updateRecurringSummary);
    });
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        processPayment();
    });
}

function processPayment() {
    const fromAccount = document.getElementById('fromAccount').value;
    const payee = getSelectedPayee();
    const billType = document.getElementById('billType').value;
    const amount = document.getElementById('payFormAmount').value;
    const date = document.getElementById('payDate').value;
    const memo = document.getElementById('payMemo').value;
    const paymentType = document.getElementById('paymentType').value;
    const frequency = document.getElementById('frequency').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!fromAccount) {
        showNotification('Please select an account', 'error');
        return;
    }
    
    if (!payee || !amount || !date) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }
    
    // Validate amount
    if (parseFloat(amount) <= 0) {
        showNotification('Amount must be greater than 0', 'error');
        return;
    }
    
    // Show processing message
    showNotification('Processing payment...', 'info');
    
    // Prepare payment data
    const paymentData = {
        accountId: parseInt(fromAccount),
        billerName: payee,
        billType: billType,
        amount: parseFloat(amount),
        paymentDate: date,
        dueDate: date,
        memo: memo,
        paymentType: paymentType,
        frequency: paymentType === 'recurring' ? frequency : null,
        endDate: paymentType === 'recurring' ? endDate : null
    };
    
    // Send to backend
    fetch('/Nexo-Banking/backend/process_bill_payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(paymentData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification(result.message, 'success');
            
            // Reset form
            resetPayForm();
            
            // Reload bill data
            setTimeout(() => {
                loadBillData();
            }, 500);
        } else {
            showNotification(result.message || 'Payment failed', 'error');
        }
    })
    .catch(error => {
        console.error('Payment error:', error);
        showNotification('Error processing payment', 'error');
    });
}

function getSelectedPayee() {
    const payeeSelect = document.getElementById('payeeSelect');
    const customPayee = document.getElementById('customPayee');
    
    if (payeeSelect.value === 'custom') {
        return customPayee.value.trim();
    }
    return payeeSelect.value;
}

function addToRecentPayments(paymentData) {
    const recentPaymentsList = document.getElementById('recentPaymentsList');
    const paymentItem = document.createElement('div');
    paymentItem.className = 'payment-item';
    
    const timeAgo = 'Just now';
    const frequencyBadge = paymentData.type === 'recurring' ? 
        `<span style="background: #eb7ef2; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; margin-left: 0.5rem;">${paymentData.frequency}</span>` : '';
    
    paymentItem.innerHTML = `
        <div class="payment-left">
            ${paymentData.payee}${frequencyBadge}
            <div class="muted">${timeAgo}</div>
        </div>
        <div class="payment-right">$${paymentData.amount.toFixed(2)}</div>
    `;
    
    // Insert at the beginning
    recentPaymentsList.insertBefore(paymentItem, recentPaymentsList.firstChild);
    
    // Keep only the latest 5 payments
    while (recentPaymentsList.children.length > 5) {
        recentPaymentsList.removeChild(recentPaymentsList.lastChild);
    }
}

function resetPayForm() {
    document.getElementById('payForm').reset();
    document.getElementById('customPayeeGroup').style.display = 'none';
    document.getElementById('recurringOptions').style.display = 'none';
    
    // Reset to today's date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('payDate').value = today;
    
    // Clear summary
    document.getElementById('recurringSummary').innerHTML = '';
}

function showAllBills() {
    showNotification('View All Bills feature - Frontend demo only', 'info');
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
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 4000);
}
