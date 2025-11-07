<?php
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    sendResponse(false, 'Not authenticated', null, 401);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method', null, 405);
    }

    $conn = getDBConnection();

    // Validate session
    if (!validateSession($conn, $_SESSION['user_id'], $_SESSION['session_token'])) {
        sendResponse(false, 'Invalid session', null, 401);
    }

    // Read input
    $input = $_POST;
    if (empty($input)) {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json)) $input = $json;
    }

    $loanId = isset($input['loan_id']) ? intval($input['loan_id']) : 0;
    $amount = isset($input['amount']) ? floatval($input['amount']) : 0.0;
    $accountId = isset($input['account_id']) ? intval($input['account_id']) : 0;

    if ($loanId <= 0 || $amount <= 0 || $accountId <= 0) {
        sendResponse(false, 'Missing or invalid parameters', null, 400);
    }

    // Use transactions and row-level locking to prevent race conditions
    $conn->beginTransaction();

    // Lock loan row
    $stmt = $conn->prepare("SELECT * FROM loans WHERE loan_id = ? AND user_id = ? FOR UPDATE");
    $stmt->execute([$loanId, $_SESSION['user_id']]);
    $loan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$loan) {
        $conn->rollBack();
        sendResponse(false, 'Loan not found', null, 404);
    }

    $outstanding = (float)$loan['outstanding'];
    if ($amount > $outstanding) {
        $conn->rollBack();
        sendResponse(false, 'Payment amount exceeds outstanding balance', null, 400);
    }

    // Lock account row and check balance
    $acctStmt = $conn->prepare("SELECT * FROM accounts WHERE account_id = ? AND user_id = ? FOR UPDATE");
    $acctStmt->execute([$accountId, $_SESSION['user_id']]);
    $account = $acctStmt->fetch(PDO::FETCH_ASSOC);

    if (!$account) {
        $conn->rollBack();
        sendResponse(false, 'Account not found or not owned by user', null, 404);
    }

    $balance = (float)$account['balance'];
    if ($balance < $amount) {
        $conn->rollBack();
        sendResponse(false, 'Insufficient funds in selected account', null, 400);
    }

    // Insert transaction (payment). Triggers will update account balance when transaction is inserted with status completed.
    $txStmt = $conn->prepare("INSERT INTO transactions (account_id, transaction_type, amount, description, category, status, transaction_date, created_at, recipient_name, recipient_account) VALUES (?, 'payment', ?, ?, ?, 'completed', NOW(), NOW(), ?, ?)");
    $description = 'Loan payment for loan #' . $loanId;
    $txStmt->execute([$accountId, number_format($amount, 2, '.', ''), $description, 'loan_payment', null, null]);

    $transactionId = $conn->lastInsertId();

    // Record loan payment
    $lpStmt = $conn->prepare("INSERT INTO loan_payments (loan_id, user_id, account_id, transaction_id, amount) VALUES (?, ?, ?, ?, ?)");
    $lpStmt->execute([$loanId, $_SESSION['user_id'], $accountId, $transactionId, number_format($amount, 2, '.', '')]);

    // Update loan outstanding
    $newOutstanding = $outstanding - $amount;
    $status = $newOutstanding <= 0.009 ? 'paid' : $loan['status'];
    $updateStmt = $conn->prepare("UPDATE loans SET outstanding = ?, status = ?, updated_at = NOW() WHERE loan_id = ?");
    $updateStmt->execute([number_format($newOutstanding, 2, '.', ''), $status, $loanId]);

    // Audit & notification
    logAudit($conn, $_SESSION['user_id'], 'loan_payment', 'loan_payments', null, null, ['loan_id' => $loanId, 'amount' => $amount]);
    createNotification($conn, $_SESSION['user_id'], 'Loan payment received', "We received your payment of $".number_format($amount,2,'.',',')." for loan #$loanId", 'loan');

    $conn->commit();

    sendResponse(true, 'Payment processed', ['transaction_id' => $transactionId, 'new_outstanding' => (float)$newOutstanding]);

} catch (Exception $e) {
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('Error in pay_loan.php: ' . $e->getMessage());
    sendResponse(false, 'An error occurred while processing payment');
}
