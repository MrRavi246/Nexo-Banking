<?php
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    sendResponse(false, 'Not authenticated', null, 401);
}

try {
    $conn = getDBConnection();

    // Validate session
    if (!validateSession($conn, $_SESSION['user_id'], $_SESSION['session_token'])) {
        sendResponse(false, 'Invalid session', null, 401);
    }

    // Expect POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method', null, 405);
    }

    // Read input (support application/x-www-form-urlencoded or JSON)
    $input = $_POST;
    if (empty($input)) {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $input = $json;
        }
    }

    $loanType = isset($input['loan_type']) ? sanitizeInput($input['loan_type']) : null;
    $principal = isset($input['principal']) ? floatval($input['principal']) : 0.0;
    $term = isset($input['term_months']) ? intval($input['term_months']) : 0;
    $purpose = isset($input['purpose']) ? sanitizeInput($input['purpose']) : null;

    if (!$loanType || $principal <= 0 || $term <= 0) {
        sendResponse(false, 'Missing or invalid application data', null, 400);
    }

    // Basic APR default (0% by default). Could be replaced by system_settings lookup.
    $apr = 0.00;

    // Calculate a simple monthly payment (no compounding interest if apr == 0)
    if ($apr > 0) {
        $monthlyRate = ($apr / 100) / 12;
        $monthlyPayment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $term)) / (pow(1 + $monthlyRate, $term) - 1);
    } else {
        $monthlyPayment = $principal / max(1, $term);
    }

    // Insert loan (status pending by default)
    $stmt = $conn->prepare("INSERT INTO loans (user_id, loan_type, principal, outstanding, term_months, apr, monthly_payment, status, purpose) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $loanType,
        number_format($principal, 2, '.', ''),
        number_format($principal, 2, '.', ''),
        $term,
        number_format($apr, 2, '.', ''),
        number_format($monthlyPayment, 2, '.', ''),
        'pending',
        $purpose
    ]);

    $loanId = $conn->lastInsertId();

    // Optionally log audit
    logAudit($conn, $_SESSION['user_id'], 'create_loan_application', 'loans', $loanId, null, ['loan_type' => $loanType, 'principal' => $principal, 'term_months' => $term]);

    sendResponse(true, 'Loan application submitted', ['loan_id' => $loanId, 'status' => 'pending']);

} catch (Exception $e) {
    error_log('Error in apply_loan.php: ' . $e->getMessage());
    sendResponse(false, 'An error occurred while submitting application');
}
