<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}
require_once 'db_connect.php';
require_once 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $notification_id = $_POST['notification_id'];
    $action = $_POST['action'];
    $user_id = $_SESSION['id'];

    $conn->begin_transaction();

    try {
        // Get notification details
        $stmt = $conn->prepare("SELECT * FROM notifications WHERE id = ? AND user_id = ? AND type = 'expense_approval'");
        $stmt->bind_param("ii", $notification_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notification = $result->fetch_assoc();
        $stmt->close();

        if (!$notification) {
            throw new Exception("Notifica non trovata o non autorizzata.");
        }

        if ($action == 'decline') {
            // Just delete the notification
            $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
            $stmt->bind_param("i", $notification_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            header("location: notifications.php?message=Spesa rifiutata.&type=success");
            exit;
        }

        // Action is 'approve'
        $account_id = $_POST['account_id'];
        if (empty($account_id)) {
            throw new Exception("Devi selezionare un conto per approvare la spesa.");
        }

        $data = json_decode($notification['message'], true);

        // All data is in the $data array
        $fund_id = $data['fund_id'];
        $description = $data['description'];
        $amount = $data['amount'];
        $expense_date = $data['expense_date'];
        $category_id = $data['category_id'];
        $note_content = $data['note_content'];
        $splits = $data['splits'];
        $paid_by_user_id = $data['payer_id'];
        $creator_id = $data['creator_id'];

        // --- DB OPERATIONS (copied from add_expense.php) ---

        $note_id = null;
        if (!empty($note_content)) {
            $note_id = create_and_share_note_with_fund_members($conn, $note_content, $creator_id, $fund_id);
        }

        $fund_details = get_shared_fund_details($conn, $fund_id, $user_id);
        $sql_personal_tx = "INSERT INTO transactions (user_id, account_id, category_id, amount, type, description, transaction_date) VALUES (?, ?, ?, ?, 'expense', ?, ?)";
        $stmt_personal_tx = $conn->prepare($sql_personal_tx);
        $personal_tx_amount = -$amount;
        $personal_tx_desc = "Spesa di gruppo '{$fund_details['name']}': {$description}";
        $stmt_personal_tx->bind_param("iiidss", $paid_by_user_id, $account_id, $category_id, $personal_tx_amount, $personal_tx_desc, $expense_date);
        $stmt_personal_tx->execute();
        $stmt_personal_tx->close();

        $sql_expense = "INSERT INTO group_expenses (fund_id, paid_by_user_id, description, amount, expense_date, category_id, note_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_expense = $conn->prepare($sql_expense);
        $stmt_expense->bind_param("iisdsii", $fund_id, $paid_by_user_id, $description, $amount, $expense_date, $category_id, $note_id);
        $stmt_expense->execute();
        $expense_id = $stmt_expense->insert_id;
        $stmt_expense->close();

        $sql_split = "INSERT INTO expense_splits (expense_id, user_id, amount_owed) VALUES (?, ?, ?)";
        $stmt_split = $conn->prepare($sql_split);
        foreach ($splits as $uid => $amount_owed) {
            $stmt_split->bind_param("iid", $expense_id, $uid, $amount_owed);
            $stmt_split->execute();
        }
        $stmt_split->close();
        
        // Delete the notification
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->bind_param("i", $notification_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        header("location: fund_details.php?id=" . $fund_id . "&message=Spesa approvata e aggiunta con successo!&type=success");

    } catch (Exception $e) {
        $conn->rollback();
        header("location: notifications.php?message=Errore: " . urlencode($e->getMessage()) . "&type=error");
    } finally {
        if (isset($conn)) $conn->close();
    }
} else {
    header("location: notifications.php");
    exit;
}
?>
