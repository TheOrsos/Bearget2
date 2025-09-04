<?php
// File: edit_contribution.php (Versione con goal_id)
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso non autorizzato.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["id"];
    $transaction_id = $_POST['transaction_id'] ?? null;
    $new_amount = $_POST['amount'] ?? null;
    $new_date = $_POST['date'] ?? null;

    if (empty($transaction_id) || !is_numeric($new_amount) || $new_amount <= 0 || empty($new_date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dati non validi o mancanti.']);
        exit();
    }

    $conn->begin_transaction();
    try {
        // 1. Ottieni i dettagli della transazione originale
        $tx = get_transaction_by_id($conn, $transaction_id, $user_id);
        if (!$tx || empty($tx['goal_id'])) {
            throw new Exception("Contributo non valido o non trovato.");
        }
        $old_amount = abs($tx['amount']);
        $goal_id = $tx['goal_id'];

        // 2. Calcola la differenza e aggiorna l'obiettivo
        $amount_difference = $new_amount - $old_amount;
        $sql_update_goal = "UPDATE saving_goals SET current_amount = current_amount + ? WHERE id = ? AND user_id = ?";
        $stmt_update_goal = $conn->prepare($sql_update_goal);
        $stmt_update_goal->bind_param("dii", $amount_difference, $goal_id, $user_id);
        $stmt_update_goal->execute();
        $stmt_update_goal->close();

        // 3. Aggiorna la transazione
        $new_negative_amount = -abs($new_amount);
        $sql_update_tx = "UPDATE transactions SET amount = ?, transaction_date = ? WHERE id = ? AND user_id = ?";
        $stmt_update_tx = $conn->prepare($sql_update_tx);
        $stmt_update_tx->bind_param("dsii", $new_negative_amount, $new_date, $transaction_id, $user_id);
        $stmt_update_tx->execute();
        $stmt_update_tx->close();

        $conn->commit();

        // 4. Recupera i dati aggiornati per l'UI
        $updated_goal = get_goal_by_id($conn, $goal_id, $user_id);
        $updated_tx_raw = get_transaction_by_id($conn, $transaction_id, $user_id);
        $account_name = get_account_by_id($conn, $updated_tx_raw['account_id'], $user_id)['name'];
        $updated_tx = array_merge($updated_tx_raw, ['account_name' => $account_name]);

        echo json_encode([
            'success' => true,
            'message' => 'Contributo aggiornato!',
            'goal' => [
                'id' => $goal_id,
                'current_amount' => floatval($updated_goal['current_amount'])
            ],
            'contribution' => $updated_tx
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $conn->close();
    exit();
}
?>