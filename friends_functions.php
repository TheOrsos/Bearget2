<?php
// Contains functions specific to the friends and chat system.

/**
 * Ottiene l'elenco degli amici di un utente.
 */
function get_friends_for_user($conn, $user_id) {
    $friends = [];
    // The user's profile picture path is also needed
    $sql = "SELECT u.id, u.username, u.email, u.friend_code, u.profile_picture_path
            FROM users u
            JOIN friendships f ON (u.id = f.user_id_1 OR u.id = f.user_id_2)
            WHERE f.status = 'accepted'
              AND (f.user_id_1 = ? OR f.user_id_2 = ?)
              AND u.id != ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $friends[] = $row;
    }

    $stmt->close();
    return $friends;
}

/**
 * Gets the unread message counts for a user, grouped by sender.
 * @return array An associative array mapping sender_id to their unread message count.
 */
function get_unread_message_counts($conn, $user_id) {
    $counts = [];
    $sql = "SELECT sender_id, COUNT(id) as unread_count
            FROM chat_messages
            WHERE receiver_id = ? AND is_read = 0
            GROUP BY sender_id";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $counts[$row['sender_id']] = $row['unread_count'];
    }

    $stmt->close();
    return $counts;
}
?>