// Pay Bills Page JavaScript - Frontend Only

document.addEventListener('DOMContentLoaded', function() {
    initializePayBillsPage();
});

function initializePayBillsPage() {
    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('payDate').value = today;
    
    // Setup form event listeners
    setupFormValidation();
    updateRecurringSummary();
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
    const payee = getSelectedPayee();
    const amount = document.getElementById('payFormAmount').value;
    const date = document.getElementById('payDate').value;
    const memo = document.getElementById('payMemo').value;
    const paymentType = document.getElementById('paymentType').value;
    const frequency = document.getElementById('frequency').value;
    
    if (!payee || !amount || !date) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }
    
    // Simulate processing
    showNotification('Processing payment...', 'info');
    
    setTimeout(() => {
        const paymentData = {
            payee: payee,
            amount: parseFloat(amount),
            date: date,
            memo: memo,
            type: paymentType,
            frequency: paymentType === 'recurring' ? frequency : null,
            timestamp: new Date().toISOString()
        };
        
        // Add to recent payments
        addToRecentPayments(paymentData);
        
        // Show success message
        const frequencyText = paymentType === 'recurring' ? ` (${frequency})` : '';
        showNotification(`Payment of $${amount} to ${payee} scheduled successfully${frequencyText}!`, 'success');
        
        // Reset form
        resetPayForm();
    }, 1500);
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
