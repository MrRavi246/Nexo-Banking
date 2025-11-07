<?php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/functions.php';

// simple admin session guard for pages
if (!isAdminLoggedIn() || !validateAdminSession(getDBConnection(), $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/_header.php';
?>

    <section class="admin-content">
        <h1>Security & Compliance</h1>
        <p>Manage security policies, blocked IPs, audit rules and compliance settings.</p>

        <div class="controls" style="margin-bottom:12px">
            <label for="secDays">Last days:</label>
            <select id="secDays">
                <option value="1">1</option>
                <option value="7">7</option>
                <option value="30" selected>30</option>
                <option value="90">90</option>
            </select>
            <button id="secRefresh">Refresh</button>
            <button id="secDownload">Download CSV</button>
        </div>

        <div style="margin-top:16px">
            <h3>Recent Failed Logins</h3>
            <div style="overflow:auto">
                <table class="admin-table" id="failedLoginsTable">
                    <thead><tr><th>When</th><th>User</th><th>Email</th><th>IP</th><th>User Agent</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div style="margin-top:16px">
            <h3>Suspicious IPs</h3>
            <div style="overflow:auto">
                <table class="admin-table" id="suspiciousIpsTable">
                    <thead><tr><th>IP</th><th>Failures</th><th>Last Seen</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div style="margin-top:16px">
            <h3>Active Sessions (recent)</h3>
            <div style="overflow:auto">
                <table class="admin-table" id="activeSessionsTable">
                    <thead><tr><th>Session ID</th><th>User</th><th>IP</th><th>User Agent</th><th>Last Activity</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div style="margin-top:16px">
            <h3>Recent Audit Events</h3>
            <div style="overflow:auto">
                <table class="admin-table" id="recentAuditsTable">
                    <thead><tr><th>When</th><th>User</th><th>Action</th><th>Table</th><th>Record</th><th>IP</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </section>

<?php include __DIR__ . '/_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const daysSel = document.getElementById('secDays');
    const refreshBtn = document.getElementById('secRefresh');
    const downloadBtn = document.getElementById('secDownload');

    async function load() {
        const days = daysSel.value;
        const res = await fetch('/backend/admin_get_security.php?days=' + encodeURIComponent(days), { credentials: 'same-origin' });
        const data = await res.json();
        if (!data.success) {
            alert('Failed to load security data: ' + data.message);
            return;
        }

        // failed logins
        const flT = document.querySelector('#failedLoginsTable tbody');
        flT.innerHTML = '';
        (data.data.failed_logins || []).forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${r.created_at}</td><td>${r.user_id || ''} ${escapeHtml(r.first_name||'')} ${escapeHtml(r.last_name||'')}</td><td>${escapeHtml(r.email||'')}</td><td>${escapeHtml(r.ip_address||'')}</td><td>${escapeHtml(r.user_agent||'')}</td>`;
            flT.appendChild(tr);
        });

        // suspicious ips
        const ipT = document.querySelector('#suspiciousIpsTable tbody');
        ipT.innerHTML = '';
        (data.data.suspicious_ips || []).forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${escapeHtml(r.ip_address)}</td><td>${r.failures}</td><td>${r.last_seen}</td>`;
            ipT.appendChild(tr);
        });

        // sessions
        const sT = document.querySelector('#activeSessionsTable tbody');
        sT.innerHTML = '';
        (data.data.active_sessions || []).forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${escapeHtml(r.session_id)}</td><td>${escapeHtml(r.user_id)}</td><td>${escapeHtml(r.ip_address||'')}</td><td>${escapeHtml(r.user_agent||'')}</td><td>${escapeHtml(r.last_activity||'')}</td>`;
            sT.appendChild(tr);
        });

        // audits
        const aT = document.querySelector('#recentAuditsTable tbody');
        aT.innerHTML = '';
        (data.data.recent_audit || []).forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${r.created_at}</td><td>${r.user_id||''}</td><td>${escapeHtml(r.action_type||'')}</td><td>${escapeHtml(r.table_name||'')}</td><td>${escapeHtml(r.record_id||'')}</td><td>${escapeHtml(r.ip_address||'')}</td>`;
            aT.appendChild(tr);
        });
    }

    function escapeHtml(s){
        if(!s) return '';
        return String(s).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; });
    }

    refreshBtn.addEventListener('click', load);
    downloadBtn.addEventListener('click', function(){
        const days = daysSel.value;
        window.location = '/backend/admin_export_security.php?days=' + encodeURIComponent(days);
    });

    load();
});
</script>

