<?php
session_start();
// Sicurezza: solo l'utente con ID 1 può accedere a questa pagina.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["id"] != 1) {
    header("location: dashboard.php");
    exit;
}
require_once 'db_connect.php';
require_once 'functions.php';

// --- LOGICA DI PAGINAZIONE E RICERCA ---
$users_per_page = 20; // Quanti utenti mostrare per pagina
$current_page_number = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$search_term = $_GET['search'] ?? '';

// Recupera gli utenti paginati e il numero totale di pagine
$user_data = get_users_paginated_and_searched($conn, $search_term, $current_page_number, $users_per_page);
$users_list = $user_data['users'];
$total_pages = $user_data['total_pages'];

// Recupera le statistiche generali
$stats = get_admin_stats($conn);

$current_page = 'admin'; // Per evidenziare il link nella sidebar
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello Admin - Bearget</title>
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
    <style>
        body { font-family: 'Inter', sans-serif; background-color: var(--color-gray-900); }
        .modal-backdrop { transition: opacity 0.3s ease-in-out; }
        .modal-content { transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out; }
    </style>
</head>
<body class="text-gray-300">
    <div class="flex h-screen">
        <div id="sidebar-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-10 overflow-y-auto">
            <header class="flex flex-wrap justify-between items-center gap-4 mb-8">
                <div class="flex items-center gap-4">
                    <button id="menu-button" type="button" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <span class="sr-only">Apri menu principale</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Pannello Admin
                        </h1>
                        <p class="text-gray-400 mt-1">Statistiche e gestione utenti.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="admin_changelog.php" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.433 13.582l-2.25 2.25a1.5 1.5 0 002.121 2.121l2.25-2.25M12 3l.4-1.2a1.4 1.4 0 012.2 0L15 3m-3 .01M6 19l-2 2m0 0l2-2m-2 2h14l2-2m-2-2l2-2m-2 2l-2-2"></path></svg>
                        Gestisci Changelog
                    </a>
                    <a href="https://cron-job.org/en/" target="_blank" class="bg-gray-700 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Cron Job Service
                    </a>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gray-800 p-6 rounded-2xl"><h3 class="text-gray-400 text-sm font-medium">Utenti Totali</h3><p class="text-3xl font-bold text-white mt-1"><?php echo $stats['total_users']; ?></p></div>
                <div class="bg-gray-800 p-6 rounded-2xl"><h3 class="text-gray-400 text-sm font-medium">Utenti Pro</h3><p class="text-3xl font-bold text-green-400 mt-1"><?php echo $stats['pro_users']; ?></p></div>
                <div class="bg-gray-800 p-6 rounded-2xl"><h3 class="text-gray-400 text-sm font-medium">Utenti Free</h3><p class="text-3xl font-bold text-yellow-400 mt-1"><?php echo $stats['free_users']; ?></p></div>
                <div class="bg-gray-800 p-6 rounded-2xl"><h3 class="text-gray-400 text-sm font-medium">Nuovi (30 giorni)</h3><p class="text-3xl font-bold text-indigo-400 mt-1"><?php echo $stats['new_users_last_30_days']; ?></p></div>
            </div>

            <div class="bg-gray-800 rounded-2xl p-4 mb-6">
                <form action="admin.php" method="GET" class="flex items-center gap-4">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Cerca per username o email..." class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-primary-500 focus:border-primary-500">
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-4 rounded-lg">Cerca</button>
                    <a href="admin.php" class="bg-gray-600 hover:bg-gray-500 text-white font-semibold py-2 px-4 rounded-lg">Resetta</a>
                </form>
            </div>

            <div class="bg-gray-800 rounded-2xl p-4 mb-6">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-gray-300">Azioni di gruppo per utenti selezionati:</span>
                    <div class="flex items-center gap-2">
                        <button data-action="suspend" class="bulk-action-btn p-2 hover:bg-gray-700 rounded-full" title="Sospendi account selezionati">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                        </button>
                        <button data-action="reactivate" class="bulk-action-btn p-2 hover:bg-gray-700 rounded-full" title="Riattiva account selezionati">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </button>
                        <button data-action="delete" class="bulk-action-btn p-2 hover:bg-gray-700 rounded-full" title="Elimina account selezionati">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                        <button data-action="disable_emails" class="bulk-action-btn p-2 hover:bg-gray-700 rounded-full" title="Disattiva ricezione email">
                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6" />
                            </svg>
                        </button>
                        <button data-action="enable_emails" class="bulk-action-btn p-2 hover:bg-gray-700 rounded-full" title="Attiva ricezione email">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-2xl p-2">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-sm text-gray-400 uppercase">
                            <tr>
                                <th class="p-4"><input type="checkbox" id="select-all-users" class="h-4 w-4 rounded bg-gray-900 border-gray-600 text-primary-600 focus:ring-primary-500"></th>
                                <th class="p-4">ID</th>
                                <th class="p-4">Username</th>
                                <th class="p-4">Email</th>
                                <th class="p-4">Stato Account</th>
                                <th class="p-4">Abbonamento</th>
                                <th class="p-4">Stato Email</th>
                                <th class="p-4 text-left">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="text-white">
                            <?php if (empty($users_list)): ?>
                                <tr><td colspan="7" class="text-center p-6 text-gray-400">Nessun utente trovato.</td></tr>
                            <?php else: ?>
                                <?php foreach ($users_list as $user): ?>
                                <tr class="border-b border-gray-700 last:border-b-0">
                                    <td class="p-4"><input type="checkbox" name="user_ids[]" value="<?php echo $user['id']; ?>" class="user-checkbox h-4 w-4 rounded bg-gray-900 border-gray-600 text-primary-600 focus:ring-primary-500"></td>
                                    <td class="p-4"><?php echo $user['id']; ?></td>
                                    <td class="p-4 font-semibold"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full <?php echo $user['account_status'] === 'active' ? 'bg-green-500' : 'bg-red-500'; ?>"></span>
                                            <span><?php echo ucfirst($user['account_status']); ?></span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 font-semibold leading-tight text-xs rounded-full <?php
                                            switch($user['subscription_status']) {
                                                case 'active': echo 'bg-green-700 text-green-100'; break;
                                                case 'lifetime': echo 'bg-indigo-700 text-indigo-100'; break;
                                                case 'pending_cancellation': echo 'bg-yellow-700 text-yellow-100'; break;
                                                default: echo 'bg-gray-700 text-gray-100';
                                            }
                                        ?>">
                                            <?php echo ucfirst($user['subscription_status']); ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if ($user['receives_emails']): ?>
                                            <span title="Email attive"><svg class="w-5 h-5 text-green-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></span>
                                        <?php else: ?>
                                            <span title="Email disattivate"><svg class="w-5 h-5 text-yellow-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6" /></svg></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center space-x-2">
                                            <button onclick='openUserInfoModal(<?php echo json_encode($user); ?>)' class="p-2 hover:bg-gray-700 rounded-full" title="Mostra dettagli utente"><svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></button>
                                            <button onclick='openSubscriptionModal(<?php echo json_encode($user); ?>)' class="p-2 hover:bg-gray-700 rounded-full" title="Gestisci Abbonamento"><svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01M12 6v-1m0-1V4m0 2.01M12 18v-2m0-2v-2m0-2V8m0 0h.01M12 18h.01M12 20h.01M12 4h.01M4 12h-2m14 0h2m-7-7v2m0-2V3m2 7h-2m-2 0h-2m7-2v-2m0 2v2m0 0v2m0-2h2m-2-2h-2m-2-2v2m-2-2v-2m2 7h2m-2-2h-2m-2 2v-2m2-2v2"></path></svg></button>
                                            <?php if ($user['id'] != 1): ?>
                                                <button onclick='toggleEmailStatus(<?php echo $user['id']; ?>)' class="p-2 hover:bg-gray-700 rounded-full" title="Attiva/Disattiva Email"><svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></button>
                                                <a href="impersonate.php?id=<?php echo $user['id']; ?>" class="p-2 hover:bg-gray-700 rounded-full" title="Accedi come <?php echo htmlspecialchars($user['username']); ?>"><svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></a>
                                                <button onclick='openSuspendModal(<?php echo json_encode($user); ?>)' class="p-2 hover:bg-gray-700 rounded-full" title="Sospendi/Riattiva Utente"><svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-between items-center mt-6">
                <span class="text-sm text-gray-400">Pagina <?php echo $current_page_number; ?> di <?php echo $total_pages > 0 ? $total_pages : 1; ?></span>
                <div class="flex gap-2">
                    <?php if ($current_page_number > 1): ?>
                        <a href="?page=<?php echo $current_page_number - 1; ?>&search=<?php echo urlencode($search_term); ?>" class="bg-gray-700 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg">&laquo; Precedente</a>
                    <?php endif; ?>
                    <?php if ($current_page_number < $total_pages): ?>
                        <a href="?page=<?php echo $current_page_number + 1; ?>&search=<?php echo urlencode($search_term); ?>" class="bg-gray-700 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg">Successivo &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <div id="user-info-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50 opacity-0 modal-backdrop" onclick="closeModal('user-info-modal')"></div>
        <div class="bg-gray-800 rounded-2xl w-full max-w-lg p-6 shadow-lg transform scale-95 opacity-0 modal-content">
            <div id="modal-feedback" class="mb-4"></div>
            <h2 class="text-2xl font-bold text-white mb-4">Dettagli Utente: <span id="modal-username" class="text-primary-400"></span></h2>
            <div class="space-y-2 text-gray-300">
                <p><strong>ID Utente:</strong> <span id="modal-userid" class="font-mono"></span></p>
                <p><strong>Email:</strong> <span id="modal-email"></span></p>
                <p><strong>Stato Account:</strong> <span id="modal-account-status" class="font-semibold"></span></p>
                <p><strong>Stato Abbonamento:</strong> <span id="modal-sub-status" class="font-semibold"></span></p>
                <p><strong>Codice Amico:</strong> <span id="modal-friendcode" class="font-mono"></span></p>
                <hr class="border-gray-600 my-3">
                <p><strong>Ultimo Accesso:</strong> <span id="modal-last-login"></span></p>
                <p><strong>Sospeso Fino al:</strong> <span id="modal-suspended-until"></span></p>
                <hr class="border-gray-600 my-3">
                <p><strong>Stripe Customer ID:</strong> <span id="modal-stripe-customer" class="font-mono text-sm"></span></p>
                <p><strong>Stripe Subscription ID:</strong> <span id="modal-stripe-sub" class="font-mono text-sm"></span></p>
                <p><strong>Fine/Rinnovo Abbonamento:</strong> <span id="modal-sub-end"></span></p>
                <hr class="border-gray-600 my-3">
                <p><strong>Account Creato il:</strong> <span id="modal-created-at"></span></p>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-700 space-y-3">
                <h3 class="text-lg font-semibold text-white">Azioni Email</h3>
                <div class="flex items-center gap-4">
                    <select id="email-action-select" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Seleziona un'azione...</option>
                        <option value="reset_password">Invia link reset password</option>
                        <option value="verify_account">Reinvia email di verifica</option>
                        <option value="custom_message">Invia messaggio personalizzato</option>
                    </select>
                    <button id="send-email-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-opacity" disabled>Invia</button>
                </div>

                <div id="custom-message-fields" class="space-y-2 hidden">
                    <input type="text" id="custom-subject" placeholder="Oggetto..." class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 text-sm" required>
                    <textarea id="custom-message-body" placeholder="Scrivi il corpo del messaggio..." class="w-full h-32 bg-gray-700 text-white rounded-lg px-3 py-2 text-sm resize-none" required></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end items-center space-x-4">
                <button onclick="openUserEditModal()" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-5 rounded-lg">Modifica Dati Utente</button>
                <button onclick="closeModal('user-info-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Chiudi</button>
            </div>
        </div>
    </div>

    <div id="user-edit-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50 opacity-0 modal-backdrop" onclick="closeModal('user-edit-modal')"></div>
        <div class="bg-gray-800 rounded-2xl w-full max-w-lg p-6 shadow-lg transform scale-95 opacity-0 modal-content">
            <h2 class="text-2xl font-bold text-white mb-6">Modifica Utente</h2>
            <form id="edit-user-form" action="admin_update_user.php" method="POST">
                <input type="hidden" name="user_id" id="edit-user-id">
                <div class="space-y-4">
                    <div>
                        <label for="edit-email" class="block text-sm font-medium text-gray-300 mb-1">Indirizzo Email</label>
                        <input type="email" name="email" id="edit-email" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label for="edit-friend-code" class="block text-sm font-medium text-gray-300 mb-1">Codice Amico</label>
                        <input type="text" name="friend_code" id="edit-friend-code" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 font-mono uppercase" maxlength="8">
                    </div>
                    <div>
                        <label for="edit-password" class="block text-sm font-medium text-gray-300 mb-1">Nuova Password (lasciare vuoto per non modificare)</label>
                        <input type="password" name="new_password" id="edit-password" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                    </div>
                </div>
                <div class="mt-8 flex justify-end space-x-4">
                    <button type="button" onclick="closeModal('user-edit-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Annulla</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg">Salva Modifiche</button>
                </div>
            </form>
        </div>
    </div>

    <div id="user-suspend-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50 opacity-0 modal-backdrop" onclick="closeModal('user-suspend-modal')"></div>
        <div class="bg-gray-800 rounded-2xl w-full max-w-lg p-6 shadow-lg transform scale-95 opacity-0 modal-content">
            <h2 class="text-2xl font-bold text-white mb-6">Gestisci Stato Utente</h2>
            <form id="suspend-user-form" action="admin_suspend_user.php" method="POST">
                <input type="hidden" name="user_id" id="suspend-user-id">
                <input type="hidden" name="action" id="suspend-action">

                <div id="suspend-options">
                    <p class="text-gray-300 mb-4">Sospendi l'accesso per <strong id="suspend-username"></strong>.</p>
                    <label for="suspend-until" class="block text-sm font-medium text-gray-300 mb-1">Sospendi fino al (lascia vuoto per sospensione a tempo indeterminato):</label>
                    <input type="date" name="suspended_until" id="suspend-until" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                </div>

                <p id="reactivate-message" class="hidden text-gray-300 mb-4">Sei sicuro di voler riattivare l'account per <strong id="reactivate-username"></strong>?</p>

                <div class="mt-8 flex justify-end space-x-4">
                    <button type="button" onclick="closeModal('user-suspend-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Annulla</button>
                    <button type="submit" id="suspend-submit-button" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-5 rounded-lg">Sospendi</button>
                </div>
            </form>
        </div>
    </div>

    <div id="user-subscription-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50 opacity-0 modal-backdrop" onclick="closeModal('user-subscription-modal')"></div>
        <div class="bg-gray-800 rounded-2xl w-full max-w-lg p-6 shadow-lg transform scale-95 opacity-0 modal-content">
            <h2 class="text-2xl font-bold text-white mb-6">Gestisci Abbonamento: <span id="sub-username" class="text-primary-400"></span></h2>
            <form id="subscription-form" action="update_user_status.php" method="POST">
                <input type="hidden" name="user_id_to_update" id="sub-user-id">
                <div class="space-y-4">
                    <div>
                        <label for="sub-status" class="block text-sm font-medium text-gray-300 mb-1">Nuovo Stato Abbonamento</label>
                        <select name="new_status" id="sub-status" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                            <option value="free">Free</option>
                            <option value="active">Active</option>
                            <option value="lifetime">Lifetime</option>
                            <option value="pending_cancellation">Pending Cancellation</option>
                            <option value="canceled">Canceled</option>
                            <option value="past_due">Past Due</option>
                        </select>
                    </div>
                </div>
                <div class="mt-8 flex justify-end space-x-4">
                    <button type="button" onclick="closeModal('user-subscription-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Annulla</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg">Salva Stato</button>
                </div>
            </form>
        </div>
    </div>

    <div id="confirm-email-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50 opacity-0 modal-backdrop" onclick="closeModal('confirm-email-modal')"></div>
        <div class="bg-gray-800 rounded-2xl w-full max-w-md p-6 shadow-lg transform scale-95 opacity-0 modal-content">
            <h2 class="text-2xl font-bold text-white mb-4">Notifica Utente</h2>
            <p class="text-gray-300 mb-6">Vuoi inviare un'email di notifica all'utente riguardo al cambio di piano?</p>
            <div class="flex justify-end space-x-4">
                <button id="confirm-email-no" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">No, Cambia Solo</button>
                <button id="confirm-email-yes" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg">Sì, Invia Email</button>
            </div>
        </div>
    </div>

