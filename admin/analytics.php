<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/_header.php';
?>

    <section class="admin-content">
        <h1>Analytics & Reports</h1>
        <p>System analytics, charts and downloadable reports.</p>

        <div class="admin-info" id="adminAnalyticsInfo">Loading analytics…</div>

        <div style="margin-top:12px; display:flex; gap:.5rem; align-items:center;">
            <label style="color:#ddd;">Range:</label>
            <select id="analyticsRange" style="padding:8px;border-radius:6px;background:transparent;color:#fff">
                <option value="7">Last 7 days</option>
                <option value="30" selected>Last 30 days</option>
                <option value="90">Last 90 days</option>
                <option value="365">Last Year</option>
            </select>
            <button id="refreshAnalytics" class="btn">Refresh</button>
            <button id="downloadCsv" class="btn">Download CSV</button>
        </div>

        <div style="margin-top:16px;display:flex;gap:1rem;flex-wrap:wrap">
            <div style="flex:1;min-width:200px;background:rgba(255,255,255,0.03);padding:12px;border-radius:8px">
                <div style="font-size:12px;color:#aaa">Total Income</div>
                <div id="metricIncome" style="font-size:20px;padding-top:6px">$0.00</div>
            </div>
            <div style="flex:1;min-width:200px;background:rgba(255,255,255,0.03);padding:12px;border-radius:8px">
                <div style="font-size:12px;color:#aaa">Total Expenses</div>
                <div id="metricExpenses" style="font-size:20px;padding-top:6px">$0.00</div>
            </div>
            <div style="flex:1;min-width:200px;background:rgba(255,255,255,0.03);padding:12px;border-radius:8px">
                <div style="font-size:12px;color:#aaa">Net Savings</div>
                <div id="metricNet" style="font-size:20px;padding-top:6px">$0.00</div>
            </div>
            <div style="flex:1;min-width:200px;background:rgba(255,255,255,0.03);padding:12px;border-radius:8px">
                <div style="font-size:12px;color:#aaa">Savings Rate</div>
                <div id="metricRate" style="font-size:20px;padding-top:6px">0%</div>
            </div>
        </div>

        <div style="margin-top:16px">
            <canvas id="analyticsChart" style="width:100%;height:320px;background:rgba(255,255,255,0.02);border-radius:8px"></canvas>
        </div>
    </section>

<?php include __DIR__ . '/_footer.php'; ?>

<script>
    (function(){
        const info = document.getElementById('adminAnalyticsInfo');
        const rangeEl = document.getElementById('analyticsRange');
        const refreshBtn = document.getElementById('refreshAnalytics');
        const downloadBtn = document.getElementById('downloadCsv');
        const incomeEl = document.getElementById('metricIncome');
        const expensesEl = document.getElementById('metricExpenses');
        const netEl = document.getElementById('metricNet');
        const rateEl = document.getElementById('metricRate');
        const canvas = document.getElementById('analyticsChart');

        refreshBtn.addEventListener('click', loadAnalytics);
        downloadBtn.addEventListener('click', function(){
            const days = encodeURIComponent(rangeEl.value);
            window.location = '/Nexo-Banking/backend/admin_export_report.php?days='+days;
        });

        async function loadAnalytics(){
            info.textContent = 'Loading analytics…';
            try {
                const days = encodeURIComponent(rangeEl.value);
                const res = await fetch('/Nexo-Banking/backend/admin_get_analytics.php?days='+days, {credentials:'same-origin'});
                const json = await res.json();
                if (!json.success) { info.textContent = json.message || 'Failed'; return; }
                const d = json.data;
                incomeEl.textContent = '$' + Number(d.total_income || 0).toLocaleString();
                expensesEl.textContent = '$' + Number(d.total_expenses || 0).toLocaleString();
                netEl.textContent = '$' + Number(d.net_savings || 0).toLocaleString();
                rateEl.textContent = Number(d.savings_rate || 0).toFixed(1) + '%';
                info.style.display = 'none';
                drawChart(d.income_expense_series);
            } catch (err) {
                console.error(err); info.textContent = 'Error loading analytics';
            }
        }

        function drawChart(series){
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const w = canvas.width = canvas.clientWidth;
            const h = canvas.height = 320;
            ctx.clearRect(0,0,w,h);
            const labels = series.labels || [];
            const income = series.income || [];
            const expenses = series.expenses || [];
            const max = Math.max(...income, ...expenses, 1);
            const padding = 40; const chartW = w - padding*2; const chartH = h - padding*2;
            const step = labels.length ? chartW / labels.length : chartW;
            // draw income bars
            income.forEach((v,i)=>{
                const x = padding + i*step;
                const barH = (v/max)*chartH;
                ctx.fillStyle = '#7ef29b'; ctx.fillRect(x, padding + chartH - barH, step*0.4, barH);
            });
            // draw expense bars
            expenses.forEach((v,i)=>{
                const x = padding + i*step + step*0.45;
                const barH = (v/max)*chartH;
                ctx.fillStyle = '#ff7b88'; ctx.fillRect(x, padding + chartH - barH, step*0.4, barH);
            });
        }

        // initial
        loadAnalytics();
    })();
</script>
