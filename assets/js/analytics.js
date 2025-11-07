// Analytics Page JavaScript - Frontend Only

document.addEventListener('DOMContentLoaded', function() {
    initializeAnalytics();
});

function initializeAnalytics() {
    setupTimeFilter();
    setupChartControls();
    setupMiniCharts();
    // Load initial analytics for the default time range
    const timeRangeEl = document.getElementById('timeRange');
    const days = timeRangeEl ? timeRangeEl.value : 30;
    updateAnalytics(days);
    setupGoalTracking();
}

function setupTimeFilter() {
    const timeRange = document.getElementById('timeRange');
    timeRange.addEventListener('change', function() {
        updateAnalytics(this.value);
    });
}

function updateAnalytics(days) {
    // Fetch real analytics from backend
    (async () => {
        try {
            const res = await fetch(`/Nexo-Banking/backend/get_analytics.php?days=${encodeURIComponent(days)}`, {credentials: 'same-origin'});
            const json = await res.json();
            if (json.success) {
                window.analyticsData = json.data;
                showNotification(`Updated analytics for the last ${days} days`, 'info');
                updateMetrics();
                createCharts();
            } else {
                showNotification('Failed to load analytics: ' + json.message, 'error');
                // fallback to simulated
                updateMetrics(days);
                createCharts();
            }
        } catch (err) {
            console.error('Error fetching analytics', err);
            showNotification('Error loading analytics', 'error');
            // fallback
            updateMetrics(days);
            createCharts();
        }
    })();
}

function updateMetrics(days) {
    // If analyticsData is present use it
    const data = window.analyticsData;
    if (data) {
        document.querySelector('.metric-card:nth-child(1) .metric-value').textContent = `$${Number(data.total_income || 0).toLocaleString()}`;
        document.querySelector('.metric-card:nth-child(2) .metric-value').textContent = `$${Number(data.total_expenses || 0).toLocaleString()}`;
        document.querySelector('.metric-card:nth-child(3) .metric-value').textContent = `$${Number(data.net_savings || 0).toLocaleString()}`;
        document.querySelector('.metric-card:nth-child(4) .metric-value').textContent = `${Number(data.savings_rate || 0).toFixed(1)}%`;
        // Populate top categories table if present
        if (Array.isArray(data.top_categories)) {
            const tbody = document.querySelector('.analytics-table tbody');
            if (tbody) {
                // Replace body with top categories rows
                let rowsHtml = '';
                data.top_categories.forEach(cat => {
                    const label = cat.category || 'Other';
                    const amt = Number(cat.total || 0).toFixed(2);
                    rowsHtml += `<tr><td>${label}</td><td>$${Number(amt).toLocaleString()}</td><td>--</td><td>--</td><td><!-- sparkline --></td></tr>`;
                });
                tbody.innerHTML = rowsHtml;
                setupMiniCharts();
            }
        }
        return;
    }

    // fallback to previous behavior when no backend data
    const multiplier = days / 30; // Base calculations on 30-day period
    const baseIncome = 12450;
    const baseExpenses = 8320;
    const income = Math.round(baseIncome * multiplier);
    const expenses = Math.round(baseExpenses * multiplier);
    const savings = income - expenses;
    const savingsRate = ((savings / income) * 100);
    document.querySelector('.metric-card:nth-child(1) .metric-value').textContent = `$${income.toLocaleString()}`;
    document.querySelector('.metric-card:nth-child(2) .metric-value').textContent = `$${expenses.toLocaleString()}`;
    document.querySelector('.metric-card:nth-child(3) .metric-value').textContent = `$${savings.toLocaleString()}`;
    document.querySelector('.metric-card:nth-child(4) .metric-value').textContent = `${savingsRate.toFixed(1)}%`;
}

function createCharts() {
    createIncomeExpenseChart();
    createCategoryChart();
    createCashFlowChart();
}

