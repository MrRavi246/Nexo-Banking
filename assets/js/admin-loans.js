document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('applicationsContainer')) {
        loadApplications();
    }
});

async function loadApplications() {
    const status = document.getElementById('statusFilter').value;
    const container = document.getElementById('applicationsContainer');
    
    container.innerHTML = '<div class="loading-spinner"><i class="ri-loader-4-line"></i><p>Loading applications...</p></div>';
    
    try {
        const res = await fetch(`/Nexo-Banking/backend/get_loan_applications.php?status=${encodeURIComponent(status)}`, {credentials: 'same-origin'});
        const json = await res.json();
        
        if (!json.success) {
            container.innerHTML = `<div class="alert alert-error"><i class="ri-error-warning-line"></i> ${json.message}</div>`;
            return;
        }

        const apps = json.data.applications || [];
        if (apps.length === 0) {
            container.innerHTML = `<div class="empty-state">
                <i class="ri-file-list-line"></i>
                <h3>No ${status} applications found</h3>
                <p>There are currently no loan applications with status: ${status}</p>
            </div>`;
            return;
        }

        let html = '<div class="table-responsive"><table class="data-table"><thead><tr>';
        html += '<th>Loan ID</th><th>Applicant</th><th>Loan Type</th><th>Amount</th><th>Term</th><th>Monthly EMI</th><th>APR</th><th>Applied On</th>';
        
        if (status === 'pending') {
            html += '<th style="width: 300px;">Disburse To</th><th style="width: 200px;">Actions</th>';
        } else {
            html += '<th>Status</th>';
        }
        
        html += '</tr></thead><tbody>';
        
        for (const a of apps) {
            const name = a.user_name || (a.first_name ? a.first_name + (a.last_name ? ' ' + a.last_name : '') : 'User #' + a.user_id);
            const loanTypeFormatted = formatLoanType(a.loan_type);
            const dateFormatted = formatDate(a.created_at);
            const statusBadge = getStatusBadge(a.status);
            
            html += `<tr>
                <td><strong>#${a.loan_id}</strong></td>
                <td><div class="user-info"><i class="ri-user-line"></i> ${escapeHtml(name)}</div></td>
                <td><span class="loan-type-badge">${loanTypeFormatted}</span></td>
                <td><strong>₹${formatAmount(a.principal)}</strong></td>
                <td>${a.term_months} months</td>
                <td>₹${formatAmount(a.monthly_payment)}</td>
                <td>${parseFloat(a.apr).toFixed(2)}%</td>
                <td>${dateFormatted}</td>`;
            
            if (status === 'pending') {
                // Build account select if accounts are available
                let acctSelect = '';
                if (Array.isArray(a.accounts) && a.accounts.length > 0) {
                    acctSelect = '<select id="acct_select_' + a.loan_id + '" class="form-select" style="width: 100%;">';
                    acctSelect += '<option value="">Select disbursement account</option>';
                    for (const acc of a.accounts) {
                        const accType = escapeHtml(acc.account_type || '');
                        const accMasked = escapeHtml(acc.masked_number || '');
                        const accBalance = formatAmount(acc.balance);
                        acctSelect += `<option value="${acc.account_id}">${accType.toUpperCase()} ${accMasked} (Bal: ₹${accBalance})</option>`;
                    }
                    acctSelect += '</select>';
                } else {
                    acctSelect = '<span class="text-muted"><i class="ri-information-line"></i> No accounts</span>';
                }

                html += `<td>${acctSelect}</td>`;
                html += `<td class="actions-cell">
                    <button onclick="performAction(${a.loan_id}, 'approve', '${escapeHtml(name)}')" class="btn btn-success btn-sm">
                        <i class="ri-check-line"></i> Approve
                    </button>
                    <button onclick="performAction(${a.loan_id}, 'reject', '${escapeHtml(name)}')" class="btn btn-danger btn-sm">
                        <i class="ri-close-line"></i> Reject
                    </button>
                </td>`;
            } else {
                html += `<td>${statusBadge}</td>`;
            }
            html += `</tr>`;
        }
        html += '</tbody></table></div>';
        container.innerHTML = html;

    } catch (err) {
        console.error('Error loading applications', err);
        container.innerHTML = `<div class="alert alert-error"><i class="ri-error-warning-line"></i> Error loading applications. Please try again.</div>`;
    }
}

