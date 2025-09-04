<?php
header('Content-Type: application/json');
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

$response = ['status' => 'error', 'message' => 'An unknown error occurred.', 'messages' => []];

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit;
}

if (isset($_GET['friend_id'])) {
    $user_id = $_SESSION['id'];
    $friend_id = (int)$_GET['friend_id'];

    // --- Fetch messages ---
    $sql_fetch = "SELECT cm.sender_id, cm.message, cm.created_at, u.profile_picture_path
                  FROM chat_messages cm
                  JOIN users u ON cm.sender_id = u.id
                  WHERE (cm.sender_id = ? AND cm.receiver_id = ?) OR (cm.sender_id = ? AND cm.receiver_id = ?)
                  ORDER BY cm.created_at ASC";

    $stmt_fetch = $conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt_fetch->close();

    // --- Mark messages as read ---
    $sql_mark_read = "UPDATE chat_messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
    $stmt_mark_read = $conn->prepare($sql_mark_read);
    $stmt_mark_read->bind_param("ii", $friend_id, $user_id);
    $stmt_mark_read->execute();
    $stmt_mark_read->close();

    $response['status'] = 'success';
    $response['message'] = 'Messages fetched.';
    $response['messages'] = $messages;

} else {
    $response['message'] = 'Friend ID not provided.';
}

$conn->close();
echo json_encode($response);
?>