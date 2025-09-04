<?php
/*
================================================================================
File: admin_send_email.php
Descrizione: Script backend per inviare email transazionali dall'admin panel.
================================================================================
*/

require_once 'db_connect.php';
session_start();

// Usa le classi di Brevo
use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;
use Exception;

// --- Sicurezza: Controlla se l'utente è l'admin (id = 1) ---
if (!isset($_SESSION["id"]) || $_SESSION["id"] != 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Accesso non autorizzato.']);
    exit();
}

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? null;
$email_type = $data['email_type'] ?? null;

if (!$user_id || !$email_type) {
    echo json_encode(['success' => false, 'message' => 'Dati mancanti per l\'invio.']);
    exit();
}

// --- Recupera i dati dell'utente ---
$sql_user = "SELECT username, email, is_verified, verification_token FROM users WHERE id = ? LIMIT 1";
if (!$stmt_user = $conn->prepare($sql_user)) {
    echo json_encode(['success' => false, 'message' => 'Errore preparazione query utente.']);
    exit();
}
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
if ($result_user->num_rows !== 1) {
    echo json_encode(['success' => false, 'message' => 'Utente non trovato.']);
    exit();
}
$user = $result_user->fetch_assoc();
$stmt_user->close();


// Logica di invio basata sul tipo di email
$brevo_config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $_ENV['BREVO_API_KEY']);
$apiInstance = new TransactionalEmailsApi(new Client(), $brevo_config);

$params = ['username' => $user['username']];
$template_id = null;
$subject = '';
$message_body = '';

switch ($email_type) {
    case 'reset_password':
        $template_id = 9; // ID del template di reset password
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql_insert_token = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
        $stmt_insert_token = $conn->prepare($sql_insert_token);
        $stmt_insert_token->bind_param("sss", $user['email'], $token, $expires_at);
        $stmt_insert_token->execute();
        $stmt_insert_token->close();

        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $params['reset_link'] = "{$scheme}://{$host}/reset_password.php?token={$token}";
        break;

    case 'verify_account':
        if ($user['is_verified']) {
            echo json_encode(['success' => false, 'message' => 'Questo account è già verificato.']);
            exit();
        }
        $template_id = 6; // ID del template di verifica registrazione
        
        $verification_token = $user['verification_token'] ?? bin2hex(random_bytes(32));
        if (!$user['verification_token']) {
            $sql_update_token = "UPDATE users SET verification_token = ? WHERE id = ?";
            $stmt_update_token = $conn->prepare($sql_update_token);
            $stmt_update_token->bind_param("si", $verification_token, $user_id);
            $stmt_update_token->execute();
            $stmt_update_token->close();
        }

        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $params['verification_link'] = "{$scheme}://{$host}/verify.php?token={$verification_token}";
        break;

    case 'plan_changed':
        $template_id = 10; // ID del template di notifica cambio piano
        $params['old_plan'] = $data['old_plan'] ?? 'non specificato';
        $params['new_plan'] = $data['new_plan'] ?? 'non specificato';
        break;

    // Nuovo caso per l'email personalizzata
    case 'custom_message':
        $template_id = 11; // ID del template personalizzato in Brevo
        $subject = $data['subject'] ?? 'Messaggio da Bearget';
        $message_body = $data['body'] ?? 'Nessun messaggio specificato.';
        
        // Imposta le variabili per il template Brevo
        // Assicurati che 'subject_variable' e 'body_variable' corrispondano ai nomi delle tue variabili nel template #11
        $params['subject_variable'] = $subject;
        $params['body_variable'] = nl2br(htmlspecialchars($message_body)); // nl2br converte i salti di riga in <br>
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Tipo di email non valido.']);
        exit();
}

// Invia l'email con i parametri e il template appropriati
$sendSmtpEmail = new SendSmtpEmail([
     'templateId' => $template_id,
     'to' => [['name' => $user['username'], 'email' => $user['email']]],
     'params' => (object)$params
]);

try {
    $apiInstance->sendTransacEmail($sendSmtpEmail);
    echo json_encode(['success' => true, 'message' => 'Email inviata con successo!']);
} catch (Exception $e) {
    error_log("Errore Brevo da admin_send_email.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Impossibile inviare l\'email. Controlla i log per maggiori dettagli.']);
}

$conn->close();
