<?php
// Gestisce la logica di invio del link di reset password tramite Brevo.
require_once 'db_connect.php';

use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;
use Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Messaggio di successo generico da mostrare in ogni caso per sicurezza
    $success_message = "Se un account con questa email esiste, abbiamo inviato un link di recupero.";

    // Controlla se l'email esiste e recupera l'username
    $sql_check = "SELECT id, username FROM users WHERE email = ? LIMIT 1";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $username = $user['username'];
            
            // Email trovata, genera un token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Salva il token nel database
            $sql_insert = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
            if ($stmt_insert = $conn->prepare($sql_insert)) {
                $stmt_insert->bind_param("sss", $email, $token, $expires_at);
                
                if ($stmt_insert->execute()) {
                    // Prepara e invia l'email tramite Brevo
                    $brevo_config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $_ENV['BREVO_API_KEY']);
                    $apiInstance = new TransactionalEmailsApi(new Client(), $brevo_config);

                    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'];
                    $reset_link = "{$scheme}://{$host}/reset_password.php?token={$token}";

                    $sendSmtpEmail = new SendSmtpEmail([
                         'templateId' => 9, // <-- ID del tuo nuovo template
                         'to' => [['name' => $username, 'email' => $email]],
                         'params' => (object)[
                             'username' => $username,
                             'reset_link' => $reset_link
                         ]
                    ]);

                    try {
                        $apiInstance->sendTransacEmail($sendSmtpEmail);
                    } catch (Exception $e) {
                        // Non mostrare l'errore all'utente per sicurezza, ma si potrebbe loggare.
                        // error_log("Errore Brevo invio reset password: " . $e->getMessage());
                    }
                }
                $stmt_insert->close();
            }
        }
        $stmt_check->close();
    }
    
    // Reindirizza sempre alla stessa pagina con lo stesso messaggio per non dare indizi a malintenzionati
    header("Location: forgot_password.php?message=" . urlencode($success_message) . "&type=success");
    exit();
}
?>