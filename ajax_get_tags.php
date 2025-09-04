<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

require_once 'db_connect.php';
require_once 'functions.php';

$user_id = $_SESSION['id'];

try {
    $tags = get_user_tags($conn, $user_id);
    echo json_encode(['success' => true, 'tags' => $tags]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>