function formatLoanType(type) {
    const types = {
        'home': '🏠 Home Loan',
        'auto': '🚗 Auto Loan',
        'personal': '👤 Personal Loan',
        'business': '💼 Business Loan',
        'education': '🎓 Education Loan'
    };
    return types[type] || type.charAt(0).toUpperCase() + type.slice(1) + ' Loan';
}

function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge badge-warning"><i class="ri-time-line"></i> Pending</span>',
        'approved': '<span class="badge badge-success"><i class="ri-checkbox-circle-line"></i> Approved</span>',
        'rejected': '<span class="badge badge-danger"><i class="ri-close-circle-line"></i> Rejected</span>',
        'active': '<span class="badge badge-primary"><i class="ri-money-dollar-circle-line"></i> Active</span>',
        'paid': '<span class="badge badge-success"><i class="ri-check-double-line"></i> Paid Off</span>'
    };
    return badges[status] || `<span class="badge">${status}</span>`;
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-IN', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function formatAmount(amount) {
    if (!amount && amount !== 0) return '0.00';
    return parseFloat(amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

async function performAction(loanId, action, applicantName) {
    let disburse_account_id = null;
    
    if (action === 'approve') {
        const sel = document.getElementById('acct_select_' + loanId);
        if (sel) {
            disburse_account_id = sel.value || null;
            if (!disburse_account_id) {
                showNotification('Please select a disbursement account', 'warning');
                return;
            }
        } else {
            showNotification('No account available for disbursement', 'error');
            return;
        }
    }
    
    // Create confirmation modal
    const actionText = action === 'approve' ? 'Approve' : 'Reject';
    const actionColor = action === 'approve' ? 'success' : 'danger';
    
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="ri-${action === 'approve' ? 'checkbox-circle' : 'close-circle'}-line"></i> ${actionText} Loan Application</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <strong>${action}</strong> loan application #${loanId} for <strong>${applicantName}</strong>?</p>
                <div class="form-group">
                    <label for="adminNote">Admin Note (Optional):</label>
                    <textarea id="adminNote" class="form-control" rows="3" placeholder="Add a note for internal records..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">
                    <i class="ri-close-line"></i> Cancel
                </button>
                <button class="btn btn-${actionColor}" onclick="confirmAction(${loanId}, '${action}', ${disburse_account_id})">
                    <i class="ri-${action === 'approve' ? 'check' : 'close'}-line"></i> ${actionText}
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    setTimeout(() => modal.classList.add('active'), 10);
}

function closeModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => modal.remove(), 300);
    }
}

async function confirmAction(loanId, action, disburseAccountId) {
    const noteInput = document.getElementById('adminNote');
    const note = noteInput ? noteInput.value.trim() : '';
    
    closeModal();
    
    // Show loading indicator
    const loadingToast = showNotification(`Processing ${action}...`, 'info', 0);
    
    try {
        const body = {loan_id: loanId, action: action, note: note};
        if (disburseAccountId) body.disburse_account_id = parseInt(disburseAccountId);

        const res = await fetch('/Nexo-Banking/backend/approve_loan.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(body)
        });
        
        const json = await res.json();
        
        // Hide loading toast
        if (loadingToast) loadingToast.remove();
        
        if (json.success) {
            showNotification(`Loan application ${action === 'approve' ? 'approved' : 'rejected'} successfully!`, 'success');
            setTimeout(() => loadApplications(), 1000);
        } else {
            showNotification(`Failed: ${json.message}`, 'error');
        }
    } catch (err) {
        console.error('Error performing action', err);
        if (loadingToast) loadingToast.remove();
        showNotification('Error processing request. Please try again.', 'error');
    }
}

function showNotification(message, type = 'info', duration = 5000) {
    const icons = {
        success: 'ri-checkbox-circle-line',
        error: 'ri-error-warning-line',
        warning: 'ri-alert-line',
        info: 'ri-information-line'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="${icons[type]}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    
    if (duration > 0) {
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
    
    return toast;
}

function escapeHtml(str) {
    if (!str && str !== 0) return '';
    return String(str).replace(/[&<>"'`]/g, function (s) {
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;',"`":'&#96;'})[s];
    });
}
