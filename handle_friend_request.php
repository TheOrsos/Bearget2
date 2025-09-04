<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php?message=You need to be logged in.&type=error");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id']) && isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    $current_user_id = $_SESSION['id'];
    $current_user = get_user_by_id($conn, $current_user_id);

    // Get the friendship request
    $sql_get_request = "SELECT * FROM friendships WHERE id = ? AND user_id_2 = ? AND status = 'pending'";
    $stmt_get = $conn->prepare($sql_get_request);
    $stmt_get->bind_param("ii", $request_id, $current_user_id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();

    if ($friend_request = $result->fetch_assoc()) {
        $requester_id = $friend_request['user_id_1'];
        $requester_user = get_user_by_id($conn, $requester_id);

        if ($action == 'accept') {
            // Update friendship status to accepted
            $sql_update = "UPDATE friendships SET status = 'accepted' WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $request_id);
            if ($stmt_update->execute()) {
                // Create a notification for the requester
                $notification_message = htmlspecialchars($current_user['username']) . " has accepted your friend request.";
                create_notification($conn, $requester_id, 'friend_request_accepted', $notification_message, $request_id);

                // Mark the original notification as read
                $sql_read_notif = "UPDATE notifications SET is_read = 1 WHERE type = 'friend_request' AND related_id = ? AND user_id = ?";
                $stmt_read_notif = $conn->prepare($sql_read_notif);
                $stmt_read_notif->bind_param("ii", $request_id, $current_user_id);
                $stmt_read_notif->execute();
                $stmt_read_notif->close();

                header("location: notifications.php?message=Friend request accepted!&type=success");
            } else {
                header("location: notifications.php?message=Error accepting request.&type=error");
            }
            $stmt_update->close();

        } elseif ($action == 'decline') {
            // Update friendship status to declined
            $sql_update = "UPDATE friendships SET status = 'declined' WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $request_id);

            if ($stmt_update->execute()) {
                 // Mark the original notification as read
                $sql_read_notif = "UPDATE notifications SET is_read = 1 WHERE type = 'friend_request' AND related_id = ? AND user_id = ?";
                $stmt_read_notif = $conn->prepare($sql_read_notif);
                $stmt_read_notif->bind_param("ii", $request_id, $current_user_id);
                $stmt_read_notif->execute();
                $stmt_read_notif->close();

                header("location: notifications.php?message=Friend request declined.&type=success");
            } else {
                header("location: notifications.php?message=Error declining request.&type=error");
            }
            $stmt_update->close();

        } else {
            header("location: notifications.php?message=Invalid action.&type=error");
        }
    } else {
        header("location: notifications.php?message=Friend request not found or you are not authorized to respond.&type=error");
    }
    $stmt_get->close();
    $conn->close();
} else {
    header("location: notifications.php");
    exit;
}
?>