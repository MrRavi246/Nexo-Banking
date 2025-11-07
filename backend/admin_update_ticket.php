<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$conn = getDBConnection();

if (!isAdminLoggedIn()) {
    sendResponse(false, 'Not authenticated (admin)', null, 401);
}
if (!validateAdminSession($conn, $_SESSION['admin_id'], $_SESSION['admin_session_token'])) {
    sendResponse(false, 'Invalid admin session', null, 401);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) sendResponse(false, 'Invalid JSON', null, 400);

$ticketId = isset($input['ticket_id']) ? intval($input['ticket_id']) : null;
$status = isset($input['status']) ? trim($input['status']) : null;
$assignedTo = isset($input['assigned_to']) ? intval($input['assigned_to']) : null;
$reply = isset($input['reply']) ? trim($input['reply']) : null;

if (!$ticketId) sendResponse(false, 'ticket_id required', null, 400);

try {
    $sets = [];
    $params = [];
    if ($status !== null) { $sets[] = 'status = ?'; $params[] = $status; }
    if ($assignedTo !== null) { $sets[] = 'assigned_to = ?'; $params[] = $assignedTo; }
    if (count($sets) > 0) {
        $sql = 'UPDATE support_tickets SET ' . implode(', ', $sets) . ', updated_at = NOW() WHERE ticket_id = ?';
        $params[] = $ticketId;
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
    }

    // Optionally insert a reply
    if ($reply !== null && $reply !== '') {
        $rstmt = $conn->prepare("INSERT INTO ticket_replies (ticket_id, admin_id, message) VALUES (?, ?, ?)");
        $rstmt->execute([$ticketId, $_SESSION['admin_id'], $reply]);
    }

    // Return updated ticket
    $sel = $conn->prepare("SELECT t.ticket_id, t.user_id, t.subject, t.message, t.status, t.assigned_to, t.created_at, t.updated_at, u.email, u.first_name, u.last_name
        FROM support_tickets t LEFT JOIN users u ON u.user_id = t.user_id WHERE t.ticket_id = ?");
    $sel->execute([$ticketId]);
    $row = $sel->fetch(PDO::FETCH_ASSOC);

    // Log audit
    logAudit($conn, $_SESSION['admin_id'], 'TICKET_UPDATED', 'support_tickets', $ticketId, null, $row);

    sendResponse(true, 'Ticket updated', $row);
} catch (Exception $e) {
    error_log('admin_update_ticket error: ' . $e->getMessage());
    sendResponse(false, 'Failed to update ticket', null, 500);
}

?>