function createIncomeExpenseChart() {
    const canvas = document.getElementById('incomeExpenseChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    
    // Clear canvas
    ctx.clearRect(0, 0, width, height);
    // Use analyticsData when available
    const d = window.analyticsData;
    if (d && d.income_expense_series) {
        const incomeData = d.income_expense_series.income || [];
        const expenseData = d.income_expense_series.expenses || [];
        const labels = d.income_expense_series.labels || [];
        drawBarChart(ctx, width, height, incomeData, expenseData, labels);
        return;
    }

    // fallback sample
    const incomeData = [1800, 2200, 1900, 2500, 2100, 1700, 2300];
    const expenseData = [1200, 1400, 1100, 1600, 1300, 1000, 1500];
    const labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    drawBarChart(ctx, width, height, incomeData, expenseData, labels);
}

function drawBarChart(ctx, width, height, incomeData, expenseData, labels) {
    const padding = 40;
    const chartWidth = width - padding * 2;
    const chartHeight = height - padding * 2;
    const barWidth = chartWidth / (labels.length * 2 + 1);
    
    const maxValue = Math.max(...incomeData, ...expenseData);
    
    // Draw bars
    labels.forEach((label, index) => {
        const x = padding + (index * 2 + 1) * barWidth;
        
        // Income bar
        const incomeHeight = (incomeData[index] / maxValue) * chartHeight;
        ctx.fillStyle = '#7ef29b';
        ctx.fillRect(x, padding + chartHeight - incomeHeight, barWidth * 0.8, incomeHeight);
        
        // Expense bar
        const expenseHeight = (expenseData[index] / maxValue) * chartHeight;
        ctx.fillStyle = '#ff7b88';
        ctx.fillRect(x + barWidth * 0.8, padding + chartHeight - expenseHeight, barWidth * 0.8, expenseHeight);
        
        // Labels
        ctx.fillStyle = 'rgba(255,255,255,0.7)';
        ctx.font = '12px Roboto';
        ctx.textAlign = 'center';
        ctx.fillText(label, x + barWidth * 0.8, height - 10);
    });
}

function createCategoryChart() {
    const canvas = document.getElementById('categoryChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    
    // Clear canvas
    ctx.clearRect(0, 0, width, height);
    // If analytics data available, use top_categories
    const d = window.analyticsData;
    if (d && Array.isArray(d.top_categories) && d.top_categories.length > 0) {
        const palette = ['#ff7b88','#7ec8f2','#eb7ef2','#fbbf24','#7ef29b','#a0e7ff'];
        const data = d.top_categories.map((c, idx) => ({ label: c.category || 'Other', value: Number(c.total||0), color: palette[idx % palette.length] }));
        drawPieChart(ctx, width, height, data);
        return;
    }

    // fallback sample
    const data = [
        { label: 'Food & Dining', value: 2450, color: '#ff7b88' },
        { label: 'Transportation', value: 1820, color: '#7ec8f2' },
        { label: 'Shopping', value: 1290, color: '#eb7ef2' },
        { label: 'Utilities', value: 980, color: '#fbbf24' },
        { label: 'Entertainment', value: 780, color: '#7ef29b' }
    ];
    drawPieChart(ctx, width, height, data);
}

function drawPieChart(ctx, width, height, data) {
    const centerX = width / 2;
    const centerY = height / 2;
    const radius = Math.min(width, height) / 3;
    
    const total = data.reduce((sum, item) => sum + item.value, 0);
    let currentAngle = -Math.PI / 2; // Start from top
    
    data.forEach(item => {
        const sliceAngle = (item.value / total) * 2 * Math.PI;
        
        // Draw slice
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle);
        ctx.closePath();
        ctx.fillStyle = item.color;
        ctx.fill();
        
        currentAngle += sliceAngle;
    });
    
    // Draw center circle for donut effect
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius * 0.5, 0, 2 * Math.PI);
    ctx.fillStyle = '#0F0F10';
    ctx.fill();
}

function createCashFlowChart() {
    const canvas = document.getElementById('cashFlowChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    
    // Clear canvas
    ctx.clearRect(0, 0, width, height);
    const d = window.analyticsData;
    if (d && d.cash_flow_series) {
        const vals = d.cash_flow_series.values || [];
        const labels = d.cash_flow_series.labels || [];
        drawLineChart(ctx, width, height, vals, labels);
        return;
    }

    // fallback sample cash flow
    const cashFlowData = [500, 800, 300, 900, 700, 1200, 600, 1100, 400, 950, 800, 1300, 750, 1050];
    const labels = Array.from({length: 14}, (_, i) => `Day ${i + 1}`);
    drawLineChart(ctx, width, height, cashFlowData, labels);
}

function drawLineChart(ctx, width, height, data, labels) {
    const padding = 60;
    const chartWidth = width - padding * 2;
    const chartHeight = height - padding * 2;
    
    const maxValue = Math.max(...data);
    const minValue = Math.min(...data);
    const valueRange = maxValue - minValue;
    
    // Draw grid lines
    ctx.strokeStyle = 'rgba(255,255,255,0.1)';
    ctx.lineWidth = 1;
    
    for (let i = 0; i <= 5; i++) {
        const y = padding + (i / 5) * chartHeight;
        ctx.beginPath();
        ctx.moveTo(padding, y);
        ctx.lineTo(width - padding, y);
        ctx.stroke();
    }
    
    // Draw line
    ctx.strokeStyle = '#eb7ef2';
    ctx.lineWidth = 3;
    ctx.beginPath();
    
    data.forEach((value, index) => {
        const x = padding + (index / (data.length - 1)) * chartWidth;
        const y = padding + chartHeight - ((value - minValue) / valueRange) * chartHeight;
        
        if (index === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }
    });
    
    ctx.stroke();
    
    // Draw points
    ctx.fillStyle = '#eb7ef2';
    data.forEach((value, index) => {
        const x = padding + (index / (data.length - 1)) * chartWidth;
        const y = padding + chartHeight - ((value - minValue) / valueRange) * chartHeight;
        
        ctx.beginPath();
        ctx.arc(x, y, 4, 0, 2 * Math.PI);
        ctx.fill();
    });
}

function setupChartControls() {
    const chartBtns = document.querySelectorAll('.chart-btn');
    
    chartBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            chartBtns.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            const period = this.dataset.period;
            updateCashFlowChart(period);
        });
    });
}

