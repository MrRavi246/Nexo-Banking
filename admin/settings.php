<?php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/functions.php';

// admin guard
if (!isAdminLoggedIn() || !validateAdminSession(getDBConnection(), $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/_header.php';
?>

    <section class="admin-content">
        <h1>System Settings</h1>
        <p>View and manage system-level configuration values stored in <code>system_settings</code>.</p>

        <div class="controls" style="margin-bottom:12px">
            <button id="refreshSettings">Refresh</button>
            <button id="downloadSettings">Download CSV</button>
        </div>

        <div style="overflow:auto">
            <table class="admin-table" id="settingsTable">
                <thead><tr><th>Key</th><th>Value</th><th>Description</th><th>Active</th><th>Updated</th><th>Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

<?php include __DIR__ . '/_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const tb = document.querySelector('#settingsTable tbody');
    const refreshBtn = document.getElementById('refreshSettings');
    const dlBtn = document.getElementById('downloadSettings');

    function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; }); }

    async function load(){
        const res = await fetch('/backend/admin_get_settings.php', { credentials: 'same-origin' });
        const payload = await res.json();
        if(!payload.success){ alert('Failed to load settings: ' + payload.message); return; }
        tb.innerHTML = '';
        payload.data.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(r.setting_key)}</td>
                <td><span class="setting-value">${escapeHtml(r.setting_value)}</span></td>
                <td>${escapeHtml(r.description)}</td>
                <td><input type="checkbox" class="setting-active" data-id="${r.setting_id}" ${r.is_active ? 'checked' : ''}></td>
                <td>${escapeHtml(r.updated_at)}</td>
                <td>
                    <button class="editBtn" data-id="${r.setting_id}" data-key="${escapeHtml(r.setting_key)}">Edit</button>
                </td>
            `;
            tb.appendChild(tr);
        });

        // wire edit buttons
        tb.querySelectorAll('.editBtn').forEach(btn => {
            btn.addEventListener('click', async function(){
                const id = this.dataset.id;
                const key = this.dataset.key;
                const row = this.closest('tr');
                const valueSpan = row.querySelector('.setting-value');
                const newVal = prompt('Enter new value for ' + key, valueSpan.textContent);
                if (newVal === null) return; // cancel
                const body = { setting_id: parseInt(id), setting_value: newVal };
                const r = await fetch('/backend/admin_update_settings.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body)
                });
                const p = await r.json();
                if (!p.success) { alert('Failed to update: ' + p.message); return; }
                valueSpan.textContent = p.data.setting_value;
                row.querySelector('td:nth-child(5)').textContent = p.data.updated_at;
                alert('Updated');
            });
        });

        // wire active toggles
        tb.querySelectorAll('.setting-active').forEach(cb => {
            cb.addEventListener('change', async function(){
                const id = parseInt(this.dataset.id);
                const body = { setting_id: id, is_active: this.checked ? 1 : 0 };
                const r = await fetch('/backend/admin_update_settings.php', {
                    method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body)
                });
                const p = await r.json();
                if (!p.success) { alert('Failed to update active: ' + p.message); return; }
                this.closest('tr').querySelector('td:nth-child(5)').textContent = p.data.updated_at;
            });
        });
    }

    refreshBtn.addEventListener('click', load);
    dlBtn.addEventListener('click', function(){ window.location = '/backend/admin_export_settings.php'; });

    load();
});
</script>

