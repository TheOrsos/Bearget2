<?php
// migrations/apply_chat_migration.php

// Questo script crea la tabella 'chat_messages' per la funzionalità di chat.
// Va eseguito una sola volta.

require_once '../db_connect.php'; // ../ perché lo script è in una sottocartella

echo "Starting migration for 'chat' feature..." . PHP_EOL;

try {
    // SQL per creare la tabella 'chat_messages'
    $sql_chat_messages = "
    CREATE TABLE `chat_messages` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `sender_id` int(11) NOT NULL,
      `receiver_id` int(11) NOT NULL,
      `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
      `is_read` tinyint(1) NOT NULL DEFAULT 0,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_sender_receiver` (`sender_id`, `receiver_id`),
      KEY `idx_receiver_sender` (`receiver_id`, `sender_id`),
      CONSTRAINT `fk_chat_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      CONSTRAINT `fk_chat_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Esegue la query per 'chat_messages'
    if ($conn->query($sql_chat_messages) === TRUE) {
        echo "Table 'chat_messages' created successfully." . PHP_EOL;
    } else {
        throw new Exception("Error creating 'chat_messages' table: " . $conn->error);
    }

    echo "Chat migration completed successfully!" . PHP_EOL;

} catch (Exception $e) {
    echo "An error occurred during migration: " . $e->getMessage() . PHP_EOL;
} finally {
    $conn->close();
}
?>