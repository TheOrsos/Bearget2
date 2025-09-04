<?php
session_start();
require_once 'db_connect.php';
require_once 'friends_functions.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$user_id = $_SESSION['id'];
$friends = get_friends_for_user($conn, $user_id);

header('Content-Type: application/json');
if ($friends !== false) {
    echo json_encode(['success' => true, 'friends' => $friends]);
} else {
    // It's better to return an empty array if there are no friends,
    // rather than an error, unless something actually went wrong with the query.
    // The get_friends_for_user function returns an empty array on no friends, and false on error.
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}
?>