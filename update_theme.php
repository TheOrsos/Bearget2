<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php'; // Required for get_user_by_id

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    exit("Accesso non autorizzato.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["id"];
    $theme = trim($_POST['theme']);

    // Get user's pro status
    $user = get_user_by_id($conn, $user_id);
    $is_pro = ($user['subscription_status'] === 'active' || $user['subscription_status'] === 'lifetime');

    // Define valid and pro themes
    $valid_themes = ['dark-indigo', 'forest-green', 'ocean-blue', 'sunset-orange', 'royal-purple', 'graphite-gray', 'dark-gold', 'modern-dark', 'foggy-gray'];
    $pro_themes = ['dark-gold'];

    // Security check: if the theme is a pro theme, the user must be pro
    if (in_array($theme, $pro_themes) && !$is_pro) {
        header("location: settings.php?message=Devi essere un utente Pro per usare questo tema.&type=error");
        exit();
    }

    if (in_array($theme, $valid_themes)) {
        $sql = "UPDATE users SET theme = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $theme, $user_id);
            if ($stmt->execute()) {
                $_SESSION["theme"] = $theme; // Aggiorna la sessione
                header("location: settings.php?message=Tema aggiornato!&type=success");
            } else {
                header("location: settings.php?message=Errore.&type=error");
            }
            $stmt->close();
        }
    } else {
        header("location: settings.php?message=Tema non valido.&type=error");
    }
    $conn->close();
    exit();
}
?>