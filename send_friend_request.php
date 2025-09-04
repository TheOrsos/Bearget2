<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php?message=You need to be logged in to do that.&type=error");
    exit;
}

$requester_id = $_SESSION['id'];
$requester_user = get_user_by_id($conn, $requester_id);
$requester_username = $requester_user['username'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['friend_code'])) {
    $friend_code = trim($_POST['friend_code']);

    if (empty($friend_code)) {
        header("location: friends.php?message=Friend code cannot be empty.&type=error");
        exit;
    }

    // Find the user by friend code
    $receiver = find_user_by_friend_code($conn, $friend_code);

    if (!$receiver) {
        header("location: friends.php?message=No user found with that friend code.&type=error");
        exit;
    }

    $receiver_id = $receiver['id'];

    // Prevent user from adding themselves
    if ($requester_id == $receiver_id) {
        header("location: friends.php?message=You cannot send a friend request to yourself.&type=error");
        exit;
    }

    // Check if a friendship already exists or is pending
    $sql_check = "SELECT id FROM friendships WHERE (user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?)";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("iiii", $requester_id, $receiver_id, $receiver_id, $requester_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        header("location: friends.php?message=You are already friends with this user or a request is pending.&type=error");
        exit;
    }
    $stmt_check->close();

    // Insert the friend request
    $sql_insert = "INSERT INTO friendships (user_id_1, user_id_2, status) VALUES (?, ?, 'pending')";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ii", $requester_id, $receiver_id);

    if ($stmt_insert->execute()) {
        $friendship_id = $stmt_insert->insert_id;

        // Create a notification for the receiver
        $notification_message = "You have received a friend request from " . htmlspecialchars($requester_username) . ".";
        create_notification($conn, $receiver_id, 'friend_request', $notification_message, $friendship_id);

        header("location: friends.php?message=Friend request sent successfully!&type=success");
    } else {
        header("location: friends.php?message=An error occurred while sending the request.&type=error");
    }

    $stmt_insert->close();
    $conn->close();

} else {
    // Redirect if not a POST request
    header("location: friends.php");
    exit;
}
?>