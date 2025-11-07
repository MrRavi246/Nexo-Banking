<?php
/**
 * Process Bill Payment
 * Handles one-time and recurring bill payments
 */

header('Content-Type: application/json');
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

function sendResponse($success, $message, $data = null) {
    http_response_code($success ? 200 : 400);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

try {
    $conn = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    // Validate session
    if (!validateSession($conn, $userId, $_SESSION['session_token'])) {
        sendResponse(false, 'Invalid session');
    }
    
    // Get and validate input data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        sendResponse(false, 'Invalid request data');
    }
    
    // Extract payment details
    $accountId = $data['accountId'] ?? null;
    $billerName = trim($data['billerName'] ?? '');
    $billType = $data['billType'] ?? 'utilities';
    $amount = $data['amount'] ?? 0;
    $paymentDate = $data['paymentDate'] ?? date('Y-m-d');
    $dueDate = $data['dueDate'] ?? null;
    $memo = $data['memo'] ?? '';
    $paymentType = $data['paymentType'] ?? 'one-time'; // one-time or recurring
    $frequency = $data['frequency'] ?? null; // daily, weekly, monthly, etc.
    $endDate = $data['endDate'] ?? null;
    
    // Validation
    if (!$accountId || !$billerName || !$amount || $amount <= 0) {
        sendResponse(false, 'Missing required fields: account, biller name, and amount');
    }
    
    // Verify account belongs to user and has sufficient balance
    $stmt = $conn->prepare("
        SELECT account_id, account_type, account_number, balance 
        FROM accounts 
        WHERE account_id = ? AND user_id = ? AND status = 'active'
    ");
    $stmt->execute([$accountId, $userId]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$account) {
        sendResponse(false, 'Invalid account selected');
    }
    
    if ($account['balance'] < $amount) {
        sendResponse(false, 'Insufficient balance in selected account');
    }
    
    // Validate bill type
    $validBillTypes = ['utilities', 'credit_card', 'loan', 'subscription', 'mobile_recharge', 'insurance'];
    if (!in_array($billType, $validBillTypes)) {
        $billType = 'utilities';
    }
    
    // Start database transaction
    $conn->beginTransaction();
    
    try {
        // Generate reference number
        $referenceNumber = 'BP' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 8));
        
        // Determine payment status
        $paymentDateTime = new DateTime($paymentDate);
        $today = new DateTime();
        
        if ($paymentDateTime->format('Y-m-d') <= $today->format('Y-m-d')) {
            // Pay immediately
            $status = 'processing';
            
            // 1. Deduct from account balance
            $stmt = $conn->prepare("
                UPDATE accounts 
                SET balance = balance - ? 
                WHERE account_id = ?
            ");
            $result = $stmt->execute([$amount, $accountId]);
            
            if (!$result) {
                throw new Exception('Failed to deduct payment amount from account');
            }
            
            error_log("Bill payment deducted: $amount from account $accountId");
            
            // 2. Record transaction
            $stmt = $conn->prepare("
                INSERT INTO transactions (
                    account_id,
                    transaction_type,
                    amount,
                    description,
                    recipient_info,
                    category,
                    reference_id,
                    status,
                    transaction_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $description = "Bill payment to $billerName" . ($memo ? " - $memo" : "");
            
            $stmt->execute([
                $accountId,
                'payment',
                -$amount, // Negative for outgoing
                $description,
                $billerName,
                'bills',
                $referenceNumber,
                'completed',
                date('Y-m-d H:i:s')
            ]);
            
            error_log("Bill payment transaction recorded: $referenceNumber");
            
            // Update status to completed
            $status = 'completed';
            
        } else {
            // Schedule for future
            $status = 'scheduled';
        }
        
        // 3. Insert bill payment record
        $stmt = $conn->prepare("
            INSERT INTO bill_payments (
                user_id,
                account_id,
                biller_name,
                bill_type,
                amount,
                due_date,
                payment_date,
                status,
                reference_number
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $accountId,
            $billerName,
            $billType,
            $amount,
            $dueDate,
            $paymentDate,
            $status,
            $referenceNumber
        ]);
        
        $paymentId = $conn->lastInsertId();
        error_log("Bill payment record created: ID $paymentId");
        
        // 4. Handle recurring payments
        if ($paymentType === 'recurring' && $frequency) {
            // Create future scheduled payments based on frequency
            $schedulePayments = generateRecurringSchedule(
                $paymentDate, 
                $frequency, 
                $endDate,
                12 // Maximum 12 future payments
            );
            
            foreach ($schedulePayments as $scheduledDate) {
                $scheduleRef = 'BP' . date('Ymd', strtotime($scheduledDate)) . strtoupper(substr(md5(uniqid()), 0, 8));
                
                $stmt = $conn->prepare("
                    INSERT INTO bill_payments (
                        user_id,
                        account_id,
                        biller_name,
                        bill_type,
                        amount,
                        due_date,
                        payment_date,
                        status,
                        reference_number
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled', ?)
                ");
                
                $stmt->execute([
                    $userId,
                    $accountId,
                    $billerName,
                    $billType,
                    $amount,
                    $scheduledDate,
                    $scheduledDate,
                    $scheduleRef
                ]);
            }
            
            error_log("Created " . count($schedulePayments) . " recurring payment schedules");
        }
        
        // 5. Create notification
        $notificationMessage = $status === 'completed' 
            ? "Payment of $" . number_format($amount, 2) . " to $billerName has been completed"
            : "Payment of $" . number_format($amount, 2) . " to $billerName has been scheduled for " . date('M d, Y', strtotime($paymentDate));
        
        createNotification($conn, $userId, 'Bill Payment ' . ucfirst($status), $notificationMessage);
        
        // Commit transaction
        $conn->commit();
        
        // Prepare response data
        $responseData = [
            'paymentId' => $paymentId,
            'referenceNumber' => $referenceNumber,
            'status' => $status,
            'billerName' => $billerName,
            'amount' => $amount,
            'paymentDate' => $paymentDate,
            'newBalance' => $status === 'completed' ? ($account['balance'] - $amount) : $account['balance'],
            'recurringCount' => $paymentType === 'recurring' ? count($schedulePayments ?? []) : 0
        ];
        
        $message = $status === 'completed' 
            ? "Payment of $" . number_format($amount, 2) . " to $billerName completed successfully!"
            : "Payment scheduled successfully for " . date('M d, Y', strtotime($paymentDate));
        
        if ($paymentType === 'recurring') {
            $message .= " " . count($schedulePayments ?? []) . " future payments scheduled.";
        }
        
        sendResponse(true, $message, $responseData);
        
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Bill payment processing error: " . $e->getMessage());
        sendResponse(false, 'Payment processing failed: ' . $e->getMessage());
    }
    
} catch (PDOException $e) {
    error_log("Bill payment database error: " . $e->getMessage());
    sendResponse(false, 'Database error occurred');
}

/**
 * Generate recurring payment schedule
 */
function generateRecurringSchedule($startDate, $frequency, $endDate = null, $maxCount = 12) {
    $schedule = [];
    $current = new DateTime($startDate);
    $end = $endDate ? new DateTime($endDate) : null;
    
    for ($i = 0; $i < $maxCount; $i++) {
        // Calculate next payment date based on frequency
        switch ($frequency) {
            case 'daily':
                $current->modify('+1 day');
                break;
            case 'weekly':
                $current->modify('+1 week');
                break;
            case 'bi-weekly':
                $current->modify('+2 weeks');
                break;
            case 'monthly':
                $current->modify('+1 month');
                break;
            case 'quarterly':
                $current->modify('+3 months');
                break;
            case 'yearly':
                $current->modify('+1 year');
                break;
            default:
                return $schedule;
        }
        
        // Check if we've exceeded end date
        if ($end && $current > $end) {
            break;
        }
        
        $schedule[] = $current->format('Y-m-d');
    }
    
    return $schedule;
}