function updateCashFlowChart(period) {
    showNotification(`Updated cash flow chart for ${period} view`, 'info');
    
    // Simulate different data based on period
    let data, labels;
    
    switch(period) {
        case 'daily':
            data = [500, 800, 300, 900, 700, 1200, 600, 1100, 400, 950, 800, 1300, 750, 1050];
            labels = Array.from({length: 14}, (_, i) => `Day ${i + 1}`);
            break;
        case 'weekly':
            data = [3500, 4200, 3800, 4500, 3900, 4100, 4300, 3700];
            labels = Array.from({length: 8}, (_, i) => `Week ${i + 1}`);
            break;
        case 'monthly':
            data = [15000, 16500, 14800, 17200, 15800, 16100];
            labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            break;
    }
    
    const canvas = document.getElementById('cashFlowChart');
    const ctx = canvas.getContext('2d');
    drawLineChart(ctx, canvas.width, canvas.height, data, labels);
}

function setupMiniCharts() {
    const miniCharts = document.querySelectorAll('.mini-chart');
    
    miniCharts.forEach(canvas => {
        const values = canvas.dataset.values.split(',').map(Number);
        drawMiniSparkline(canvas, values);
    });
}

function drawMiniSparkline(canvas, data) {
    const ctx = canvas.getContext('2d');
    const width = canvas.width = 60;
    const height = canvas.height = 30;
    
    ctx.clearRect(0, 0, width, height);
    
    const maxValue = Math.max(...data);
    const minValue = Math.min(...data);
    const valueRange = maxValue - minValue || 1;
    
    ctx.strokeStyle = '#eb7ef2';
    ctx.lineWidth = 2;
    ctx.beginPath();
    
    data.forEach((value, index) => {
        const x = (index / (data.length - 1)) * width;
        const y = height - ((value - minValue) / valueRange) * height;
        
        if (index === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }
    });
    
    ctx.stroke();
}

function setupGoalTracking() {
    const addGoalBtn = document.querySelector('.add-goal-btn');
    
    addGoalBtn.addEventListener('click', function() {
        showAddGoalModal();
    });
}

function showAddGoalModal() {
    showNotification('Add Goal feature - This would open a modal to create new financial goals', 'info');
    
    // In a real app, this would show a modal for adding new goals
    setTimeout(() => {
        showNotification('New goal added successfully!', 'success');
    }, 2000);
}

function updateGoalProgress(goalIndex, newProgress) {
    const goalCards = document.querySelectorAll('.goal-card');
    if (goalCards[goalIndex]) {
        const progressFill = goalCards[goalIndex].querySelector('.progress-fill');
        const progressText = goalCards[goalIndex].querySelector('.progress-info span:last-child');
        
        progressFill.style.width = `${newProgress}%`;
        progressText.textContent = `${newProgress}%`;
    }
}

// Simulate real-time updates
function simulateDataUpdates() {
    setInterval(() => {
        // Randomly update one of the metrics slightly
        const metricCards = document.querySelectorAll('.metric-value');
        const randomIndex = Math.floor(Math.random() * metricCards.length);
        const currentValue = parseFloat(metricCards[randomIndex].textContent.replace(/[$,]/g, ''));
        
        // Small random change (±2%)
        const change = (Math.random() - 0.5) * 0.04 * currentValue;
        const newValue = Math.max(0, currentValue + change);
        
        if (randomIndex === 3) { // Percentage metric
            metricCards[randomIndex].textContent = `${newValue.toFixed(1)}%`;
        } else { // Dollar amounts
            metricCards[randomIndex].textContent = `$${Math.round(newValue).toLocaleString()}`;
        }
    }, 30000); // Update every 30 seconds
}

// Start simulation
setTimeout(simulateDataUpdates, 5000);

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
                max-width: 400px;
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
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Export functions for external use
window.analyticsAPI = {
    updateGoalProgress,
    updateMetrics,
    showNotification
};
