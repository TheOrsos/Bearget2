<?php
session_start();
header('Content-Type: application/json');

// Rimuovi queste righe se le hai ancora, non servono più
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// 1. Controllo di sicurezza: solo l'admin può eseguire questo script
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["id"]) || $_SESSION["id"] != 1) {
    echo json_encode(['success' => false, 'message' => 'Accesso non autorizzato.']);
    exit;
}

require_once 'db_connect.php';
require_once 'functions.php';

// Aggiungiamo le classi di Brevo necessarie
use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;
use Exception;

$response = ['success' => false, 'message' => 'Errore generico.'];

// 2. Recupero dell'ID dell'aggiornamento dalla richiesta POST
$data = json_decode(file_get_contents('php://input'), true);
$update_id = $data['id'] ?? null;

if (!$update_id) {
    $response['message'] = 'ID dell\'aggiornamento non fornito.';
    echo json_encode($response);
    exit;
}

// 3. Recupero dei dettagli dell'aggiornamento dal database
$stmt = $conn->prepare("SELECT version, title, description, content FROM changelog_updates WHERE id = ?");
$stmt->bind_param("i", $update_id);
$stmt->execute();
$result = $stmt->get_result();
$update = $result->fetch_assoc();
$stmt->close();

if (!$update) {
    $response['message'] = 'Aggiornamento non trovato.';
    echo json_encode($response);
    exit;
}

// 4. Recupero di tutti gli utenti dal database (senza filtri)
$users_result = $conn->query("SELECT email, username FROM users");
if (!$users_result) {
    $response['message'] = 'Impossibile recuperare gli utenti.';
    echo json_encode($response);
    exit;
}

$users = [];
while ($row = $users_result->fetch_assoc()) {
    $users[] = $row;
}

// 5. Configurazione di Brevo e invio delle email
$brevo_config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $_ENV['BREVO_API_KEY']);
$apiInstance = new TransactionalEmailsApi(new Client(), $brevo_config);
$changelog_url = get_base_url() . 'changelog.php';
$email_template_id = 12; 

$emails_sent = 0;
$errors_occurred = false;

foreach ($users as $user) {
    $email_vars = [
        'username' => $user['username'],
        'changelog_version' => $update['version'],
        'changelog_title' => $update['title'],
        'changelog_description' => $update['description'],
        'changelog_url' => $changelog_url,
    ];

    $sendSmtpEmail = new SendSmtpEmail([
         'templateId' => $email_template_id,
         'to' => [['name' => $user['username'], 'email' => $user['email']]],
         'params' => (object)$email_vars
    ]);

    try {
        $apiInstance->sendTransacEmail($sendSmtpEmail);
        $emails_sent++;
    } catch (Exception $e) {
        // Se c'è un errore, lo registriamo ma continuiamo con gli altri utenti
        error_log("Errore Brevo per utente " . $user['email'] . ": " . $e->getMessage());
        $errors_occurred = true;
    }
}

// 6. Preparazione della risposta finale
if ($emails_sent > 0) {
    $response['success'] = true;
    $response['message'] = "Notifica inviata con successo a " . $emails_sent . " utenti.";
    if ($errors_occurred) {
        $response['message'] .= " Si sono verificati alcuni errori durante l'invio, controlla i log del server.";
    }
    
    // Aggiorniamo lo stato nel database solo se almeno un'email è partita
    $update_stmt = $conn->prepare("UPDATE changelog_updates SET email_sent = 1 WHERE id = ?");
    $update_stmt->bind_param("i", $update_id);
    $update_stmt->execute();
    $update_stmt->close();

} else {
    $response['message'] = "Nessuna email inviata. Controlla la configurazione di Brevo e i log del server.";
}

echo json_encode($response);
$conn->close();
?>