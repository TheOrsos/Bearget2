<?php
// File: ajax_get_goal_contributions.php (Versione con goal_id)
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso non autorizzato.']);
    exit;
}

$user_id = $_SESSION["id"];
$goal_id = $_GET['goal_id'] ?? null;

if (empty($goal_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID obiettivo mancante.']);
    exit();
}

try {
    // Trova tutte le transazioni collegate a questo goal_id
    $sql = "SELECT
                t.id,
                t.amount,
                t.transaction_date,
                a.name as account_name
            FROM transactions t
            JOIN accounts a ON t.account_id = a.id
            WHERE t.user_id = ?
            AND t.goal_id = ?
            AND t.type = 'expense'
            ORDER BY t.transaction_date DESC, t.id DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $goal_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $contributions = [];
    while ($row = $result->fetch_assoc()) {
        $contributions[] = $row;
    }

    $stmt->close();

    echo json_encode(['success' => true, 'contributions' => $contributions]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()]);
}

$conn->close();
?>