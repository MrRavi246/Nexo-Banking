<?php
// Start output buffering to prevent any output before JSON
ob_start();

require_once 'config.php';
require_once 'functions.php';

// Clear any output that might have occurred
ob_clean();

header('Content-Type: application/json');

// Check if user is logged in
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

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $requiredFields = ['fromAccount', 'amount', 'transferMethod', 'recipientType'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            sendResponse(false, "Missing required field: $field");
        }
    }

    $fromAccountId = $data['fromAccount'];
    $amount = floatval($data['amount']);
    $transferMethod = $data['transferMethod']; // internal, external, wire
    $recipientType = $data['recipientType']; // contact, new
    $transferDate = $data['transferDate'] ?? date('Y-m-d');
    $memo = $data['memo'] ?? '';
    $securityMethod = $data['securityMethod'] ?? 'sms';

    // Validate amount
    if ($amount <= 0) {
        sendResponse(false, 'Invalid transfer amount');
    }

    // Calculate transfer fee
    $transferFee = 0;
    switch ($transferMethod) {
        case 'internal':
            $transferFee = 0;
            break;
        case 'external':
            $transferFee = 2.99;
            break;
        case 'wire':
            $transferFee = 15.00;
            break;
        default:
            sendResponse(false, 'Invalid transfer method');
    }

    $totalAmount = $amount + $transferFee;

    // Verify source account belongs to user and has sufficient balance
    $stmt = $conn->prepare("
        SELECT account_id, account_type, account_number, balance 
        FROM accounts 
        WHERE account_id = ? AND user_id = ? AND status = 'active'
    ");
    $stmt->execute([$fromAccountId, $userId]);
    $sourceAccount = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sourceAccount) {
        sendResponse(false, 'Invalid source account');
    }

    if ($sourceAccount['balance'] < $totalAmount) {
        sendResponse(false, 'Insufficient funds in source account');
    }

    // Get recipient information
    $recipientName = '';
    $recipientAccount = '';
    $recipientBank = '';
    $recipientEmail = '';
    $contactId = null; // Initialize contactId variable

    if ($recipientType === 'contact') {
        // Get saved contact information
        if (!isset($data['contactId'])) {
            sendResponse(false, 'Contact ID is required');
        }
        
        $contactId = $data['contactId'];
        
        // Try to get from beneficiaries table
        try {
            $stmt = $conn->prepare("
                SELECT beneficiary_name, account_number, bank_name, email 
                FROM beneficiaries 
                WHERE beneficiary_id = ? AND user_id = ? AND status = 'active'
            ");
            $stmt->execute([$contactId, $userId]);
            $contact = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$contact) {
                sendResponse(false, 'Invalid contact selected');
            }
            
            $recipientName = $contact['beneficiary_name'];
            $recipientAccount = $contact['account_number'];
            $recipientBank = $contact['bank_name'];
            $recipientEmail = $contact['email'];
        } catch (PDOException $e) {
            // If beneficiaries table doesn't exist, check if it's a Nexo internal transfer
            if ($transferMethod === 'internal') {
                // For internal transfers, contactId could be a user_id
                $stmt = $conn->prepare("
                    SELECT user_id, username, email, first_name, last_name 
                    FROM users 
                    WHERE user_id = ?
                ");
                $stmt->execute([$contactId]);
                $contact = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$contact) {
                    sendResponse(false, 'Invalid recipient');
                }
                
                $recipientName = $contact['first_name'] . ' ' . $contact['last_name'];
                $recipientEmail = $contact['email'];
                $recipientBank = 'Nexo Banking';
                
                // Get recipient's primary account
                $stmt = $conn->prepare("
                    SELECT account_number 
                    FROM accounts 
                    WHERE user_id = ? AND account_type = 'checking' AND status = 'active' 
                    LIMIT 1
                ");
                $stmt->execute([$contactId]);
                $recipientAcct = $stmt->fetch(PDO::FETCH_ASSOC);
                $recipientAccount = $recipientAcct['account_number'] ?? '';
            } else {
                sendResponse(false, 'Contact information not found');
            }
        }
    } else {
        // New recipient
        $recipientName = $data['recipientName'] ?? '';
        $recipientAccount = $data['accountNumber'] ?? '';
        $recipientBank = $data['bankName'] ?? '';
        $recipientEmail = $data['recipientEmail'] ?? '';
        $recipientRouting = $data['routingNumber'] ?? '';
        $recipientAccountType = $data['accountType'] ?? 'checking';
        
        if (empty($recipientName) || empty($recipientAccount)) {
            sendResponse(false, 'Recipient name and account number are required');
        }
        
        // Check if recipient account exists in Nexo database (for internal transfers)
        $stmt = $conn->prepare("
            SELECT a.account_id, a.user_id, u.first_name, u.last_name, u.email
            FROM accounts a
            JOIN users u ON a.user_id = u.user_id
            WHERE a.account_number = ? AND a.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$recipientAccount]);
        $nexoRecipient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($nexoRecipient) {
            // This is actually an internal Nexo transfer
            error_log("New recipient account found in Nexo database - converting to internal transfer");
            $contactId = $nexoRecipient['user_id'];
            $transferMethod = 'internal';
            $recipientName = $nexoRecipient['first_name'] . ' ' . $nexoRecipient['last_name'];
            $recipientEmail = $nexoRecipient['email'];
            $recipientBank = 'Nexo Banking';
        }
        
        // Auto-save new recipient to beneficiaries table
        try {
            $stmt = $conn->prepare("
                INSERT INTO beneficiaries (
                    user_id, 
                    beneficiary_name, 
                    account_number, 
                    bank_name, 
                    routing_number,
                    account_type,
                    email, 
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([
                $userId,
                $recipientName,
                $recipientAccount,
                $recipientBank,
                $recipientRouting,
                $recipientAccountType,
                $recipientEmail
            ]);
            error_log("New recipient auto-saved to beneficiaries: $recipientName");
        } catch (PDOException $e) {
            // Beneficiaries table might not exist, log but continue
            error_log("Failed to save beneficiary (table might not exist): " . $e->getMessage());
        }
    }

    // Start database transaction
    $conn->beginTransaction();

    try {
        // 1. Deduct amount from source account
        $stmt = $conn->prepare("
            UPDATE accounts 
            SET balance = balance - ? 
            WHERE account_id = ? AND user_id = ?
        ");
        $result = $stmt->execute([$totalAmount, $fromAccountId, $userId]);
        error_log("Sender balance deducted: " . ($result ? 'success' : 'failed') . " Amount: $totalAmount");

        // 2. Record the transfer transaction
        $stmt = $conn->prepare("
            INSERT INTO transactions (
                account_id, 
                transaction_type, 
                amount, 
                description, 
                category, 
                recipient_name,
                recipient_account,
                status, 
                transaction_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $description = $memo ?: "Transfer to $recipientName";
        $status = ($transferMethod === 'internal') ? 'completed' : 'pending';
        $category = 'transfer';
        
        $stmt->execute([
            $fromAccountId,
            'transfer',
            -$totalAmount, // Negative for outgoing
            $description,
            $category,
            $recipientName,
            $recipientAccount,
            $status,
            $transferDate
        ]);
        
        $transactionId = $conn->lastInsertId();
        error_log("Sender transaction recorded. ID: $transactionId");

        // 3. Record transfer fee if applicable
        if ($transferFee > 0) {
            $stmt = $conn->prepare("
                INSERT INTO transactions (
                    account_id, 
                    transaction_type, 
                    amount, 
                    description, 
                    category, 
                    status, 
                    transaction_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $fromAccountId,
                'fee',
                -$transferFee,
                ucfirst($transferMethod) . ' Transfer Fee',
                'fees',
                'completed',
                $transferDate
            ]);
        }

        // 4. For internal Nexo-to-Nexo transfers, credit recipient's account
        if ($transferMethod === 'internal' && isset($contactId) && $contactId !== null) {
            error_log("Processing internal transfer to contactId: $contactId");
            
            // Get recipient's checking account
            $stmt = $conn->prepare("
                SELECT account_id, user_id 
                FROM accounts 
                WHERE user_id = ? AND account_type = 'checking' AND status = 'active' 
                LIMIT 1
            ");
            $stmt->execute([$contactId]);
            $recipientAcctData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($recipientAcctData) {
                error_log("Found recipient account: " . $recipientAcctData['account_id']);
                
                // Credit recipient's account
                $stmt = $conn->prepare("
                    UPDATE accounts 
                    SET balance = balance + ? 
                    WHERE account_id = ?
                ");
                $result = $stmt->execute([$amount, $recipientAcctData['account_id']]);
                error_log("Recipient balance updated: " . ($result ? 'success' : 'failed'));
                
                // Record incoming transaction for recipient
                $stmt = $conn->prepare("
                    INSERT INTO transactions (
                        account_id, 
                        transaction_type, 
                        amount, 
                        description, 
                        category, 
                        recipient_name,
                        status, 
                        transaction_date
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                // Get sender's name
                $stmt2 = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
                $stmt2->execute([$userId]);
                $sender = $stmt2->fetch(PDO::FETCH_ASSOC);
                $senderName = $sender['first_name'] . ' ' . $sender['last_name'];
                
                $stmt->execute([
                    $recipientAcctData['account_id'],
                    'transfer',
                    $amount, // Positive for incoming
                    $memo ?: "Transfer from $senderName",
                    'transfer',
                    $senderName,
                    'completed',
                    $transferDate
                ]);
                
                error_log("Recipient transaction recorded. ID: " . $conn->lastInsertId());
                
                // Create notification for recipient
                createNotification(
                    $conn,
                    $contactId,
                    'Money Received',
                    "You received $$amount from $senderName",
                    'transfer'
                );
            } else {
                error_log("ERROR: Recipient account not found for user_id: $contactId");
            }
        } else {
            error_log("Not an internal transfer. Method: $transferMethod, ContactId: " . ($contactId ?? 'null'));
        }

        // 5. Create notification for sender
        createNotification(
            $conn,
            $userId,
            'Transfer Initiated',
            "Transfer of $$amount to $recipientName has been " . ($status === 'completed' ? 'completed' : 'initiated'),
            'transfer'
        );

        // Commit transaction
        $conn->commit();

        // Prepare response
        $responseData = [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'fee' => $transferFee,
            'total' => $totalAmount,
            'recipient' => $recipientName,
            'status' => $status,
            'date' => $transferDate,
            'new_balance' => $sourceAccount['balance'] - $totalAmount
        ];

        sendResponse(true, 'Transfer processed successfully', $responseData);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error in process_transfer.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    sendResponse(false, 'An error occurred while processing the transfer: ' . $e->getMessage());
}
