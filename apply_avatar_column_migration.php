<?php
// migrations/apply_avatar_column_migration.php

// Questo script aggiunge la colonna 'profile_picture_path' alla tabella 'users'.
// Va eseguito una sola volta.

require_once '../db_connect.php'; // ../ perché lo script è in una sottocartella

echo "Starting migration to add 'profile_picture_path' column..." . PHP_EOL;

try {
    // SQL per aggiungere la colonna
    $sql_alter_table = "
    ALTER TABLE `users`
    ADD `profile_picture_path` VARCHAR(255) NULL DEFAULT NULL AFTER `email`;
    ";

    // Esegue la query
    if ($conn->query($sql_alter_table) === TRUE) {
        echo "Column 'profile_picture_path' added to 'users' table successfully." . PHP_EOL;
    } else {
        // Check if the column already exists to prevent script failure on re-run
        if ($conn->errno == 1060) { // Error code for 'Duplicate column name'
            echo "Column 'profile_picture_path' already exists. No changes made." . PHP_EOL;
        } else {
            throw new Exception("Error altering 'users' table: " . $conn->error);
        }
    }

    echo "Migration completed successfully!" . PHP_EOL;

} catch (Exception $e) {
    echo "An error occurred during migration: " . $e->getMessage() . PHP_EOL;
} finally {
    $conn->close();
}
?>