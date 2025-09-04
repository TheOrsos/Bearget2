<?php
// File: delete_fund_contribution.php
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
    $contribution_id = $_POST['contribution_id'] ?? null;
    $restore_balance = $_POST['restore_balance'] ?? 'no'; // Default to not restoring

    if (empty($contribution_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID contributo mancante.']);
        exit();
    }

    $conn->begin_transaction();
    try {
        // 1. Ottieni i dettagli del contributo e verifica i permessi
        $sql_get_contrib = "SELECT sfc.user_id as contributor_id, sfc.transaction_id, sfc.fund_id, sf.creator_id
                            FROM shared_fund_contributions sfc
                            JOIN shared_funds sf ON sfc.fund_id = sf.id
                            WHERE sfc.id = ?";
        $stmt_get = $conn->prepare($sql_get_contrib);
        $stmt_get->bind_param("i", $contribution_id);
        $stmt_get->execute();
        $result = $stmt_get->get_result();
        $contribution = $result->fetch_assoc();
        $stmt_get->close();

        if (!$contribution) {
            throw new Exception("Contributo non trovato.");
        }

        // Verifica se l'utente è il creatore del fondo o l'utente che ha fatto il contributo
        if ($user_id != $contribution['contributor_id'] && $user_id != $contribution['creator_id']) {
            throw new Exception("Non hai i permessi per eliminare questo contributo.");
        }

        $transaction_id = $contribution['transaction_id'];

        // 2. Elimina il contributo dalla tabella dei contributi
        $sql_delete_contrib = "DELETE FROM shared_fund_contributions WHERE id = ?";
        $stmt_delete_contrib = $conn->prepare($sql_delete_contrib);
        $stmt_delete_contrib->bind_param("i", $contribution_id);
        $stmt_delete_contrib->execute();
        $stmt_delete_contrib->close();

        // 3. Se richiesto, elimina la transazione associata per ripristinare il saldo
        if ($restore_balance === 'yes' && !empty($transaction_id)) {
            $contributor_id = $contribution['contributor_id'];

            // Elimina la transazione associata
            $sql_delete_tx = "DELETE FROM transactions WHERE id = ? AND user_id = ?";
            $stmt_delete_tx = $conn->prepare($sql_delete_tx);
            $stmt_delete_tx->bind_param("ii", $transaction_id, $contributor_id);
            $stmt_delete_tx->execute();

            if ($stmt_delete_tx->affected_rows == 0) {
                 // Questo potrebbe succedere se la transazione è già stata rimossa o l'ID utente non corrisponde.
                 // Non blocchiamo l'operazione, ma in un'applicazione reale si potrebbe loggare un avviso.
            }
            $stmt_delete_tx->close();
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Contributo eliminato con successo!',
            'contribution_id' => $contribution_id
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