<script>
    let currentUserData = null;

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        const backdrop = modal.querySelector('.modal-backdrop');
        const content = modal.querySelector('.modal-content');
        modal.classList.remove('hidden');
        setTimeout(() => {
            backdrop?.classList.remove('opacity-0');
            content?.classList.remove('opacity-0', 'scale-95');
        }, 10);
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        const backdrop = modal.querySelector('.modal-backdrop');
        const content = modal.querySelector('.modal-content');
        backdrop?.classList.add('opacity-0');
        content?.classList.add('opacity-0', 'scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    function toggleEmailStatus(userId) {
        if (confirm("Sei sicuro di voler cambiare lo stato di ricezione email per questo utente?")) {
            window.location.href = 'admin_toggle_email.php?id=' + userId;
        }
    }

    function formatDate(dateString) {
        if (!dateString || dateString === '0000-00-00 00:00:00') return 'N/A';
        const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleDateString('it-IT', options);
    }

    function sendAdminEmail(userId, emailType, params = {}) {
        let allParams = { user_id: userId, email_type: emailType, ...params };

        let userAction = `eseguire l'azione ${emailType}`;
        if (emailType === 'reset_password') userAction = 'inviare un link di reset password';
        if (emailType === 'verify_account') userAction = 'reinviare l\'email di verifica';
        if (emailType === 'custom_message') userAction = 'inviare un messaggio personalizzato';

        const feedbackDiv = document.getElementById('modal-feedback');
        feedbackDiv.className = 'p-4 text-sm rounded-lg bg-blue-900 text-blue-300 mb-4';
        feedbackDiv.textContent = 'Invio email in corso...';

        return fetch('admin_send_email.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(allParams)
        })
        .then(response => response.json())
        .then(data => {
            feedbackDiv.textContent = data.message;
            feedbackDiv.className = data.success ? 'p-4 text-sm rounded-lg bg-green-900 text-green-300 mb-4' : 'p-4 text-sm rounded-lg bg-red-900 text-red-300 mb-4';
            setTimeout(() => { feedbackDiv.textContent = ''; feedbackDiv.className = ''; }, 5000);
            return data;
        })
        .catch(error => {
            console.error('Errore:', error);
            feedbackDiv.textContent = 'Si è verificato un errore di rete.';
            feedbackDiv.className = 'p-4 text-sm rounded-lg bg-red-900 text-red-300 mb-4';
            setTimeout(() => { feedbackDiv.textContent = ''; feedbackDiv.className = ''; }, 5000);
            throw error;
        });
    }

    function openUserInfoModal(user) {
        currentUserData = user;

        document.getElementById('modal-feedback').textContent = '';
        document.getElementById('modal-feedback').className = '';

        document.getElementById('modal-username').textContent = user.username;
        document.getElementById('modal-userid').textContent = user.id;
        document.getElementById('modal-email').textContent = user.email;
        document.getElementById('modal-account-status').textContent = user.account_status;
        document.getElementById('modal-sub-status').textContent = user.subscription_status;
        document.getElementById('modal-friendcode').textContent = user.friend_code || 'N/A';
        document.getElementById('modal-last-login').textContent = formatDate(user.last_login_at);
        document.getElementById('modal-suspended-until').textContent = formatDate(user.suspended_until);

        const customerIdSpan = document.getElementById('modal-stripe-customer');
        if (user.stripe_customer_id) {
            const stripeUrl = `https://dashboard.stripe.com/test/customers/${user.stripe_customer_id}`;
            customerIdSpan.innerHTML = `<a href=\"${stripeUrl}\" target=\"_blank\" class=\"text-indigo-400 hover:underline\">${user.stripe_customer_id}</a>`;
        } else {
            customerIdSpan.textContent = 'N/A';
        }
        document.getElementById('modal-stripe-sub').textContent = user.stripe_subscription_id || 'N/A';
        document.getElementById('modal-sub-end').textContent = formatDate(user.subscription_end_date);
        document.getElementById('modal-created-at').textContent = formatDate(user.created_at);

        // Reset dei campi e dello stato del menu a tendina
        document.getElementById('email-action-select').value = '';
        document.getElementById('send-email-btn').disabled = true;
        document.getElementById('custom-message-fields').classList.add('hidden');
        document.getElementById('custom-subject').value = '';
        document.getElementById('custom-message-body').value = '';

        const verifyBtn = document.getElementById('send-verify-btn');
        if (verifyBtn) {
            if (user.is_verified) {
                verifyBtn.disabled = true;
                verifyBtn.classList.add('opacity-50', 'cursor-not-allowed');
                verifyBtn.title = 'L\'utente è già verificato';
            } else {
                verifyBtn.disabled = false;
                verifyBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                verifyBtn.title = 'Invia email di verifica';
            }
        }

        openModal('user-info-modal');
    }

    function openUserEditModal() {
        if (!currentUserData) return;
        document.getElementById('edit-user-id').value = currentUserData.id;
        document.getElementById('edit-email').value = currentUserData.email;
        document.getElementById('edit-friend-code').value = currentUserData.friend_code;
        document.getElementById('edit-password').value = '';
        closeModal('user-info-modal');
        openModal('user-edit-modal');
    }

    function openSuspendModal(user) {
        document.getElementById('suspend-user-id').value = user.id;
        const suspendOptions = document.getElementById('suspend-options');
        const reactivateMessage = document.getElementById('reactivate-message');
        const suspendUsername = document.getElementById('suspend-username');
        const reactivateUsername = document.getElementById('reactivate-username');
        const submitButton = document.getElementById('suspend-submit-button');
        const actionInput = document.getElementById('suspend-action');

        if (user.account_status === 'active') {
            suspendOptions.style.display = 'block';
            reactivateMessage.style.display = 'none';
            suspendUsername.textContent = user.username;
            submitButton.textContent = 'Sospendi';
            submitButton.classList.remove('bg-green-600', 'hover:bg-green-700');
            submitButton.classList.add('bg-red-600', 'hover:bg-red-700');
            actionInput.value = 'suspend';
        } else {
            suspendOptions.style.display = 'none';
            reactivateMessage.style.display = 'block';
            reactivateUsername.textContent = user.username;
            submitButton.textContent = 'Riattiva';
            submitButton.classList.remove('bg-red-600', 'hover:bg-red-700');
            submitButton.classList.add('bg-green-600', 'hover:bg-green-700');
            actionInput.value = 'reactivate';
        }
        openModal('user-suspend-modal');
    }

    function openSubscriptionModal(user) {
        currentUserData = user;
        document.getElementById('sub-user-id').value = user.id;
        document.getElementById('sub-username').textContent = user.username;
        document.getElementById('sub-status').value = user.subscription_status;
        openModal('user-subscription-modal');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const menuButton = document.getElementById('menu-button');
        const sidebarBackdrop = document.getElementById('sidebar-backdrop');
        const emailActionSelect = document.getElementById('email-action-select');
        const sendEmailBtn = document.getElementById('send-email-btn');
        const customMessageFields = document.getElementById('custom-message-fields');
        const modalFeedback = document.getElementById('modal-feedback');

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

        const subForm = document.getElementById('subscription-form');
        if(subForm) {
            subForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const newPlan = document.getElementById('sub-status').value;
                const oldPlan = currentUserData.subscription_status;

                if (newPlan === oldPlan) {
                    closeModal('user-subscription-modal');
                    return;
                }

                closeModal('user-subscription-modal');
                openModal('confirm-email-modal');
                document.getElementById('confirm-email-yes').onclick = () => {
                    sendAdminEmail(currentUserData.id, 'plan_changed', {
                        old_plan: oldPlan,
                        new_plan: newPlan
                    }).then(() => {
                        subForm.submit(); // solo dopo che l'email è stata inviata
                    }).catch(() => {
                        // anche se fallisce, aggiorna lo stesso il piano
                        subForm.submit();
                    });
                };

                document.getElementById('confirm-email-no').onclick = () => {
                    subForm.submit();
                };
            });
        }

        // Listener per il menu a tendina
        if (emailActionSelect && sendEmailBtn && customMessageFields) {
            emailActionSelect.addEventListener('change', () => {
                const selectedAction = emailActionSelect.value;
                if (selectedAction) {
                    sendEmailBtn.disabled = false;
                    if (selectedAction === 'custom_message') {
                        customMessageFields.classList.remove('hidden');
                    } else {
                        customMessageFields.classList.add('hidden');
                    }
                } else {
                    sendEmailBtn.disabled = true;
                    customMessageFields.classList.add('hidden');
                }

            });

            // Listener per il pulsante di invio
            sendEmailBtn.addEventListener('click', () => {
                const selectedAction = emailActionSelect.value;
                if (!currentUserData || !selectedAction) {
                    return;
                }

                if (selectedAction === 'custom_message') {
                    const subject = document.getElementById('custom-subject').value;
                    const messageBody = document.getElementById('custom-message-body').value;
                    if (subject && messageBody) {
                        sendAdminEmail(currentUserData.id, selectedAction, { subject: subject, body: messageBody });
                    } else {
                        modalFeedback.className = 'p-4 text-sm rounded-lg bg-yellow-900 text-yellow-300 mb-4';
                        modalFeedback.textContent = 'Oggetto e corpo del messaggio sono obbligatori.';
                        setTimeout(() => { modalFeedback.textContent = ''; modalFeedback.className = ''; }, 5000);
                    }
                } else {
                    if (confirm("Sei sicuro di voler inviare questa email?")) {
                        sendAdminEmail(currentUserData.id, selectedAction);
                    }
                }
            });
        }
    });

</script>

<div id="bulk-action-confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <div class="fixed inset-0 bg-black bg-opacity-50 opacity-0 modal-backdrop" onclick="closeModal('bulk-action-confirm-modal')"></div>
    <div class="bg-gray-800 rounded-2xl w-full max-w-md p-6 shadow-lg transform scale-95 opacity-0 modal-content">
        <h2 class="text-2xl font-bold text-white mb-4">Conferma Azione di Gruppo</h2>
        <p id="bulk-action-modal-message" class="text-gray-300 mb-6">Sei sicuro di voler procedere?</p>
        <div class="flex justify-end space-x-4">
            <button type="button" onclick="closeModal('bulk-action-confirm-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Annulla</button>
            <button type="button" id="bulk-action-confirm-button" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-5 rounded-lg">Conferma</button>
        </div>
    </div>
</div>
<script src="admin_bulk_actions.js"></script>
</body>
</html>
