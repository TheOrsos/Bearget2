<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { header("location: index.php"); exit; }
require_once 'db_connect.php';
require_once 'functions.php';
$user_id = $_SESSION["id"];

// Segna tutte le notifiche come lette
mark_notifications_as_read($conn, $user_id);

$notifications = get_all_notifications($conn, $user_id);
$user_accounts = get_user_accounts($conn, $user_id); // Aggiunta questa riga
$notification_count = 0; // Impostato a 0 perché l'utente è sulla pagina delle notifiche

$current_page = 'notifications'; 

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifiche - Bearget</title>
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
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; background-color: var(--color-gray-900); } </style>
</head>
<body class="text-gray-300">
    <div class="flex h-screen">
        <div id="sidebar-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 p-6 lg:p-10 overflow-y-auto">
            <header class="mb-8">
                <div class="flex items-center gap-4">
                    <button id="menu-button" type="button" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <span class="sr-only">Apri menu principale</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Notifiche
                        </h1>
                        <p class="text-gray-400 mt-1">Qui troverai inviti e altri avvisi importanti.</p>
                    </div>
                </div>
            </header>
            <div class="space-y-4 max-w-3xl mx-auto">
                <?php if (empty($notifications)): ?>
                    <div class="bg-gray-800 rounded-lg p-6 text-center">
                        <p class="text-gray-400">Nessuna nuova notifica al momento.</p>
                    </div>
                <?php else: foreach ($notifications as $notification): ?>
                <div class="bg-gray-800 rounded-lg p-4 <?php echo $notification['is_read'] ? 'opacity-60' : ''; ?>">
                    <div class="flex items-start">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mr-4">
                            <?php if ($notification['type'] == 'expense_approval'): ?>
                                <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <?php elseif ($notification['type'] == 'fund_invite'): ?>
                                <svg class="w-6 h-6 mr-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.122-1.28-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.122-1.28.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <?php elseif (in_array($notification['type'], ['friend_request', 'friend_request_accepted'])): ?>
                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-4 text-purple-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3.004 3.004 0 013.75-2.906z" /></svg>
                            <?php elseif (in_array($notification['type'], ['money_transfer_request', 'money_transfer_accepted', 'money_transfer_declined'])): ?>
                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-4 text-green-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.5 2.5 0 004 0V7.15a.5.5 0 00-.567.267C11.116 8.36 10.06 9 9 9s-2.116-.64-2.567-1.582zM9 13a1 1 0 100-2 1 1 0 000 2z" /><path fill-rule="evenodd" d="M9.878 3.878a3 3 0 00-3.756 0A3 3 0 004.5 6.622V14a2 2 0 002 2h7a2 2 0 002-2V6.622a3 3 0 00-1.622-2.744zM6 14v-1.378A5.02 5.02 0 019 9h2a5.02 5.02 0 013 3.622V14H6z" clip-rule="evenodd" /></svg>
                            <?php elseif ($notification['type'] == 'budget_warning'): ?>
                                <svg class="w-6 h-6 mr-4 text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <?php elseif ($notification['type'] == 'budget_exceeded'): ?>
                                <svg class="w-6 h-6 mr-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex-grow">
                            <?php if ($notification['type'] == 'expense_approval'):
                                $data = json_decode($notification['message'], true);
                                if ($data):
                                    $creator_username = htmlspecialchars($data['creator_username']);
                                    $amount = number_format($data['amount'], 2, ',', '.');
                                    $fund_name = htmlspecialchars($data['fund_name']);
                                    $description = htmlspecialchars($data['description']);
                            ?>
                                    <p class="text-white">
                                        <span class="font-bold"><?= $creator_username ?></span> ha registrato una spesa di <span class="font-bold text-primary-400">€<?= $amount ?></span> a tuo nome nel fondo <a href="fund_details.php?id=<?= $data['fund_id'] ?>" class="text-blue-400 hover:underline">'<?= $fund_name ?>'</a> per <span class="italic">"<?= $description ?>"</span>.
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1"><?= date("d/m/Y H:i", strtotime($notification['created_at'])) ?></p>
                                    
                                    <form action="approve_expense.php" method="POST" class="mt-4 space-y-3">
                                        <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                        <div>
                                            <label for="account_id_<?= $notification['id'] ?>" class="block text-sm font-medium text-gray-400 mb-1">Approva usando il conto:</label>
                                            <select name="account_id" id="account_id_<?= $notification['id'] ?>" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                                                <?php foreach ($user_accounts as $account): ?>
                                                <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button type="submit" name="action" value="approve" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg text-sm">Approva</button>
                                            <button type="submit" name="action" value="decline" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg text-sm">Rifiuta</button>
                                        </div>
                                    </form>
                            <?php 
                                endif;
                            elseif ($notification['type'] == 'fund_invite' && !is_fund_member($conn, $notification['related_id'], $user_id)): ?>
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?= date("d/m/Y H:i", strtotime($notification['created_at'])) ?></p>
                                <div class="flex space-x-2 flex-shrink-0 mt-2">
                                    <form action="accept_invite.php" method="POST" class="inline-block">
                                        <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                        <input type="hidden" name="fund_id" value="<?php echo $notification['related_id']; ?>">
                                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-3 py-1 rounded-md text-sm">Accetta</button>
                                    </form>
                                    <form action="decline_invite.php" method="POST" class="inline-block">
                                        <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-1 rounded-md text-sm">Rifiuta</button>
                                    </form>
                                </div>
                            <?php elseif ($notification['type'] == 'friend_request' && get_friendship_status($conn, $notification['related_id']) == 'pending'): ?>
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?= date("d/m/Y H:i", strtotime($notification['created_at'])) ?></p>
                                <div class="flex space-x-2 flex-shrink-0 mt-2">
                                    <form action="handle_friend_request.php" method="POST" class="inline-block">
                                        <input type="hidden" name="request_id" value="<?php echo $notification['related_id']; ?>">
                                        <button type="submit" name="action" value="accept" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-3 py-1 rounded-md text-sm">Accetta</button>
                                    </form>
                                    <form action="handle_friend_request.php" method="POST" class="inline-block">
                                        <input type="hidden" name="request_id" value="<?php echo $notification['related_id']; ?>">
                                        <button type="submit" name="action" value="decline" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-1 rounded-md text-sm">Rifiuta</button>
                                    </form>
                                </div>
                            <?php elseif ($notification['type'] == 'money_transfer_request' && get_money_transfer_status($conn, $notification['related_id']) == 'pending'): ?>
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?= date("d/m/Y H:i", strtotime($notification['created_at'])) ?></p>
                                <div class="flex space-x-2 flex-shrink-0 mt-2">
                                    <form action="handle_money_transfer.php" method="POST" class="inline-block">
                                        <input type="hidden" name="transfer_id" value="<?php echo $notification['related_id']; ?>">
                                        <button type="submit" name="action" value="accept" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-3 py-1 rounded-md text-sm">Accetta</button>
                                    </form>
                                    <form action="handle_money_transfer.php" method="POST" class="inline-block">
                                        <input type="hidden" name="transfer_id" value="<?php echo $notification['related_id']; ?>">
                                        <button type="submit" name="action" value="decline" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-1 rounded-md text-sm">Rifiuta</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?= date("d/m/Y H:i", strtotime($notification['created_at'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </main>
    </div>
    <script>
                // --- NUOVA LOGICA PER LA SIDEBAR RESPONSIVE ---
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const menuButton = document.getElementById('menu-button');
            const sidebarBackdrop = document.getElementById('sidebar-backdrop');

            const toggleSidebar = () => {
                sidebar.classList.toggle('-translate-x-full');
                sidebarBackdrop.classList.toggle('hidden');
            };

            if (menuButton) {
                menuButton.addEventListener('click', toggleSidebar);
            }

            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', toggleSidebar);
            }
        });
    </script>
</body>
</html>