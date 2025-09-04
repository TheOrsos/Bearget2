<?php
// admin_user_actions.php
require_once 'db_connect.php';
session_start();

// Security check: ensure the user is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["id"] != 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Get the raw POST data
$input = json_decode(file_get_contents('php://input'), true);

$action = $input['action'] ?? null;
$userIds = $input['userIds'] ?? [];

// Validate input
if (!$action || empty($userIds) || !is_array($userIds)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// Ensure user IDs are integers to prevent SQL injection
$sanitizedUserIds = array_map('intval', $userIds);
// Prevent the admin from performing actions on their own account (ID 1)
$sanitizedUserIds = array_filter($sanitizedUserIds, function($id) {
    return $id !== 1;
});

if (empty($sanitizedUserIds)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No valid users selected.']);
    exit;
}

$placeholders = implode(',', array_fill(0, count($sanitizedUserIds), '?'));
$types = str_repeat('i', count($sanitizedUserIds));

$conn->begin_transaction();

try {
    switch ($action) {
        case 'suspend':
            $stmt = $conn->prepare("UPDATE users SET account_status = 'suspended' WHERE id IN ($placeholders)");
            $stmt->bind_param($types, ...$sanitizedUserIds);
            break;
        case 'reactivate':
            $stmt = $conn->prepare("UPDATE users SET account_status = 'active', suspended_until = NULL WHERE id IN ($placeholders)");
            $stmt->bind_param($types, ...$sanitizedUserIds);
            break;
        case 'delete':
            // Be very careful with delete operations.
            // You might want to add more checks or a soft delete mechanism.
            $stmt = $conn->prepare("DELETE FROM users WHERE id IN ($placeholders)");
            $stmt->bind_param($types, ...$sanitizedUserIds);
            break;
        case 'disable_emails':
            $stmt = $conn->prepare("UPDATE users SET receives_emails = 0 WHERE id IN ($placeholders)");
            $stmt->bind_param($types, ...$sanitizedUserIds);
            break;
        case 'enable_emails':
            $stmt = $conn->prepare("UPDATE users SET receives_emails = 1 WHERE id IN ($placeholders)");
            $stmt->bind_param($types, ...$sanitizedUserIds);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            $conn->rollback();
            exit;
    }

    $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => "Azione '{$action}' eseguita con successo su {$affected_rows} utenti."]);

} catch (Exception $e) {
    $conn->rollback();
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore durante l\'operazione: ' . $e->getMessage()]);
}

$conn->close();
?>
