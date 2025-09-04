<?php
// apply_friends_migration.php

// Questo script crea le tabelle 'friendships' e 'money_transfers' necessarie per la nuova funzionalità.
// Va eseguito una sola volta.

require_once 'db_connect.php';

echo "Starting migration for 'friends' feature..." . PHP_EOL;

try {
    // SQL per creare la tabella 'friendships'
    $sql_friendships = "
    CREATE TABLE `friendships` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id_1` int(11) NOT NULL COMMENT 'ID of the user who sent the request',
      `user_id_2` int(11) NOT NULL COMMENT 'ID of the user who received the request',
      `status` enum('pending','accepted','declined') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_friendship` (`user_id_1`,`user_id_2`),
      KEY `user_id_1` (`user_id_1`),
      KEY `user_id_2` (`user_id_2`),
      CONSTRAINT `fk_friendships_user1` FOREIGN KEY (`user_id_1`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      CONSTRAINT `fk_friendships_user2` FOREIGN KEY (`user_id_2`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Esegue la query per 'friendships'
    if ($conn->query($sql_friendships) === TRUE) {
        echo "Table 'friendships' created successfully." . PHP_EOL;
    } else {
        throw new Exception("Error creating 'friendships' table: " . $conn->error);
    }

    // SQL per creare la tabella 'money_transfers'
    $sql_money_transfers = "
    CREATE TABLE `money_transfers` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `sender_id` int(11) NOT NULL,
      `receiver_id` int(11) NOT NULL,
      `from_account_id` int(11) NOT NULL,
      `amount` decimal(10,2) NOT NULL,
      `status` enum('pending','accepted','declined') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `sender_id` (`sender_id`),
      KEY `receiver_id` (`receiver_id`),
      KEY `from_account_id` (`from_account_id`),
      CONSTRAINT `fk_transfers_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      CONSTRAINT `fk_transfers_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      CONSTRAINT `fk_transfers_account` FOREIGN KEY (`from_account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Esegue la query per 'money_transfers'
    if ($conn->query($sql_money_transfers) === TRUE) {
        echo "Table 'money_transfers' created successfully." . PHP_EOL;
    } else {
        throw new Exception("Error creating 'money_transfers' table: " . $conn->error);
    }

    echo "Migration completed successfully!" . PHP_EOL;

} catch (Exception $e) {
    echo "An error occurred during migration: " . $e->getMessage() . PHP_EOL;
} finally {
    $conn->close();
}
?>