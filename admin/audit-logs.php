<?php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/functions.php';

if (!isAdminLoggedIn() || !validateAdminSession(getDBConnection(), $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/_header.php';
?>

    <section class="admin-content">
        <h1>Audit Logs</h1>
        <p>System audit logs and administrative actions.</p>

        <div class="controls" style="margin-bottom:12px">
            <label for="auditDays">Last days:</label>
            <select id="auditDays"><option value="1">1</option><option value="7">7</option><option value="30" selected>30</option><option value="90">90</option></select>
            <input id="auditQuery" placeholder="search action, table, ip, email" />
            <select id="auditAction"><option value="">All actions</option></select>
            <button id="refreshAudit">Refresh</button>
            <button id="downloadAudit">Download CSV</button>
        </div>

        <div style="overflow:auto;margin-top:12px">
            <table class="admin-table" id="auditTable">
                <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Table</th><th>IP</th><th>Details</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

<?php include __DIR__ . '/_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const daysEl = document.getElementById('auditDays');
    const qEl = document.getElementById('auditQuery');
    const actionEl = document.getElementById('auditAction');
    const refreshBtn = document.getElementById('refreshAudit');
    const dlBtn = document.getElementById('downloadAudit');
    const tbody = document.querySelector('#auditTable tbody');

    function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; }); }

    async function load(){
        const days = daysEl.value;
        const q = encodeURIComponent(qEl.value || '');
        const action = encodeURIComponent(actionEl.value || '');
        const res = await fetch('/backend/admin_get_audit.php?days=' + days + '&q=' + q + '&action=' + action, { credentials: 'same-origin' });
        const p = await res.json();
        if (!p.success) { alert('Failed to load audit logs: ' + p.message); return; }
        tbody.innerHTML = '';
        const seenActions = new Set();
        p.data.forEach(r => {
            seenActions.add(r.action_type);
            const tr = document.createElement('tr');
            const user = r.email ? (escapeHtml(r.email) + ' (' + escapeHtml(r.user_id || '') + ')') : escapeHtml(r.user_id || '');
            const details = escapeHtml(r.new_values || r.old_values || '');
            tr.innerHTML = `<td>${escapeHtml(r.created_at)}</td><td>${user}</td><td>${escapeHtml(r.action_type)}</td><td>${escapeHtml(r.table_name)}</td><td>${escapeHtml(r.ip_address)}</td><td><pre style="white-space:pre-wrap;max-width:480px">${details}</pre></td>`;
            tbody.appendChild(tr);
        });

        // populate actions dropdown
        const existing = Array.from(actionEl.options).map(o => o.value);
        seenActions.forEach(a => {
            if (!existing.includes(a)) {
                const opt = document.createElement('option'); opt.value = a; opt.textContent = a; actionEl.appendChild(opt);
            }
        });
    }

    refreshBtn.addEventListener('click', load);
    dlBtn.addEventListener('click', function(){ const d = daysEl.value; const a = actionEl.value || ''; window.location = '/backend/admin_export_audit.php?days=' + encodeURIComponent(d) + '&action=' + encodeURIComponent(a); });

    load();
});
</script>

