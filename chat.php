<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}
require_once 'db_connect.php';
require_once 'functions.php';

$user_id = $_SESSION["id"];
$friend_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($friend_id === 0) {
    header("location: friends.php?message=Invalid friend specified.&type=error");
    exit;
}

// Security Check: Verify they are actually friends
$friends = get_friends_for_user($conn, $user_id);
$is_friend = false;
foreach ($friends as $f) {
    if ($f['id'] == $friend_id) {
        $is_friend = true;
        break;
    }
}
if (!$is_friend) {
    header("location: friends.php?message=You can only chat with your friends.&type=error");
    exit;
}

$friend = get_user_by_id($conn, $friend_id);

if (!$friend) {
    // This case should not happen if the database is consistent, but it's a good safeguard.
    header("location: friends.php?message=Could not find friend data.&type=error");
    exit;
}

// Fetch initial messages
$sql_fetch = "SELECT sender_id, message, created_at FROM chat_messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();
$initial_messages = [];
while ($row = $result->fetch_assoc()) {
    $initial_messages[] = $row;
}
$stmt_fetch->close();

// Mark initial messages as read
$sql_mark_read = "UPDATE chat_messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
$stmt_mark_read = $conn->prepare($sql_mark_read);
$stmt_mark_read->bind_param("ii", $friend_id, $user_id);
$stmt_mark_read->execute();
$stmt_mark_read->close();


$current_page = 'friends'; // To highlight the correct sidebar item
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat con <?php echo htmlspecialchars($friend['username']); ?> - Bearget</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="theme.php">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: var(--color-gray-900); }
        #chat-box { height: calc(100vh - 200px); }
    </style>
</head>
<body class="text-gray-200">
    <div class="flex h-screen">
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 flex flex-col">
            <header class="bg-gray-800 shadow-md p-4 flex items-center gap-4 flex-shrink-0">
                <a href="friends.php" class="p-2 rounded-full hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <h1 class="text-xl font-bold text-white">Chat con <?php echo htmlspecialchars($friend['username']); ?></h1>
            </header>

            <div id="chat-box" class="flex-1 p-6 overflow-y-auto">
                <!-- Messages will be loaded here -->
            </div>

            <footer class="p-4 bg-gray-800">
                <form id="chat-form" class="flex gap-4">
                    <input type="hidden" id="receiver_id" value="<?php echo $friend_id; ?>">
                    <input type="text" id="message-input" placeholder="Scrivi un messaggio..." autocomplete="off" required class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-6 rounded-lg">Invia</button>
                </form>
            </footer>
        </main>
    </div>

    <script>
        const chatBox = document.getElementById('chat-box');
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message-input');
        const receiverId = document.getElementById('receiver_id').value;
        const currentUserId = <?php echo $user_id; ?>;

        let lastMessageTimestamp = '1970-01-01 00:00:00';

        function scrollToBottom() {
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function renderMessage(msg) {
            const messageWrapper = document.createElement('div');
            const messageBubble = document.createElement('div');

            const isSender = msg.sender_id == currentUserId;

            messageWrapper.classList.add('flex', 'mb-4', isSender ? 'justify-end' : 'justify-start');
            messageBubble.classList.add('px-4', 'py-2', 'rounded-lg', 'max-w-xs', 'lg:max-w-md');
            messageBubble.classList.add(isSender ? 'bg-primary-600' : 'bg-gray-700');

            messageBubble.textContent = msg.message;
            messageWrapper.appendChild(messageBubble);
            chatBox.appendChild(messageWrapper);

            lastMessageTimestamp = msg.created_at > lastMessageTimestamp ? msg.created_at : lastMessageTimestamp;
        }

        // Load initial messages
        const initialMessages = <?php echo json_encode($initial_messages); ?>;
        initialMessages.forEach(renderMessage);
        scrollToBottom();

        // Send message via AJAX
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;

            const formData = new FormData();
            formData.append('receiver_id', receiverId);
            formData.append('message', message);

            // Optimistically render the sent message
            const optimisticMessage = {
                sender_id: currentUserId,
                message: message,
                created_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
            };
            renderMessage(optimisticMessage);
            scrollToBottom();

            messageInput.value = '';

            fetch('api_send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success') {
                    console.error('Failed to send message:', data.message);
                    // Optionally, show an error indicator on the optimistically sent message
                }
            })
            .catch(error => console.error('Error sending message:', error));
        });

        // Fetch new messages via AJAX polling
        function fetchNewMessages() {
            fetch(`api_fetch_messages.php?friend_id=${receiverId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.messages.length > 0) {
                        const newMessages = data.messages.filter(msg => msg.created_at > lastMessageTimestamp);
                        if (newMessages.length > 0) {
                            newMessages.forEach(renderMessage);
                            scrollToBottom();
                        }
                    }
                })
                .catch(error => console.error('Error fetching messages:', error));
        }

        setInterval(fetchNewMessages, 10000); // Poll every 10 seconds
    </script>
</body>
</html>