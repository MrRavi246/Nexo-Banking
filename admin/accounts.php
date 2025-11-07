<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/_header.php';
?>

    <section class="admin-content">
        <h1>Account Management</h1>
        <p>Manage user accounts: view, freeze, close or inspect account details.</p>

        <div class="admin-info" id="adminAccountsInfo">Loading accounts...</div>

        <div style="margin-top:12px; display:flex; gap:.5rem; align-items:center;">
            <input id="acctQ" type="text" placeholder="Search account number, user or email" style="flex:1;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.06);background:transparent;color:#fff">
            <select id="acctType" style="padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.06);background:transparent;color:#fff">
                <option value="">All Types</option>
                <option value="checking">Checking</option>
                <option value="savings">Savings</option>
                <option value="credit">Credit</option>
                <option value="loan">Loan</option>
            </select>
            <select id="acctStatus" style="padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.06);background:transparent;color:#fff">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="frozen">Frozen</option>
                <option value="closed">Closed</option>
            </select>
            <button id="acctFilterBtn" class="btn-primary">Filter</button>
        </div>

        <div id="accountsWrapper" style="overflow:auto;margin-top:12px">
            <table class="admin-table">
                <thead>
                    <tr><th>Account ID</th><th>Account No</th><th>User</th><th>Type</th><th>Balance</th><th>Status</th><th>Last Activity</th><th>Actions</th></tr>
                </thead>
                <tbody id="accountsTbody">
                    <tr><td colspan="8" style="color:#999;padding:12px">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <script>
        (function(){
            const qEl = document.getElementById('acctQ');
            const typeEl = document.getElementById('acctType');
            const statusEl = document.getElementById('acctStatus');
            const btn = document.getElementById('acctFilterBtn');
            const tbody = document.getElementById('accountsTbody');
            const info = document.getElementById('adminAccountsInfo');

            btn.addEventListener('click', function(e){
                e.preventDefault();
                loadAccounts();
            });

            async function loadAccounts(){
                info.textContent = 'Loading accounts...';
                try {
                    const q = encodeURIComponent(qEl.value || '');
                    const type = encodeURIComponent(typeEl.value || '');
                    const status = encodeURIComponent(statusEl.value || '');
                    const res = await fetch('/Nexo-Banking/backend/admin_get_accounts.php?q='+q+'&type='+type+'&status='+status+'&limit=500', {credentials: 'same-origin'});
                    const json = await res.json();
                    if (!json.success) {
                        info.textContent = json.message || 'Failed to load accounts';
                        return;
                    }
                    const rows = json.data.accounts || [];
                    if (rows.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="8" style="color:#999;padding:12px">No accounts found.</td></tr>';
                        info.textContent = 'No accounts found';
                        return;
                    }
                    info.style.display = 'none';
                    const frag = document.createDocumentFragment();
                    rows.forEach(r => {
                        const tr = document.createElement('tr');
                        const user = (r.first_name || '') + ' ' + (r.last_name || '');
                        const bal = Number(r.balance || 0).toFixed(2);
                        tr.innerHTML = `
                            <td>${escapeHtml(r.account_id)}</td>
                            <td>${escapeHtml(r.account_number)}</td>
                            <td>${escapeHtml(user)}<br/><small>${escapeHtml(r.email||'')}</small></td>
                            <td>${escapeHtml(r.account_type)}</td>
                            <td>$${Number(bal).toLocaleString()}</td>
                            <td>${escapeHtml(r.status)}</td>
                            <td>${escapeHtml(r.last_activity || r.created_at || '')}</td>
                            <td><button class="btn small" onclick="alert('Implement action: view/edit for account '+${escapeJs(r.account_id)})">View</button> <button class="btn small" onclick="alert('Implement action: freeze/close')">Actions</button></td>
                        `;
                        frag.appendChild(tr);
                    });
                    tbody.innerHTML = '';
                    tbody.appendChild(frag);
                } catch (err) {
                    console.error(err);
                    info.textContent = 'Error loading accounts';
                }
            }

            function escapeHtml(s){
                if (s === null || s === undefined) return '';
                return String(s).replace(/[&\\"'<>]/g, function(c){
                    return { '&':'&amp;', '"':'&quot;', "'":"&#39;", '<':'&lt;', '>':'&gt;', '\\':'\\\\' }[c];
                });
            }

            function escapeJs(s){
                if (s === null || s === undefined) return "''";
                return `'`+String(s).replace(/'/g, "\\'")+`'`;
            }

            // initial load
            loadAccounts();
        })();
    </script>

<?php include __DIR__ . '/_footer.php';
