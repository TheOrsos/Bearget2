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
$version = $data['version'];
$title = $data['title'];
$description = $data['description'];
$image_url = $data['image_url'];
$content = $data['content'];
$is_published = isset($data['is_published']) ? 1 : 0;

if (empty($version) || empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Versione, titolo e contenuto sono obbligatori.']);
    exit;
}

if ($id) {
    // Update
    $sql = "UPDATE changelog_updates SET version = ?, title = ?, description = ?, image_url = ?, content = ?, is_published = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssii", $version, $title, $description, $image_url, $content, $is_published, $id);
} else {
    // Insert
    $sql = "INSERT INTO changelog_updates (version, title, description, image_url, content, is_published) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $version, $title, $description, $image_url, $content, $is_published);
}

if ($stmt->execute()) {
    $new_id = $id ? $id : $conn->insert_id;
    echo json_encode(['success' => true, 'message' => 'Aggiornamento salvato con successo.', 'new_id' => $new_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore nel salvataggio dell\'aggiornamento.']);
}

$stmt->close();
$conn->close();
?>