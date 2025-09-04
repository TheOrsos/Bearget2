<?php
session_start();
require_once 'db_connect.php';

// Sicurezza: solo l'admin (ID 1) può eseguire questa operazione.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["id"] != 1) {
    exit("Accesso non autorizzato.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id_to_update = $_POST['user_id'];
    $action = $_POST['action'];
    
    if (empty($user_id_to_update) || empty($action)) {
        header("location: admin.php?message=Dati mancanti.&type=error");
        exit;
    }

    if ($action == 'suspend') {
        $suspended_until_date = !empty($_POST['suspended_until']) ? $_POST['suspended_until'] : null;

        $sql = "UPDATE users SET account_status = 'suspended', suspended_until = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $suspended_until_date, $user_id_to_update);
            if ($stmt->execute()) {
                header("location: admin.php?message=Utente sospeso con successo.&type=success");
            } else {
                header("location: admin.php?message=Errore durante la sospensione.&type=error");
            }
            $stmt->close();
        }
    } elseif ($action == 'reactivate') {
        $sql = "UPDATE users SET account_status = 'active', suspended_until = NULL WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $user_id_to_update);
            if ($stmt->execute()) {
                header("location: admin.php?message=Utente riattivato con successo.&type=success");
            } else {
                header("location: admin.php?message=Errore durante la riattivazione.&type=error");
            }
            $stmt->close();
        }
    } else {
        header("location: admin.php?message=Azione non valida.&type=error");
    }
    
    $conn->close();
    exit();
} else {
    header("location: admin.php");
    exit();
}
?>