<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php?message=You need to be logged in.&type=error");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['receiver_id'], $_POST['amount'], $_POST['from_account_id'])) {

    $sender_id = $_SESSION['id'];
    $receiver_id = (int)$_POST['receiver_id'];
    $amount = (float)$_POST['amount'];
    $from_account_id = (int)$_POST['from_account_id'];

    // --- Validation ---

    // 1. Validate amount
    if ($amount <= 0) {
        header("location: friends.php?message=Transfer amount must be positive.&type=error");
        exit;
    }

    // 2. Check if they are friends
    $sql_check_friendship = "SELECT id FROM friendships WHERE status = 'accepted' AND ((user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?))";
    $stmt_check = $conn->prepare($sql_check_friendship);
    $stmt_check->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows == 0) {
        header("location: friends.php?message=You can only send money to your friends.&type=error");
        exit;
    }
    $stmt_check->close();

    // 3. Verify account ownership
    $account = get_account_by_id($conn, $from_account_id, $sender_id);
    if (!$account) {
        header("location: friends.php?message=Invalid source account selected.&type=error");
        exit;
    }

    // 4. Check for sufficient funds
    $account_balance = get_account_balance($conn, $from_account_id);
    if ($account_balance < $amount) {
        header("location: friends.php?message=Insufficient funds in the selected account.&type=error");
        exit;
    }

    // --- End Validation ---

    // Insert the money transfer request
    $sql_insert = "INSERT INTO money_transfers (sender_id, receiver_id, from_account_id, amount, status) VALUES (?, ?, ?, ?, 'pending')";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiid", $sender_id, $receiver_id, $from_account_id, $amount);

    if ($stmt_insert->execute()) {
        $transfer_id = $stmt_insert->insert_id;
        $sender_user = get_user_by_id($conn, $sender_id);

        // Create a notification for the receiver
        $formatted_amount = number_format($amount, 2, ',', '.');
        $notification_message = htmlspecialchars($sender_user['username']) . " wants to send you €{$formatted_amount}.";
        create_notification($conn, $receiver_id, 'money_transfer_request', $notification_message, $transfer_id);

        header("location: friends.php?message=Money transfer request sent successfully.&type=success");
    } else {
        header("location: friends.php?message=An error occurred while sending the transfer request.&type=error");
    }

    $stmt_insert->close();
    $conn->close();

} else {
    header("location: friends.php");
    exit;
}
?>