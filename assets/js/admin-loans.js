document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('applicationsContainer')) {
        loadApplications();
    }
});

async function loadApplications() {
    const status = document.getElementById('statusFilter').value;
    try {
        const res = await fetch(`/Nexo-Banking/backend/get_loan_applications.php?status=${encodeURIComponent(status)}`, {credentials: 'same-origin'});
        const json = await res.json();
        const container = document.getElementById('applicationsContainer');
        if (!json.success) {
            container.innerHTML = `<div class="error">${json.message}</div>`;
            return;
        }

        const apps = json.data.applications || [];
        if (apps.length === 0) {
            container.innerHTML = '<div class="muted">No applications found.</div>';
            return;
        }

        let html = '<table class="admin-table" style="width:100%;border-collapse:collapse;"><thead><tr><th>ID</th><th>User</th><th>Type</th><th>Principal</th><th>Term</th><th>Submitted</th><th>Actions</th></tr></thead><tbody>';
        for (const a of apps) {
            const name = a.user_name || (a.first_name ? a.first_name + (a.last_name ? ' ' + a.last_name : '') : a.user_id);
            html += `<tr><td>${a.loan_id}</td><td>${escapeHtml(name)}</td><td>${escapeHtml(a.loan_type)}</td><td>$${parseFloat(a.principal).toFixed(2)}</td><td>${a.term_months} mo</td><td>${a.created_at}</td><td>`;
            if (a.status === 'pending') {
                // Build account select if accounts are available
                let acctSelect = '';
                if (Array.isArray(a.accounts) && a.accounts.length > 0) {
                    acctSelect = '<select id="acct_select_' + a.loan_id + '">';
                    acctSelect += '<option value="">Select account</option>';
                    for (const acc of a.accounts) {
                        acctSelect += `<option value="${acc.account_id}">${escapeHtml(acc.account_type || '')} ${escapeHtml(acc.masked_number || '')} (Balance: $${parseFloat(acc.balance).toFixed(2)})</option>`;
                    }
                    acctSelect += '</select>';
                } else {
                    acctSelect = '<span class="muted">No accounts available</span>';
                }

                html += acctSelect + ' ' + `<button onclick="performAction(${a.loan_id}, 'approve')" class="btn primary">Approve</button> <button onclick="performAction(${a.loan_id}, 'reject')" class="btn danger">Reject</button>`;
            } else {
                html += `<span class="muted">${a.status}</span>`;
            }
            html += `</td></tr>`;
        }
        html += '</tbody></table>';
        container.innerHTML = html;

    } catch (err) {
        console.error('Error loading applications', err);
        document.getElementById('applicationsContainer').innerHTML = '<div class="error">Error loading applications</div>';
    }
}

async function performAction(loanId, action) {
    const note = prompt('Optional note for audit/notification (admin):', '');
    if (!confirm(`Are you sure you want to ${action} loan #${loanId}?`)) return;

    try {
        // If approving, read selected account id from the select element
        let disburse_account_id = null;
        if (action === 'approve') {
            const sel = document.getElementById('acct_select_' + loanId);
            if (sel) disburse_account_id = sel.value || null;
        }

        const body = {loan_id: loanId, action: action, note: note};
        if (disburse_account_id) body.disburse_account_id = parseInt(disburse_account_id);

        const res = await fetch('/Nexo-Banking/backend/approve_loan.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(body)
        });
        const json = await res.json();
        if (json.success) {
            alert('Action completed: ' + action);
            loadApplications();
        } else {
            alert('Failed: ' + json.message);
        }
    } catch (err) {
        console.error('Error performing action', err);
        alert('Error performing action');
    }
}

function escapeHtml(str) {
    if (!str && str !== 0) return '';
    return String(str).replace(/[&<>"'`]/g, function (s) {
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;',"`":'&#96;'})[s];
    });
}
