<?php
header('Content-Type: application/json');
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['receiver_id']) && isset($_POST['message'])) {
    $sender_id = $_SESSION['id'];
    $receiver_id = (int)$_POST['receiver_id'];
    $message = trim($_POST['message']);

    if (empty($message)) {
        $response['message'] = 'Message cannot be empty.';
        echo json_encode($response);
        exit;
    }

    // Security check: ensure the receiver is a friend
    $sql_check_friendship = "SELECT id FROM friendships WHERE status = 'accepted' AND ((user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?))";
    $stmt_check = $conn->prepare($sql_check_friendship);
    $stmt_check->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows == 0) {
        $response['message'] = 'You can only send messages to friends.';
        echo json_encode($response);
        exit;
    }
    $stmt_check->close();

    // Insert the message
    $sql_insert = "INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iis", $sender_id, $receiver_id, $message);

    if ($stmt_insert->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Message sent.';
    } else {
        $response['message'] = 'Failed to send message.';
    }

    $stmt_insert->close();
} else {
    $response['message'] = 'Invalid request method or missing parameters.';
}

$conn->close();
echo json_encode($response);
?>