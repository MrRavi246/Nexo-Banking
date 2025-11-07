<?php
require_once 'config.php';
require_once 'functions.php';

if (!isAdminLoggedIn()) {
    sendResponse(false, 'Admin not authenticated', null, 401);
}

try {
    $conn = getDBConnection();

    // Validate admin session
    if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
        sendResponse(false, 'Invalid admin session', null, 401);
    }

    // Optional status filter (pending, approved, rejected, paid)
    $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : 'pending';

    $stmt = $conn->prepare("SELECT l.loan_id, l.user_id, u.username AS user_name, u.first_name, u.last_name, l.loan_type, l.principal, l.outstanding, l.term_months, l.apr, l.monthly_payment, l.status, l.purpose, l.created_at FROM loans l LEFT JOIN users u ON u.user_id = l.user_id WHERE l.status = ? ORDER BY l.created_at DESC");
    $stmt->execute([$status]);
    $apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For each application, also fetch the user's accounts so admin can choose disbursement target
    foreach ($apps as &$a) {
        $a['principal'] = (float)$a['principal'];
        $a['outstanding'] = (float)$a['outstanding'];
        $a['apr'] = (float)$a['apr'];
        $a['monthly_payment'] = is_null($a['monthly_payment']) ? null : (float)$a['monthly_payment'];

        // Fetch accounts for this user
        $acctStmt = $conn->prepare("SELECT account_id, account_number, account_type, balance FROM accounts WHERE user_id = ? ORDER BY account_id");
        $acctStmt->execute([$a['user_id']]);
        $accounts = $acctStmt->fetchAll(PDO::FETCH_ASSOC);
        // Mask account numbers for safety
        foreach ($accounts as &$acc) {
            $acc['masked_number'] = isset($acc['account_number']) ? '**** ' . substr($acc['account_number'], -4) : '';
            unset($acc['account_number']);
            $acc['balance'] = (float)$acc['balance'];
        }
        $a['accounts'] = $accounts;
    }

    sendResponse(true, 'Applications retrieved', ['applications' => $apps]);

} catch (Exception $e) {
    error_log('Error in get_loan_applications.php: ' . $e->getMessage());
    sendResponse(false, 'An error occurred while fetching applications');
}
