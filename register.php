<?php
/*
================================================================================
File: register.php
Descrizione: Gestisce la logica di registrazione dell'utente,
             inviando un'email di verifica tramite un template Brevo.
================================================================================
*/
session_start();
require_once 'db_connect.php';

use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;
use Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // --- Validazione Server-Side ---
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        redirect_with_message("Tutti i campi sono obbligatori.", "error");
    }
    if ($password !== $confirm_password) {
        redirect_with_message("Le password non coincidono.", "error");
    }
    if (strlen($password) < 8) {
        redirect_with_message("La password deve essere di almeno 8 caratteri.", "error");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect_with_message("Formato email non valido.", "error");
    }
    // --- Fine Validazione ---

    $sql_check = "SELECT id FROM users WHERE email = ?";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            redirect_with_message("Un account con questa email esiste già.", "error");
        }
        $stmt_check->close();
    }

    $sql_insert = "INSERT INTO users (username, email, password, verification_token, friend_code) VALUES (?, ?, ?, ?, ?)";
    if ($stmt_insert = $conn->prepare($sql_insert)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $verification_token = bin2hex(random_bytes(32));
        
        do {
            $friend_code = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 8);
            $sql_check_code = "SELECT id FROM users WHERE friend_code = ?";
            $stmt_check_code = $conn->prepare($sql_check_code);
            $stmt_check_code->bind_param("s", $friend_code);
            $stmt_check_code->execute();
            $stmt_check_code->store_result();
        } while ($stmt_check_code->num_rows > 0);
        $stmt_check_code->close();

        $stmt_insert->bind_param("sssss", $username, $email, $hashed_password, $verification_token, $friend_code);

        if ($stmt_insert->execute()) {
            $new_user_id = $stmt_insert->insert_id;

            $sql_account = "INSERT INTO accounts (user_id, name, initial_balance) VALUES (?, 'Conto Principale', 0.00)";
            $stmt_account = $conn->prepare($sql_account);
            $stmt_account->bind_param("i", $new_user_id);
            $stmt_account->execute();
            $stmt_account->close();
            $default_categories = [['Stipendio', 'income', '💼'], ['Altre Entrate', 'income', '💰'], ['Spesa', 'expense', '🛒'], ['Trasporti', 'expense', '⛽️'], ['Casa', 'expense', '🏠'], ['Bollette', 'expense', '🧾'], ['Svago', 'expense', '🎉'], ['Ristoranti', 'expense', '🍔'], ['Salute', 'expense', '❤️‍🩹'], ['Regali', 'expense', '🎁'], ['Risparmi', 'expense', '💾'], ['Fondi Comuni', 'expense', '👥'], ['Trasferimento', 'expense', '🔄']];
            $sql_category = "INSERT INTO categories (user_id, name, type, icon) VALUES (?, ?, ?, ?)";
            $stmt_category = $conn->prepare($sql_category);
            foreach ($default_categories as $cat) {
                $stmt_category->bind_param("isss", $new_user_id, $cat[0], $cat[1], $cat[2]);
                $stmt_category->execute();
            }
            $stmt_category->close();

            $brevo_config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $_ENV['BREVO_API_KEY']);
            $apiInstance = new TransactionalEmailsApi(new Client(), $brevo_config);

            $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $verification_link = "{$scheme}://{$host}/verify.php?token={$verification_token}";
            
            $sendSmtpEmail = new SendSmtpEmail([
                 'templateId' => 6,
                 'to' => [['name' => $username, 'email' => $email]],
                 'params' => (object)[
                     'username' => $username,
                     'verification_link' => $verification_link
                 ]
            ]);

            try {
                $apiInstance->sendTransacEmail($sendSmtpEmail);
                $success_message = "Registrazione completata! Ti abbiamo inviato un'email per verificare il tuo account.";
                redirect_with_message($success_message, "success");
            } catch (Exception $e) {
                $error_message = "Registrazione avvenuta, ma non è stato possibile inviare l'email di verifica. Contatta il supporto. Errore: " . $e->getMessage();
                redirect_with_message($error_message, "error");
            }

        } else {
            redirect_with_message("Oops! Qualcosa è andato storto. Riprova più tardi.", "error");
        }
        $stmt_insert->close();
    }
    $conn->close();
}

function redirect_with_message($message, $type, $token = null) {
    $url = "index.php?message=" . urlencode($message) . "&type=" . $type . "&action=register";
    if ($token) {
        $url .= "&token=" . $token;
    }
    header("Location: " . $url);
    exit();
}
?>