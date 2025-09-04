<?php
// Inizializza la sessione per poter accedere a $_SESSION e al cookie.
session_start();

// Includi la connessione al database.
require_once 'db_connect.php';

// --- LOGICA PER RIMUOVERE IL TOKEN "RICORDAMI" ---
if (isset($_COOKIE['remember_me_token'])) {
    $token = $_COOKIE['remember_me_token'];

    // 1. Rimuovi il token dal database
    if (str_contains($token, ':')) {
        list($selector, ) = explode(':', $token, 2);
        if ($selector) {
            $sql = "DELETE FROM auth_tokens WHERE selector = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $selector);
            $stmt->execute();
            $stmt->close();
        }
    }

    // 2. Cancella il cookie dal browser
    // Impostando una data di scadenza nel passato, il browser lo eliminerà.
    setcookie('remember_me_token', '', time() - 3600, '/');
}
// --- FINE LOGICA TOKEN ---

// Unset di tutte le variabili di sessione.
$_SESSION = array();

// Distrugge la sessione.
session_destroy();

// Reindirizza alla pagina di login.
header("location: index.php");
exit;
?>