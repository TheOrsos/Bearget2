<?php
// Questo script va eseguito periodicamente (es. una volta al giorno) tramite un cron job.
// Cancella i messaggi e le notifiche più vecchi di 30 giorni.

require_once 'db_connect.php';

// Imposta il fuso orario per evitare problemi con la data
date_default_timezone_set('Europe/Rome');

// Calcola la data di 30 giorni fa
$thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));

// 1. Cancella i messaggi della chat più vecchi di 30 giorni
$sql_delete_messages = "DELETE FROM chat_messages WHERE created_at < ?";
$stmt_messages = $conn->prepare($sql_delete_messages);
$stmt_messages->bind_param("s", $thirty_days_ago);
$stmt_messages->execute();
$deleted_messages = $stmt_messages->affected_rows;
$stmt_messages->close();

// 2. Cancella le notifiche più vecchie di 30 giorni
$sql_delete_notifications = "DELETE FROM notifications WHERE created_at < ?";
$stmt_notifications = $conn->prepare($sql_delete_notifications);
$stmt_notifications->bind_param("s", $thirty_days_ago);
$stmt_notifications->execute();
$deleted_notifications = $stmt_notifications->affected_rows;
$stmt_notifications->close();

$conn->close();

// Logga l'esecuzione (opzionale, ma utile per il debug)
$log_message = date('[Y-m-d H:i:s]') . " - Cleanup script executed. Deleted {$deleted_messages} messages and {$deleted_notifications} notifications.\n";
// Potresti voler scrivere questo log in un file specifico
// file_put_contents('cleanup_log.txt', $log_message, FILE_APPEND);

// Restituisce un output semplice per il servizio di cron job
echo "Cleanup completed. Deleted {$deleted_messages} messages and {$deleted_notifications} notifications.";
?>