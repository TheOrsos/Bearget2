<?php
// File: delete_contribution.php (Versione con goal_id)
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

    if (empty($transaction_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID transazione mancante.']);
        exit();
    }

    $conn->begin_transaction();
    try {
        // 1. Ottieni i dettagli della transazione (contributo)
        $tx = get_transaction_by_id($conn, $transaction_id, $user_id);
        if (!$tx || empty($tx['goal_id'])) {
            throw new Exception("Contributo non valido o non trovato.");
        }

        $contribution_amount = abs($tx['amount']);
        $goal_id = $tx['goal_id'];

        // 2. Sottrai l'importo dal totale dell'obiettivo
        $sql_update_goal = "UPDATE saving_goals SET current_amount = current_amount - ? WHERE id = ? AND user_id = ?";
        $stmt_update = $conn->prepare($sql_update_goal);
        $stmt_update->bind_param("dii", $contribution_amount, $goal_id, $user_id);
        $stmt_update->execute();
        $stmt_update->close();

        // 3. Elimina la transazione
        $sql_delete_tx = "DELETE FROM transactions WHERE id = ? AND user_id = ?";
        $stmt_delete = $conn->prepare($sql_delete_tx);
        $stmt_delete->bind_param("ii", $transaction_id, $user_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        // 4. Recupera il nuovo importo corrente dell'obiettivo
        $updated_goal = get_goal_by_id($conn, $goal_id, $user_id);

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Contributo eliminato con successo!',
            'goal' => [
                'id' => $goal_id,
                'current_amount' => floatval($updated_goal['current_amount'])
            ]
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