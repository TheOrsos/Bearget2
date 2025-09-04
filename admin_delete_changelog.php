<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["id"] != 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorizzato.']);
    exit;
}

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID mancante.']);
    exit;
}

$sql = "DELETE FROM changelog_updates WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Aggiornamento eliminato con successo.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore durante l\'eliminazione.']);
}

$stmt->close();
$conn->close();
?>