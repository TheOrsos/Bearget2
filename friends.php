<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}
require_once 'db_connect.php';
require_once 'functions.php';
require_once 'friends_functions.php';

$user_id = $_SESSION["id"];
$user = get_user_by_id($conn, $user_id);
$friends = get_friends_for_user($conn, $user_id);
$accounts = get_user_accounts($conn, $user_id); // For the transfer modal
$unread_counts = get_unread_message_counts($conn, $user_id);

$current_page = 'friends';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amici - Bearget</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="theme.php">
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: { 500: 'var(--color-primary-500)', 600: 'var(--color-primary-600)', 700: 'var(--color-primary-700)' },
              gray: { 100: 'var(--color-gray-100)', 200: 'var(--color-gray-200)', 300: 'var(--color-gray-300)', 400: 'var(--color-gray-400)', 700: 'var(--color-gray-700)', 800: 'var(--color-gray-800)', 900: 'var(--color-gray-900)' },
              success: 'var(--color-success)', danger: 'var(--color-danger)', warning: 'var(--color-warning)'
            }
          }
        }
      }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; background-color: var(--color-gray-900); } </style>
</head>
<body class="text-gray-200">

    <div class="flex h-screen">
        <div id="sidebar-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 p-6 lg:p-10 overflow-y-auto">
            <header class="mb-8">
                <div class="flex items-center gap-4">
                    <button id="menu-button" type="button" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3.004 3.004 0 013.75-2.906z" /></svg>
                            Amici
                        </h1>
                        <p class="text-gray-400 mt-1">Gestisci i tuoi amici e invia loro denaro.</p>
                    </div>
                </div>
            </header>

            <?php include 'toast_notification.php'; ?>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <div class="xl:col-span-1 space-y-8">
                    <!-- Your Friend Code -->
                    <div class="bg-gray-800 rounded-2xl p-6">
                        <h2 class="text-xl font-bold text-white mb-4">Il Tuo Codice Amico</h2>
                        <p class="text-gray-400 mb-2">Condividi questo codice per ricevere richieste di amicizia.</p>
                        <div class="bg-gray-900 text-white text-center font-mono text-2xl tracking-widest py-3 rounded-lg cursor-pointer" onclick="copyToClipboard('<?php echo htmlspecialchars($user['friend_code']); ?>')">
                            <?php echo htmlspecialchars($user['friend_code']); ?>
                        </div>
                        <p id="copy-message" class="text-center text-sm text-green-400 mt-2 hidden">Copiato!</p>
                    </div>

                    <!-- Add Friend -->
                    <div class="bg-gray-800 rounded-2xl p-6">
                        <h2 class="text-xl font-bold text-white mb-4">Aggiungi un Amico</h2>
                        <form action="send_friend_request.php" method="POST">
                            <label for="friend_code" class="block text-sm font-medium text-gray-300 mb-1">Codice Amico</label>
                            <input type="text" name="friend_code" id="friend_code" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 mb-4" placeholder="Incolla il codice qui">
                            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2.5 rounded-lg">Invia Richiesta</button>
                        </form>
                    </div>
                </div>

                <div class="xl:col-span-2 bg-gray-800 rounded-2xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Lista Amici</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-gray-700">
                                    <th class="py-2 px-4 w-16"></th>
                                    <th class="py-2 px-4">Username</th>
                                    <th class="py-2 px-4">Email</th>
                                    <th class="py-2 px-4">Codice Amico</th>
                                    <th class="py-2 px-4"></th>
                                </tr>
                            </thead>
                            <tbody id="friends-table-body">
                                <?php if (empty($friends)): ?>
                                    <tr><td colspan="5" class="text-center py-8 text-gray-400">Non hai ancora nessun amico.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($friends as $friend): ?>
                                        <tr class="border-b border-gray-700 hover:bg-gray-700/50">
                                            <td class="py-2 px-4">
                                                <img src="<?php echo !empty($friend['profile_picture_path']) ? htmlspecialchars($friend['profile_picture_path']) : 'assets/images/default_avatar.png'; ?>" alt="Avatar" class="w-10 h-10 rounded-full object-cover">
                                            </td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($friend['username']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($friend['email']); ?></td>
                                            <td class="py-3 px-4 font-mono"><?php echo htmlspecialchars($friend['friend_code']); ?></td>
                                            <td class="py-3 px-4 text-right">
                                                <button onclick="openChatModal(<?php echo $friend['id']; ?>, '<?php echo htmlspecialchars($friend['username']); ?>')" class="chat-button relative inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1 px-3 rounded-lg text-sm" data-friend-id="<?php echo $friend['id']; ?>">
                                                    Chat
                                                </button>
                                                <button onclick="openTransferModal(<?php echo $friend['id']; ?>, '<?php echo htmlspecialchars($friend['username']); ?>')" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-1 px-3 rounded-lg text-sm ml-2">
                                                    Invia Denaro
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Transfer Modal -->
    <div id="transferModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-gray-800 rounded-2xl p-8 w-full max-w-md">
            <h2 class="text-2xl font-bold text-white mb-4">Invia Denaro a <span id="modalFriendName"></span></h2>
            <form id="transferForm" action="send_money_transfer.php" method="POST">
                <input type="hidden" name="receiver_id" id="modalReceiverId">

                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-300 mb-1">Importo (€)</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0.01" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                </div>

                <div class="mb-6">
                    <label for="from_account_id" class="block text-sm font-medium text-gray-300 mb-1">Dal Conto</label>
                    <select name="from_account_id" id="from_account_id" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?php echo $account['id']; ?>"><?php echo htmlspecialchars($account['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex justify-end gap-4">
                    <button type="button" onclick="closeTransferModal()" class="bg-gray-600 hover:bg-gray-500 text-white font-semibold py-2 px-4 rounded-lg">Annulla</button>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-4 rounded-lg">Invia Richiesta</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Chat Modal -->
    <div id="chatModal" class="fixed inset-0 bg-gray-900 z-50 hidden flex flex-col">
        <header class="bg-gray-800 shadow-md p-4 flex items-center gap-4 flex-shrink-0">
            <button onclick="closeChatModal()" class="p-2 rounded-full hover:bg-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </button>
            <h1 id="chatModalFriendName" class="text-xl font-bold text-white"></h1>
        </header>
        <div id="chat-box" class="flex-1 p-6 overflow-y-auto">
            <!-- Messages will be loaded here -->
        </div>
        <footer class="p-4 bg-gray-800">
            <form id="chat-form" class="flex gap-4">
                <input type="hidden" id="chatReceiverId" value="">
                <input type="text" id="message-input" placeholder="Scrivi un messaggio..." autocomplete="off" required class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-6 rounded-lg">Invia</button>
            </form>
        </footer>
    </div>

    <script>
        const currentUserId = <?php echo $user_id; ?>;

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                const msg = document.getElementById('copy-message');
                msg.classList.remove('hidden');
                setTimeout(() => msg.classList.add('hidden'), 2000);
            });
        }

        // --- Transfer Modal Logic ---
        const transferModal = document.getElementById('transferModal');
        const transferModalFriendName = document.getElementById('modalFriendName');
        const transferModalReceiverId = document.getElementById('modalReceiverId');

        function openTransferModal(friendId, friendName) {
            transferModalFriendName.textContent = friendName;
            transferModalReceiverId.value = friendId;
            transferModal.classList.remove('hidden');
        }

        function closeTransferModal() {
            transferModal.classList.add('hidden');
        }

        // --- Chat Modal Logic ---
        const chatModal = document.getElementById('chatModal');
        const chatModalFriendName = document.getElementById('chatModalFriendName');
        const chatBox = document.getElementById('chat-box');
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message-input');
        const chatReceiverIdInput = document.getElementById('chatReceiverId');

        let chatPollingInterval = null;
        let lastMessageTimestamp = '1970-01-01 00:00:00';

        function openChatModal(friendId, friendName) {
            chatModalFriendName.textContent = `Chat con ${friendName}`;
            chatReceiverIdInput.value = friendId;
            chatBox.innerHTML = ''; // Clear previous messages
            lastMessageTimestamp = '1970-01-01 00:00:00';

            chatModal.classList.remove('hidden');
            fetchNewMessages(); // Initial fetch
            chatPollingInterval = setInterval(fetchNewMessages, 10000); // Poll every 10 seconds
        }

        function closeChatModal() {
            chatModal.classList.add('hidden');
            clearInterval(chatPollingInterval); // Stop polling
        }

        function scrollToBottom() {
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function renderMessage(msg) {
            const messageWrapper = document.createElement('div');
            const messageBubble = document.createElement('div');
            const avatarImg = document.createElement('img');
            const isSender = msg.sender_id == currentUserId;

            // Avatar setup
            avatarImg.src = msg.profile_picture_path ? msg.profile_picture_path : 'assets/images/default_avatar.png';
            avatarImg.alt = 'Avatar';
            avatarImg.classList.add('w-8', 'h-8', 'rounded-full', 'object-cover');

            // Bubble setup
            messageBubble.classList.add('px-4', 'py-2', 'rounded-lg', 'max-w-xs', 'lg:max-w-md', 'break-words');
            messageBubble.classList.add(isSender ? 'bg-primary-600' : 'bg-gray-700');
            messageBubble.textContent = msg.message;

            // Wrapper setup
            messageWrapper.classList.add('flex', 'items-end', 'mb-4', 'gap-2');
            if (isSender) {
                messageWrapper.classList.add('justify-end');
                messageWrapper.appendChild(messageBubble);
                messageWrapper.appendChild(avatarImg);
            } else {
                messageWrapper.classList.add('justify-start');
                messageWrapper.appendChild(avatarImg);
                messageWrapper.appendChild(messageBubble);
            }

            chatBox.appendChild(messageWrapper);

            if (msg.created_at > lastMessageTimestamp) {
                lastMessageTimestamp = msg.created_at;
            }
        }

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = messageInput.value.trim();
            const receiverId = chatReceiverIdInput.value;
            if (!message || !receiverId) return;

            const formData = new FormData();
            formData.append('receiver_id', receiverId);
            formData.append('message', message);

            messageInput.value = '';

            fetch('api_send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    fetchNewMessages(); // Fetch immediately after sending
                } else {
                    console.error('Failed to send message:', data.message);
                }
            })
            .catch(error => console.error('Error sending message:', error));
        });

        function fetchNewMessages() {
            const receiverId = chatReceiverIdInput.value;
            if (!receiverId) return;

            fetch(`api_fetch_messages.php?friend_id=${receiverId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.messages.length > 0) {
                        // Simple re-render approach for now
                        chatBox.innerHTML = '';
                        data.messages.forEach(renderMessage);
                        scrollToBottom();
                    } else if (data.status === 'success') {
                        // No messages yet
                         chatBox.innerHTML = '<p class="text-center text-gray-500">Nessun messaggio. Inizia la conversazione!</p>';
                    }
                })
                .catch(error => console.error('Error fetching messages:', error));
        }

        // --- Sidebar Logic ---
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const menuButton = document.getElementById('menu-button');
            const sidebarBackdrop = document.getElementById('sidebar-backdrop');
            const toggleSidebar = () => {
                sidebar.classList.toggle('-translate-x-full');
                sidebarBackdrop.classList.toggle('hidden');
            };
            if(menuButton) menuButton.addEventListener('click', toggleSidebar);
            if(sidebarBackdrop) sidebarBackdrop.addEventListener('click', toggleSidebar);
        });
    </script>
</body>
</html>