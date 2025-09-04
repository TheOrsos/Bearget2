<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php?message=You need to be logged in.&type=error");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['transfer_id']) && isset($_POST['action'])) {
    $transfer_id = (int)$_POST['transfer_id'];
    $action = $_POST['action'];
    $current_user_id = $_SESSION['id'];

    // Get the money transfer request to ensure it's for the current user and is pending
    $sql_get_transfer = "SELECT * FROM money_transfers WHERE id = ? AND receiver_id = ? AND status = 'pending'";
    $stmt_get = $conn->prepare($sql_get_transfer);
    $stmt_get->bind_param("ii", $transfer_id, $current_user_id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();

    if ($transfer = $result->fetch_assoc()) {
        $sender_id = $transfer['sender_id'];
        $sender_user = get_user_by_id($conn, $sender_id);
        $receiver_user = get_user_by_id($conn, $current_user_id);
        $amount = $transfer['amount'];
        $from_account_id = $transfer['from_account_id'];

        if ($action == 'accept') {
            // Find receiver's primary account (or first one)
            $receiver_accounts = get_user_accounts($conn, $current_user_id);
            if (empty($receiver_accounts)) {
                header("location: notifications.php?message=You need to have at least one account to receive money.&type=error");
                exit;
            }
            $to_account_id = $receiver_accounts[0]['id'];

            // Start a transaction
            $conn->begin_transaction();

            try {
                // 1. Debit sender's account
                $debit_desc = "Money sent to " . htmlspecialchars($receiver_user['username']);
                $sql_debit = "INSERT INTO transactions (user_id, account_id, type, amount, description, transaction_date) VALUES (?, ?, 'expense', ?, ?, NOW())";
                $stmt_debit = $conn->prepare($sql_debit);
                $negative_amount = -$amount;
                $stmt_debit->bind_param("iids", $sender_id, $from_account_id, $negative_amount, $debit_desc);
                $stmt_debit->execute();

                // 2. Credit receiver's account
                $credit_desc = "Money received from " . htmlspecialchars($sender_user['username']);
                $sql_credit = "INSERT INTO transactions (user_id, account_id, type, amount, description, transaction_date) VALUES (?, ?, 'income', ?, ?, NOW())";
                $stmt_credit = $conn->prepare($sql_credit);
                $stmt_credit->bind_param("iids", $current_user_id, $to_account_id, $amount, $credit_desc);
                $stmt_credit->execute();

                // 3. Update transfer status
                $sql_update = "UPDATE money_transfers SET status = 'accepted' WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("i", $transfer_id);
                $stmt_update->execute();

                // 4. Mark notification as read
                $sql_read_notif = "UPDATE notifications SET is_read = 1 WHERE type = 'money_transfer_request' AND related_id = ? AND user_id = ?";
                $stmt_read_notif = $conn->prepare($sql_read_notif);
                $stmt_read_notif->bind_param("ii", $transfer_id, $current_user_id);
                $stmt_read_notif->execute();

                // 5. Create notification for sender
                $formatted_amount = number_format($amount, 2, ',', '.');
                $notification_message = htmlspecialchars($receiver_user['username']) . " accepted your transfer of €{$formatted_amount}.";
                create_notification($conn, $sender_id, 'money_transfer_accepted', $notification_message, $transfer_id);

                // If all queries were successful, commit the transaction
                $conn->commit();
                header("location: notifications.php?message=Transfer accepted successfully.&type=success");

            } catch (Exception $e) {
                // An error occurred, rollback the transaction
                $conn->rollback();
                header("location: notifications.php?message=A critical error occurred. The transaction has been cancelled. Error: " . $e->getMessage() . "&type=error");
            }

        } elseif ($action == 'decline') {
            // Just update the status and notify
            $sql_update = "UPDATE money_transfers SET status = 'declined' WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $transfer_id);
            $stmt_update->execute();

            // Mark notification as read
            $sql_read_notif = "UPDATE notifications SET is_read = 1 WHERE type = 'money_transfer_request' AND related_id = ? AND user_id = ?";
            $stmt_read_notif = $conn->prepare($sql_read_notif);
            $stmt_read_notif->bind_param("ii", $transfer_id, $current_user_id);
            $stmt_read_notif->execute();

            // Notify sender
            $notification_message = htmlspecialchars($receiver_user['username']) . " declined your money transfer.";
            create_notification($conn, $sender_id, 'money_transfer_declined', $notification_message, $transfer_id);

            header("location: notifications.php?message=Transfer declined.&type=success");
        } else {
            header("location: notifications.php?message=Invalid action.&type=error");
        }
    } else {
        header("location: notifications.php?message=Transfer not found or you are not authorized to respond.&type=error");
    }
    $stmt_get->close();
    $conn->close();
} else {
    header("location: notifications.php");
    exit;
}
?>