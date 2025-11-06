<?php
require_once __DIR__ . '/../backend/admin_auth_check.php';

include __DIR__ . '/_header.php';
?>

<section class="admin-content">
    <h1>User Management</h1>
    
    <!-- Tabs -->
    <div class="user-tabs">
        <button class="tab-btn active" onclick="showTab('pending')">
            Pending Approval <span class="badge" id="pendingCount">0</span>
        </button>
        <button class="tab-btn" onclick="showTab('all')">All Users</button>
        <button class="tab-btn" onclick="showTab('active')">Active Users</button>
        <button class="tab-btn" onclick="showTab('rejected')">Rejected Users</button>
    </div>
    
    <!-- Pending Users Tab -->
    <div id="pending-tab" class="tab-content active">
        <h2>Pending User Approvals</h2>
        <div id="pendingUsersContainer">
            <p>Loading pending users...</p>
        </div>
    </div>
    
    <!-- All Users Tab -->
    <div id="all-tab" class="tab-content">
        <h2>All Users</h2>
        <div id="allUsersContainer">
            <p>Loading users...</p>
        </div>
    </div>
    
    <!-- Active Users Tab -->
    <div id="active-tab" class="tab-content">
        <h2>Active Users</h2>
        <div id="activeUsersContainer">
            <p>Loading active users...</p>
        </div>
    </div>
    
    <!-- Rejected Users Tab -->
    <div id="rejected-tab" class="tab-content">
        <h2>Rejected Users</h2>
        <div id="rejectedUsersContainer">
            <p>Loading rejected users...</p>
        </div>
    </div>
</section>

<!-- User Detail Modal -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="userDetails"></div>
    </div>
</div>

<!-- Rejection Reason Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeRejectModal()">&times;</span>
        <h2>Reject User Registration</h2>
        <form id="rejectForm">
            <input type="hidden" id="rejectUserId">
            <div class="form-group">
                <label for="rejectionReason">Rejection Reason:</label>
                <textarea id="rejectionReason" rows="4" required placeholder="Enter reason for rejection..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" onclick="closeRejectModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject User</button>
            </div>
        </form>
    </div>
</div>

<style>
.user-tabs {
    display: flex;
    gap: 0;
    margin-bottom: 30px;
    border-bottom: 2px solid #2a2a2a;
    background: linear-gradient(180deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0) 100%);
    border-radius: 8px 8px 0 0;
}

.tab-btn {
    padding: 15px 30px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    color: #999;
    position: relative;
    transition: all 0.3s ease;
    border-radius: 8px 8px 0 0;
}

.tab-btn:hover {
    color: #fff;
    background: rgba(255,255,255,0.05);
}

.tab-btn.active {
    color: #007bff;
    background: rgba(0, 123, 255, 0.1);
    border-bottom: 3px solid #007bff;
}

.tab-btn .badge {
    background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
    color: white;
    border-radius: 20px;
    padding: 3px 10px;
    font-size: 11px;
    margin-left: 8px;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(255, 65, 108, 0.3);
}

.tab-content {
    display: none;
    animation: fadeIn 0.3s ease-in;
}

.tab-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.user-card {
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 20px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.user-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,123,255,0.2);
    border-color: rgba(0,123,255,0.3);
}

.user-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 20px;
}

.user-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    font-weight: 700;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    border: 3px solid rgba(255,255,255,0.2);
    transition: all 0.3s ease;
}

.user-card:hover .user-avatar {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 12px 30px rgba(102, 126, 234, 0.6);
}

.user-details h3 {
    margin: 0 0 8px 0;
    color: #fff;
    font-size: 20px;
    font-weight: 700;
    letter-spacing: -0.5px;
}

.user-details p {
    margin: 0;
    color: #aaa;
    font-size: 14px;
    line-height: 1.6;
}

.user-details p small {
    color: #888;
    font-size: 12px;
}

.user-meta {
    margin: 20px 0;
    padding: 20px;
    background: rgba(0,0,0,0.2);
    border-radius: 12px;
    border-left: 4px solid #007bff;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.user-meta p {
    margin: 0;
    color: #ccc;
    font-size: 14px;
}

.user-meta strong {
    color: #fff;
    font-weight: 600;
    margin-right: 8px;
}

.user-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.btn i {
    font-size: 16px;
}

.btn-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(56, 239, 125, 0.4);
}

.btn-success:active {
    transform: translateY(0);
}

.btn-danger {
    background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
    color: white;
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 65, 108, 0.4);
}

.btn-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #636363 0%, #a2ab58 100%);
    color: white;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #757575 0%, #b8c26a 100%);
    transform: translateY(-2px);
}

.status-badge {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.status-pending {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.status-active {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.status-rejected {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: #333;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.8);
    backdrop-filter: blur(8px);
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background: linear-gradient(135deg, rgba(30,30,30,0.98) 0%, rgba(50,50,50,0.98) 100%);
    margin: 5% auto;
    padding: 35px;
    border-radius: 20px;
    width: 80%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-content h2 {
    color: #fff;
    font-size: 24px;
    margin-bottom: 20px;
    font-weight: 700;
}

.close {
    color: #999;
    float: right;
    font-size: 32px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    line-height: 1;
}

.close:hover {
    color: #ff4b2b;
    transform: rotate(90deg);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #fff;
    font-size: 14px;
}

.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    font-family: inherit;
    background: rgba(0,0,0,0.3);
    color: #fff;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 25px;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.admin-table th,
.admin-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.admin-table th {
    background: linear-gradient(135deg, rgba(0,123,255,0.2) 0%, rgba(0,123,255,0.1) 100%);
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 1px;
}

.admin-table td {
    color: #ccc;
    font-size: 14px;
}

.admin-table tr {
    transition: all 0.3s ease;
}

.admin-table tr:hover {
    background: rgba(0,123,255,0.1);
    transform: scale(1.01);
}

.admin-table tr:last-child td {
    border-bottom: none;
}
</style>

<script src="../assets/js/admin-users.js"></script>

<?php include __DIR__ . '/_footer.php'; ?>

