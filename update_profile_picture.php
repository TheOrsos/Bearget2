<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php?message=Not logged in.&type=error");
    exit;
}

$user_id = $_SESSION['id'];
$redirect_url = 'settings.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];

    // 1. Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        header("location: {$redirect_url}?message=Error during file upload. Code: {$file['error']}&type=error");
        exit;
    }

    // 2. Check file size (e.g., max 2MB)
    $max_size = 2 * 1024 * 1024; // 2 MB
    if ($file['size'] > $max_size) {
        header("location: {$redirect_url}?message=File is too large. Maximum size is 2MB.&type=error");
        exit;
    }

    // 3. Check MIME type to ensure it's an image
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mime_type, $allowed_mime_types)) {
        header("location: {$redirect_url}?message=Invalid file type. Only JPG, PNG, and GIF are allowed.&type=error");
        exit;
    }

    // 4. Generate a unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'user_' . $user_id . '_' . time() . '.' . $extension;
    $target_dir = 'uploads/avatars/';
    $target_path = $target_dir . $new_filename;

    // Ensure the target directory exists and is writable
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // 5. Get old picture path to delete it after successful upload
    $user_data = get_user_by_id($conn, $user_id);
    $old_picture_path = $user_data['profile_picture_path'] ?? null;

    // 6. Move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // 7. Update the database
        $sql_update = "UPDATE users SET profile_picture_path = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $target_path, $user_id);

        if ($stmt_update->execute()) {
            // 8. Delete the old profile picture if it exists
            if ($old_picture_path && file_exists($old_picture_path)) {
                unlink($old_picture_path);
            }
            header("location: {$redirect_url}?message=Profile picture updated successfully.&type=success");
        } else {
            // If DB update fails, delete the newly uploaded file to avoid orphaned files
            unlink($target_path);
            header("location: {$redirect_url}?message=Failed to update profile picture in database.&type=error");
        }
        $stmt_update->close();
    } else {
        header("location: {$redirect_url}?message=Failed to move uploaded file.&type=error");
    }

    $conn->close();

} else {
    header("location: {$redirect_url}");
    exit;
}
?>