<?php
require_once 'config.php';
require_once 'functions.php';

if (!isAdminLoggedIn()) {
    sendResponse(false, 'Admin not authenticated', null, 401);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method', null, 405);
    }

    $conn = getDBConnection();

    if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
        sendResponse(false, 'Invalid admin session', null, 401);
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) $data = $_POST;

    $loanId = isset($data['loan_id']) ? intval($data['loan_id']) : 0;
    $action = isset($data['action']) ? sanitizeInput($data['action']) : null; // 'approve' or 'reject'
    $adminNote = isset($data['note']) ? sanitizeInput($data['note']) : null;
    $disburseAccountId = isset($data['disburse_account_id']) ? intval($data['disburse_account_id']) : null;

    if ($loanId <= 0 || !in_array($action, ['approve', 'reject'])) {
        sendResponse(false, 'Missing or invalid parameters', null, 400);
    }

    $conn->beginTransaction();

    // Lock the loan
    $stmt = $conn->prepare("SELECT * FROM loans WHERE loan_id = ? FOR UPDATE");
    $stmt->execute([$loanId]);
    $loan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$loan) {
        $conn->rollBack();
        sendResponse(false, 'Loan application not found', null, 404);
    }

    if ($action === 'approve') {
        // Approve: set status=approved initially
        $update = $conn->prepare("UPDATE loans SET status = 'approved', updated_at = NOW() WHERE loan_id = ?");
        $update->execute([$loanId]);

        // Attempt to disburse the loan amount to the borrower's account
        $disbursed = false;
        $transactionId = null;

        // Determine destination account: prefer explicit disburseAccountId if provided, else pick first account
        $destAccountId = null;
        if ($disburseAccountId) {
            // Validate that the provided account belongs to the user
            $validateStmt = $conn->prepare("SELECT account_id FROM accounts WHERE account_id = ? AND user_id = ? FOR UPDATE");
            $validateStmt->execute([$disburseAccountId, $loan['user_id']]);
            $valid = $validateStmt->fetch(PDO::FETCH_ASSOC);
            if ($valid && isset($valid['account_id'])) {
                $destAccountId = $valid['account_id'];
            } else {
                // invalid provided account
                createNotification($conn, $loan['user_id'], 'Loan approved (pending disbursement)', 'Your loan #' . $loanId . ' was approved but the selected disbursement account was invalid. Admin will need to retry.');
            }
        }

        if (!$destAccountId) {
            $acctStmt = $conn->prepare("SELECT account_id FROM accounts WHERE user_id = ? ORDER BY account_id LIMIT 1 FOR UPDATE");
            $acctStmt->execute([$loan['user_id']]);
            $destAcct = $acctStmt->fetch(PDO::FETCH_ASSOC);
            if ($destAcct && isset($destAcct['account_id'])) {
                $destAccountId = $destAcct['account_id'];
            }
        }

        if ($destAccountId) {
            // Insert a deposit transaction to credit the user's account
            $txStmt = $conn->prepare("INSERT INTO transactions (account_id, transaction_type, amount, description, category, status, transaction_date, created_at) VALUES (?, 'deposit', ?, ?, 'loan_disbursement', 'completed', NOW(), NOW())");
            $txStmt->execute([$destAccountId, number_format($loan['principal'], 2, '.', ''), 'Loan disbursement for loan #' . $loanId]);
            $transactionId = $conn->lastInsertId();
            $disbursed = true;

            // Mark loan as active since funds were disbursed
            $updateActive = $conn->prepare("UPDATE loans SET status = 'active', updated_at = NOW(), disbursed_account_id = ?, disbursed_at = NOW() WHERE loan_id = ?");
            // Using prepared statement regardless of whether columns exist; if migration not applied, this will still run but may error. We'll attempt to execute and catch exceptions.
            try {
                $updateActive->execute([$destAccountId, $loanId]);
            } catch (Exception $e) {
                // If columns don't exist, fallback to updating status only
                $updateFallback = $conn->prepare("UPDATE loans SET status = 'active', updated_at = NOW() WHERE loan_id = ?");
                $updateFallback->execute([$loanId]);
            }

            // Notify user of disbursement
            createNotification($conn, $loan['user_id'], 'Loan disbursed', 'Your loan #' . $loanId . ' has been disbursed to your account ending ' . substr((string)$destAccountId, -4));
        } else {
            // No account found to disburse to; leave status as approved and notify admin via response
            createNotification($conn, $loan['user_id'], 'Loan approved (pending disbursement)', 'Your loan #' . $loanId . ' was approved but no active account was found for automatic disbursement.');
        }

        // Log audit for approval (include disbursement result)
        logAudit($conn, $_SESSION['admin_id'], 'approve_loan', 'loans', $loanId, $loan, ['status' => $disbursed ? 'active_disbursed' : 'approved_pending_disbursement', 'note' => $adminNote, 'transaction_id' => $transactionId]);
    } else {
        // Reject
        $update = $conn->prepare("UPDATE loans SET status = 'rejected', updated_at = NOW() WHERE loan_id = ?");
        $update->execute([$loanId]);

        createNotification($conn, $loan['user_id'], 'Loan application rejected', 'Your loan application #' . $loanId . ' has been rejected.');
        logAudit($conn, $_SESSION['admin_id'], 'reject_loan', 'loans', $loanId, $loan, ['status' => 'rejected', 'note' => $adminNote]);
    }

    $conn->commit();

    sendResponse(true, 'Action completed', ['loan_id' => $loanId, 'action' => $action]);

} catch (Exception $e) {
    if (isset($conn) && $conn && $conn->inTransaction()) $conn->rollBack();
    error_log('Error in approve_loan.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    // Return the error message to the admin caller to aid debugging in dev environment
    sendResponse(false, 'An error occurred while performing action: ' . $e->getMessage());
}
