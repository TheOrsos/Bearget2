<?php
// Inizia la sessione se non è già attiva.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connect.php';
require_once 'functions.php';

// Controlla se l'utente non è già loggato tramite la sessione.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    // Se non c'è una sessione, controlla la presenza di un cookie "Ricordami".
    if (isset($_COOKIE['remember_me_token'])) {
        $token = $_COOKIE['remember_me_token'];
        $user = validate_remember_me_token($conn, $token);

        if ($user) {
            // Token valido: l'utente viene loggato.
            log_in_user($user);

            // Best practice: Rota il token. Elimina il vecchio e impostane uno nuovo.
            // Prima elimina il vecchio token dal DB.
            list($selector, ) = explode(':', $token, 2);
            $sql_delete_old = "DELETE FROM auth_tokens WHERE selector = ?";
            $stmt_delete_old = $conn->prepare($sql_delete_old);
            $stmt_delete_old->bind_param("s", $selector);
            $stmt_delete_old->execute();

            // Crea e imposta un nuovo token.
            $new_token_data = remember_user_token($conn, $user['id']);
            if ($new_token_data) {
                 setcookie(
                    'remember_me_token',
                    $new_token_data['cookie_value'],
                    [
                        'expires' => $new_token_data['expires_timestamp'],
                        'path' => '/',
                        'domain' => '',
                        'secure' => isset($_SERVER['HTTPS']),
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]
                );
            }

        } else {
            // Se il token nel cookie non è valido (scaduto o manomesso),
            // è buona norma pulire il cookie dal browser dell'utente.
            setcookie('remember_me_token', '', time() - 3600, '/');
        }
    }
}

// Controllo finale: se dopo tutto questo l'utente non è loggato,
// allora deve essere reindirizzato alla pagina di login.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Aggiungiamo un controllo per evitare loop di reindirizzamento se ci si trova già su index.php
    if (basename($_SERVER['PHP_SELF']) != 'index.php') {
        header("location: index.php");
        exit;
    }
}
?>