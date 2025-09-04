<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

// Sicurezza
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    exit("Accesso non autorizzato.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["id"];
    $override_account = isset($_POST['override_account']);
    $default_account_id = $_POST['account_id'];
    
    $success_count = 0;
    $skipped_rows = [];

    // Controlla se il file è stato caricato correttamente
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
        
        $file_tmp_path = $_FILES['csv_file']['tmp_name'];

        // Apri il file CSV
        if (($handle = fopen($file_tmp_path, "r")) !== FALSE) {
            
            $conn->begin_transaction();
            try {
                $sql = "INSERT INTO transactions (user_id, account_id, category_id, amount, type, description, transaction_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                
                $header = fgetcsv($handle, 1000, ","); // Leggi l'intestazione per saltarla

                $row_number = 1;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row_number++;
                    
                    // Assegnazione colonne come da file esportato
                    $date = $data[0] ?? null;
                    $description = $data[1] ?? null;
                    $category_name = $data[2] ?? null;
                    $account_name = $data[3] ?? null;
                    $amount = $data[4] ?? null;

                    if (empty($date) || empty($description) || !is_numeric($amount) || empty($category_name)) {
                        $skipped_rows[] = "Riga {$row_number}: Dati mancanti o non validi.";
                        continue;
                    }
                    
                    $account_id_to_use = $default_account_id;
                    if (!$override_account) {
                        if(empty($account_name)) {
                            $skipped_rows[] = "Riga {$row_number}: Nome del conto mancante nel file.";
                            continue;
                        }
                        $account = get_account_by_name_for_user($conn, $user_id, $account_name);
                        if (!$account) {
                            $skipped_rows[] = "Riga {$row_number}: Conto '{$account_name}' non trovato.";
                            continue;
                        }
                        $account_id_to_use = $account['id'];
                    }

                    $category = get_category_by_name_for_user($conn, $user_id, $category_name);
                    if (!$category) {
                        $skipped_rows[] = "Riga {$row_number}: Categoria '{$category_name}' non trovata.";
                        continue;
                    }
                    $category_id = $category['id'];
                    $type = $category['type'];
                    
                    $final_amount = ($type == 'expense') ? -abs($amount) : abs($amount);

                    $stmt->bind_param("iiidsss", $user_id, $account_id_to_use, $category_id, $final_amount, $type, $description, $date);
                    $stmt->execute();
                    $success_count++;
                }

                $stmt->close();
                $conn->commit();

            } catch (Exception $e) {
                $conn->rollback();
                header("location: transactions.php?message=Errore durante l'importazione: " . urlencode($e->getMessage()) . "&type=error");
                exit();
            }

            fclose($handle);
            $message = "Importazione completata! Transazioni aggiunte: {$success_count}.";
            if (!empty($skipped_rows)) {
                // Salva le righe saltate in sessione per mostrarle
                $_SESSION['import_skipped_rows'] = $skipped_rows;
                $message .= " Righe saltate: " . count($skipped_rows) . ". <a href='#' onclick='showSkippedRows()' class='font-bold underline'>Mostra dettagli</a>";
            }
            header("location: transactions.php?message=" . urlencode($message) . "&type=success");
            exit();
        }
    }
}

header("location: transactions.php?message=Errore nel caricamento del file.&type=error");
exit();

function get_account_by_name_for_user($conn, $user_id, $account_name) {
    $sql = "SELECT id FROM accounts WHERE user_id = ? AND name = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $trimmed_name = trim($account_name);
    $stmt->bind_param("is", $user_id, $trimmed_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $account = $result->fetch_assoc();
    $stmt->close();
    return $account;
}
?>