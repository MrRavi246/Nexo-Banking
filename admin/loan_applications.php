<?php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/functions.php';

if (!isAdminLoggedIn()) {
    header('Location: ' . ADMIN_LOGIN_URL);
    exit();
}

$conn = getDBConnection();
if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    header('Location: ' . ADMIN_LOGIN_URL);
    exit();
}

// Get loan statistics
$stats = [];
try {
    $stmt = $conn->query("SELECT 
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
        COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
        COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_count,
        COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count,
        SUM(CASE WHEN status = 'pending' THEN principal ELSE 0 END) as pending_amount,
        SUM(CASE WHEN status IN ('active', 'approved') THEN outstanding ELSE 0 END) as outstanding_amount
        FROM loans");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Error fetching loan stats: ' . $e->getMessage());
}

include '_header.php';
?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ri-hand-coin-line"></i> Loan Applications</h1>
        <p>Review and manage loan applications from customers</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon warning"><i class="ri-time-line"></i></div>
        <div class="stat-info">
            <span class="stat-label">Pending Review</span>
            <span class="stat-value"><?= $stats['pending_count'] ?? 0 ?></span>
            <span class="stat-sublabel">₹<?= number_format($stats['pending_amount'] ?? 0, 2) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="ri-checkbox-circle-line"></i></div>
        <div class="stat-info">
            <span class="stat-label">Approved</span>
            <span class="stat-value"><?= $stats['approved_count'] ?? 0 ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary"><i class="ri-money-dollar-circle-line"></i></div>
        <div class="stat-info">
            <span class="stat-label">Active Loans</span>
            <span class="stat-value"><?= $stats['active_count'] ?? 0 ?></span>
            <span class="stat-sublabel">₹<?= number_format($stats['outstanding_amount'] ?? 0, 2) ?> outstanding</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="ri-check-double-line"></i></div>
        <div class="stat-info">
            <span class="stat-label">Paid Off</span>
            <span class="stat-value"><?= $stats['paid_count'] ?? 0 ?></span>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <h2><i class="ri-file-list-3-line"></i> Loan Applications</h2>
        <div class="filter-controls">
            <label for="statusFilter" style="margin-right: 0.5rem; font-weight: 500;">Filter by Status:</label>
            <select id="statusFilter" class="form-select" onchange="loadApplications()">
                <option value="pending">Pending Review</option>
                <option value="approved">Approved</option>
                <option value="active">Active</option>
                <option value="rejected">Rejected</option>
                <option value="paid">Paid Off</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div id="applicationsContainer" class="table-container">
            <div class="loading-spinner">
                <i class="ri-loader-4-line"></i>
                <p>Loading loan applications...</p>
            </div>
        </div>
    </div>
</div>

<script src="/Nexo-Banking/assets/js/admin-loans.js"></script>

<?php include '_footer.php'; ?>
