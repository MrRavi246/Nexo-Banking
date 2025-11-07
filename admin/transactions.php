<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Render admin header
include __DIR__ . '/_header.php';
?>

    <section class="admin-content">
        <h1>Transactions</h1>

        <form id="adminFilterForm" method="get" class="admin-filter" style="display:flex;gap:8px;align-items:center;margin-bottom:12px">
            <input id="filterQ" type="text" name="q" placeholder="Search by account, type, status or id" value="<?=htmlspecialchars($_GET['q'] ?? '')?>" style="flex:1;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.06);background:transparent;color:#fff">
            <select id="filterStatus" name="status" style="padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.06);background:transparent;color:#fff">
                <option value="">All Statuses</option>
                <option value="pending" <?= (isset($_GET['status']) && $_GET['status']=='pending')?'selected':''?>>Pending</option>
                <option value="completed" <?= (isset($_GET['status']) && $_GET['status']=='completed')?'selected':''?>>Completed</option>
                <option value="failed" <?= (isset($_GET['status']) && $_GET['status']=='failed')?'selected':''?>>Failed</option>
            </select>
            <button class="btn-primary">Filter</button>
        </form>

        <div id="adminInfo" class="admin-info" style="display:none"></div>

        <div id="transactionsWrapper">
            <div id="transactionsLoading" style="padding:12px;color:#ccc">Loading transactions…</div>
            <div style="overflow:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Account</th>
                        <th>Recipient</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="transactionsTbody">
                    <!-- rows injected by JS -->
                </tbody>
            </table>
            </div>
        </div>
    </section>

    <script>
        (function(){
            const form = document.getElementById('adminFilterForm');
            const qEl = document.getElementById('filterQ');
            const statusEl = document.getElementById('filterStatus');
            const tbody = document.getElementById('transactionsTbody');
            const loading = document.getElementById('transactionsLoading');
            const info = document.getElementById('adminInfo');

            form.addEventListener('submit', function(e){
                e.preventDefault();
                loadTransactions();
            });

            async function loadTransactions(){
                loading.style.display = '';
                info.style.display = 'none';
                tbody.innerHTML = '';
                const q = encodeURIComponent(qEl.value || '');
                const status = encodeURIComponent(statusEl.value || '');
                try {
                    const res = await fetch('/Nexo-Banking/backend/admin_get_transactions.php?q='+q+'&status='+status+'&limit=500', {credentials: 'same-origin'});
                    const json = await res.json();
                    loading.style.display = 'none';
                    if (!json.success) {
                        info.textContent = json.message || 'Failed to load transactions';
                        info.style.display = '';
                        return;
                    }
                    const rows = json.data.transactions || [];
                    if (rows.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" style="color:#999;padding:12px">No transactions found.</td></tr>';
                        return;
                    }
                    const fragment = document.createDocumentFragment();
                    rows.forEach(r => {
                        const tr = document.createElement('tr');
                        const date = r.transaction_date || r.created_at || '';
                        const amount = (Number(r.amount)||0).toFixed(2);
                        tr.innerHTML = `
                            <td>${escapeHtml(r.transaction_id)}</td>
                            <td>${escapeHtml(r.account_id)}</td>
                            <td>${escapeHtml(r.recipient_name || r.recipient_account || '')}</td>
                            <td>$${Number(amount).toLocaleString()}</td>
                            <td>${escapeHtml(r.transaction_type || '')}</td>
                            <td>${escapeHtml(r.status || '')}</td>
                            <td>${escapeHtml(date)}</td>
                        `;
                        fragment.appendChild(tr);
                    });
                    tbody.appendChild(fragment);
                } catch (err) {
                    loading.style.display = 'none';
                    info.textContent = 'Error loading transactions';
                    info.style.display = '';
                    console.error(err);
                }
            }

            function escapeHtml(s){
                if (s === null || s === undefined) return '';
                return String(s).replace(/[&\\"'<>]/g, function(c){
                    return { '&':'&amp;', '"':'&quot;', "'":"&#39;", '<':'&lt;', '>':'&gt;', '\\':'\\\\' }[c];
                });
            }

            // initial load
            loadTransactions();
        })();
    </script>

<?php include __DIR__ . '/_footer.php';
