<?php
require_once 'config.php';
require_once 'functions.php';

// All responses use sendResponse()

if (!isLoggedIn()) {
    sendResponse(false, 'Not authenticated', null, 401);
}

try {
    $conn = getDBConnection();

    // Validate session
    if (!validateSession($conn, $_SESSION['user_id'], $_SESSION['session_token'])) {
        sendResponse(false, 'Invalid session', null, 401);
    }

    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT loan_id, loan_type, principal, outstanding, term_months, apr, monthly_payment, status, purpose, created_at, updated_at FROM loans WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cast numeric fields to proper types
    foreach ($loans as &$loan) {
        $loan['principal'] = (float)$loan['principal'];
        $loan['outstanding'] = (float)$loan['outstanding'];
        $loan['apr'] = (float)$loan['apr'];
        $loan['monthly_payment'] = is_null($loan['monthly_payment']) ? null : (float)$loan['monthly_payment'];
        $loan['term_months'] = (int)$loan['term_months'];
    }

    sendResponse(true, 'Loans retrieved', ['loans' => $loans]);

} catch (Exception $e) {
    error_log('Error in get_loans.php: ' . $e->getMessage());
    sendResponse(false, 'An error occurred while fetching loans');
}
