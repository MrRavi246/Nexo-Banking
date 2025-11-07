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
        <h1>Support Tickets</h1>
        <p>List and manage user support tickets.</p>

        <div class="controls" style="margin-bottom:12px">
            <input id="ticketQuery" placeholder="search subject, message, email" />
            <select id="ticketStatus">
                <option value="">All</option>
                <option value="open">Open</option>
                <option value="pending">Pending</option>
                <option value="closed">Closed</option>
            </select>
            <button id="refreshTickets">Refresh</button>
            <button id="downloadTickets">Download CSV</button>
        </div>

        <div style="overflow:auto;margin-top:12px">
            <table class="admin-table" id="ticketsTable">
                <thead><tr><th>Ticket #</th><th>User</th><th>Subject</th><th>Status</th><th>Assigned</th><th>Updated</th><th>Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

<?php include __DIR__ . '/_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const tb = document.querySelector('#ticketsTable tbody');
    const refreshBtn = document.getElementById('refreshTickets');
    const dlBtn = document.getElementById('downloadTickets');
    const qEl = document.getElementById('ticketQuery');
    const statusEl = document.getElementById('ticketStatus');

    function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; }); }

    async function load(){
        const q = encodeURIComponent(qEl.value || '');
        const status = encodeURIComponent(statusEl.value || '');
        const res = await fetch('/backend/admin_get_tickets.php?q=' + q + '&status=' + status, { credentials: 'same-origin' });
        const p = await res.json();
        if(!p.success){ alert('Failed to load tickets: ' + p.message); return; }
        tb.innerHTML = '';
        p.data.forEach(t => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${t.ticket_id}</td>
                <td>${escapeHtml(t.email || (t.first_name||'') + ' ' + (t.last_name||''))}</td>
                <td>${escapeHtml(t.subject)}</td>
                <td><select class="statusSel" data-id="${t.ticket_id}"><option value="open">Open</option><option value="pending">Pending</option><option value="closed">Closed</option></select></td>
                <td>${escapeHtml(t.assigned_to || '')}</td>
                <td>${escapeHtml(t.updated_at)}</td>
                <td><button class="assignBtn" data-id="${t.ticket_id}">Assign to me</button> <button class="replyBtn" data-id="${t.ticket_id}">Reply</button></td>
            `;
            tb.appendChild(tr);
            // set status select value
            tr.querySelector('.statusSel').value = t.status;
        });

        // wire events
        tb.querySelectorAll('.statusSel').forEach(sel => {
            sel.addEventListener('change', async function(){
                const id = this.dataset.id; const st = this.value;
                const body = { ticket_id: parseInt(id), status: st };
                const r = await fetch('/backend/admin_update_ticket.php', { method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body)});
                const jp = await r.json(); if(!jp.success){ alert('Update failed: ' + jp.message); return; }
                this.closest('tr').querySelector('td:nth-child(6)').textContent = jp.data.updated_at;
            });
        });

        tb.querySelectorAll('.assignBtn').forEach(btn => btn.addEventListener('click', async function(){
            const id = parseInt(this.dataset.id);
            const body = { ticket_id: id, assigned_to: <?php echo intval($_SESSION['admin_id']); ?> };
            const r = await fetch('/backend/admin_update_ticket.php', { method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body)});
            const jp = await r.json(); if(!jp.success){ alert('Assign failed: ' + jp.message); return; }
            this.closest('tr').querySelector('td:nth-child(5)').textContent = jp.data.assigned_to;
        }));

        tb.querySelectorAll('.replyBtn').forEach(btn => btn.addEventListener('click', async function(){
            const id = parseInt(this.dataset.id);
            const msg = prompt('Reply message:'); if (msg === null) return;
            const body = { ticket_id: id, reply: msg };
            const r = await fetch('/backend/admin_update_ticket.php', { method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body)});
            const jp = await r.json(); if(!jp.success){ alert('Reply failed: ' + jp.message); return; }
            alert('Reply saved');
        }));
    }

    refreshBtn.addEventListener('click', load);
    dlBtn.addEventListener('click', function(){ const s = statusEl.value || ''; window.location = '/backend/admin_export_tickets.php?status=' + encodeURIComponent(s); });

    load();
});
</script>

