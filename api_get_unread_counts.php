<?php
header('Content-Type: application/json');
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

// This function might not be loaded if the user is on a page that doesn't include it.
if (file_exists('friends_functions.php')) {
    require_once 'friends_functions.php';
} else {
    // If the functions file doesn't exist, we can't proceed.
    echo json_encode(['status' => 'error', 'message' => 'Required functions not found.']);
    exit;
}


$response = [
    'status' => 'error',
    'total_chats' => 0,
    'by_user' => [],
    'notification_count' => 0
];

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['id'];
$unread_counts = get_unread_message_counts($conn, $user_id);
$notification_count = get_unread_notification_count($conn, $user_id);

$response = [
    'status' => 'success',
    'total_chats' => count($unread_counts),
    'by_user' => $unread_counts,
    'notification_count' => $notification_count
];

$conn->close();
echo json_encode($response);
?>