<?php
session_start();
require_once 'db_connect.php';

// Security check: ensure the user is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["id"] != 1) {
    header("location: login.php");
    exit;
}

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location: admin.php");
    exit;
}

$user_id_to_toggle = (int)$_GET['id'];

// Prevent admin from changing their own status
if ($user_id_to_toggle === 1) {
    header("location: admin.php");
    exit;
}

// Fetch the current status
$stmt = $conn->prepare("SELECT receives_emails FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id_to_toggle);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user) {
    // Determine the new status
    $new_status = $user['receives_emails'] ? 0 : 1;

    // Update the database
    $update_stmt = $conn->prepare("UPDATE users SET receives_emails = ? WHERE id = ?");
    $update_stmt->bind_param("ii", $new_status, $user_id_to_toggle);
    $update_stmt->execute();
    $update_stmt->close();
}

// Redirect back to the admin page
header("location: admin.php");
exit;

?>
