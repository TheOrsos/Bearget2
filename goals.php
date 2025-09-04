<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}
require_once 'db_connect.php';
require_once 'functions.php';

$user_id = $_SESSION["id"];
$current_page = 'goals';

// CONTROLLO ACCESSO PRO
$user = get_user_by_id($conn, $user_id);
if ($user['subscription_status'] !== 'active' && $user['subscription_status'] !== 'lifetime') {
    header("location: pricing.php?message=Accedi agli obiettivi con un piano Premium!");
    exit;
}

$goals = get_saving_goals($conn, $user_id);
$accounts = get_user_accounts($conn, $user_id);
// FIX: Corrected function name and parameter order
$savingCategory = get_category_by_name_for_user($conn, $user_id, 'Risparmi');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obiettivi di Risparmio - Bearget</title>
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
        .row-fade-out { transition: opacity 0.5s ease-out; opacity: 0; }
    </style>
</head>
<body class="text-gray-200">

    <div class="flex h-screen">
        <div id="sidebar-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 p-6 lg:p-10 overflow-y-auto">
            <header class="flex flex-wrap justify-between items-center gap-4 mb-8">
                <div class="flex items-center gap-4">
                    <button id="menu-button" type="button" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <span class="sr-only">Apri menu principale</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Obiettivi di Risparmio
                        </h1>
                        <p class="text-gray-400 mt-1">Traccia i progressi verso i tuoi sogni.</p>
                    </div>
                </div>
                <button onclick="openModal('add-goal-modal')" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-5 rounded-lg flex items-center transition-colors shadow-lg hover:shadow-primary-500/50">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Crea Obiettivo
                </button>
            </header>

            <div id="goals-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php if (empty($goals)): ?>
                    <div id="empty-state-goals" class="md:col-span-2 xl:col-span-3 bg-gray-800 rounded-2xl p-10 text-center flex flex-col items-center">
                        <svg class="w-16 h-16 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.25278C12 6.25278 10.8333 5 9.5 5C8.16667 5 7 6.25278 7 6.25278V9.74722C7 9.74722 8.16667 11 9.5 11C10.8333 11 12 9.74722 12 9.74722V6.25278ZM12 6.25278C12 6.25278 13.1667 5 14.5 5C15.8333 5 17 6.25278 17 6.25278V9.74722C17 9.74722 15.8333 11 14.5 11C13.1667 11 12 9.74722 12 9.74722V6.25278Z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11V14"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14H15"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17H15"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20H14"></path></svg>
                        <h3 class="text-lg font-semibold text-white">Nessun obiettivo impostato</h3>
                        <p class="text-gray-400 max-w-sm mx-auto mt-1">Creare un obiettivo di risparmio è il primo passo per realizzarlo. Inizia ora!</p>
                        <button onclick="openModal('add-goal-modal')" class="mt-6 bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-5 rounded-lg flex items-center transition-colors shadow-lg hover:shadow-primary-500/50">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Crea il tuo primo obiettivo
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($goals as $goal):
                        $percentage = ($goal['target_amount'] > 0) ? ($goal['current_amount'] / $goal['target_amount']) * 100 : 0;
                    ?>
                    <div class="bg-gray-800 rounded-2xl p-5 flex flex-col transition-transform duration-200 hover:-translate-y-1 hover:shadow-2xl" data-goal-id="<?php echo $goal['id']; ?>">
                        <div class="flex-grow">
                            <div class="flex justify-between items-start">
                                <h3 class="text-xl font-bold text-white mb-2 goal-name"><?php echo htmlspecialchars($goal['name']); ?></h3>
                                <div class="flex items-center space-x-1">
                                    <button onclick='openEditGoalModal(<?php echo json_encode($goal); ?>)' title="Modifica Obiettivo" class="p-2 text-gray-400 hover:text-blue-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536L16.732 3.732z"></path></svg></button>
                                    <button onclick="openHistoryModal(<?php echo $goal['id']; ?>, '<?php echo htmlspecialchars(addslashes($goal['name'])); ?>')" title="Cronologia Contributi" class="p-2 text-gray-400 hover:text-green-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></button>
                                    <form action="delete_goal.php" method="POST" class="delete-form">
                                        <input type="hidden" name="goal_id" value="<?php echo $goal['id']; ?>">
                                        <button type="submit" title="Elimina Obiettivo" class="p-2 text-gray-400 hover:text-red-400">&times;</button>
                                    </form>
                                </div>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2.5 mb-2">
                                <div class="bg-green-500 h-2.5 rounded-full progress-bar" style="width: <?php echo min($percentage, 100); ?>%"></div>
                            </div>
                            <div class="flex justify-between text-sm text-gray-300">
                                <span class="current-amount">€<?php echo number_format($goal['current_amount'], 2, ',', '.'); ?></span>
                                <span class="target-amount text-gray-400">di €<?php echo number_format($goal['target_amount'], 2, ',', '.'); ?></span>
                            </div>
                            <?php if (!empty($goal['target_date'])): ?>
                            <div class="text-sm text-gray-400 mt-3 flex items-center gap-2 goal-date">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>Scadenza: <?php echo date('d/m/Y', strtotime($goal['target_date'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4">
                            <button onclick="openContributionModal(<?php echo $goal['id']; ?>, '<?php echo htmlspecialchars(addslashes($goal['name'])); ?>')" class="w-full bg-gray-700 hover:bg-gray-600 text-white font-semibold py-2 rounded-lg">Aggiungi Fondi</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modale Aggiungi Obiettivo -->
    <div id="add-goal-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50 opacity-0 modal-backdrop" onclick="closeModal('add-goal-modal')"></div>
        <div class="bg-gray-800 rounded-2xl w-full max-w-md p-6 transform scale-95 opacity-0 modal-content">
            <h2 class="text-2xl font-bold text-white mb-6">Crea Nuovo Obiettivo</h2>
            <form id="add-goal-form" action="add_goal.php" method="POST" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Nome Obiettivo</label>
                    <input type="text" name="name" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label for="target_amount" class="block text-sm font-medium text-gray-300 mb-1">Importo Obiettivo (€)</label>
                    <input type="number" step="0.01" name="target_amount" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label for="target_date" class="block text-sm font-medium text-gray-300 mb-1">Data di Scadenza (Opzionale)</label>
                    <input type="date" name="target_date" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div class="pt-4 flex justify-end space-x-4">
                    <button type="button" onclick="closeModal('add-goal-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Annulla</button>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-5 rounded-lg">Crea</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale Modifica Obiettivo -->
    <div id="edit-goal-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50 opacity-0 modal-backdrop" onclick="closeModal('edit-goal-modal')"></div>
        <div class="bg-gray-800 rounded-2xl w-full max-w-md p-6 transform scale-95 opacity-0 modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-white">Modifica Obiettivo</h2>
                <span id="edit-goal-prefixed-id" class="text-sm font-mono text-gray-400"></span>
            </div>
            <form id="edit-goal-form" action="update_goal.php" method="POST" class="space-y-4">
                <input type="hidden" name="goal_id" id="edit-goal-id">
                <div>
                    <label for="edit-name" class="block text-sm font-medium text-gray-300 mb-1">Nome Obiettivo</label>
                    <input type="text" name="name" id="edit-name" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label for="edit-target-amount" class="block text-sm font-medium text-gray-300 mb-1">Importo Obiettivo (€)</label>
                    <input type="number" step="0.01" name="target_amount" id="edit-target-amount" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label for="edit-target-date" class="block text-sm font-medium text-gray-300 mb-1">Data di Scadenza (Opzionale)</label>
                    <input type="date" name="target_date" id="edit-target-date" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div class="pt-4 flex justify-end space-x-4">
                    <button type="button" onclick="closeModal('edit-goal-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Annulla</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg">Salva Modifiche</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale Aggiungi Contributo -->
    <div id="add-contribution-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50 opacity-0 modal-backdrop" onclick="closeModal('add-contribution-modal')"></div>
        <div class="bg-gray-800 rounded-2xl w-full max-w-md p-6 transform scale-95 opacity-0 modal-content">
            <h2 class="text-2xl font-bold text-white mb-2">Aggiungi Fondi</h2>
            <p id="contribution-goal-name" class="text-gray-400 mb-6"></p>
            <form id="add-contribution-form" action="add_contribution.php" method="POST" class="space-y-4">
                <input type="hidden" name="goal_id" id="contribution-goal-id">
                <input type="hidden" name="category_id" value="<?php echo $savingCategory['id'] ?? ''; ?>">
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-300 mb-1">Importo da Aggiungere (€)</label>
                    <input type="number" step="0.01" name="amount" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label for="account_id" class="block text-sm font-medium text-gray-300 mb-1">Preleva da Conto</label>
                    <select name="account_id" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                        <?php foreach($accounts as $account): ?>
                            <option value="<?php echo $account['id']; ?>"><?php echo htmlspecialchars($account['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!isset($savingCategory['id'])): ?>
                    <p class="text-sm text-yellow-400">Attenzione: Categoria 'Risparmi' non trovata. Il contributo non creerà una transazione di spesa.</p>
                <?php endif; ?>
                <div class="pt-4 flex justify-end space-x-4">
                    <button type="button" onclick="closeModal('add-contribution-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Annulla</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg">Aggiungi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale di Conferma Eliminazione Obiettivo -->
    <div id="confirm-delete-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-60 opacity-0 modal-backdrop" onclick="closeModal('confirm-delete-modal')"></div>
        <div class="bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 transform scale-95 opacity-0 modal-content text-center">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-900">
                <svg class="h-6 w-6 text-red-400" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg leading-6 font-bold text-white mt-4">Eliminare Obiettivo?</h3>
            <p class="mt-2 text-sm text-gray-400">Questa azione è irreversibile. Sei sicuro di voler continuare?</p>
            <div class="mt-8 flex justify-center space-x-4">
                <button id="confirm-delete-btn" type="button" class="bg-danger hover:bg-red-700 text-white font-semibold py-2 px-5 rounded-lg">Elimina</button>
                <button type="button" onclick="closeModal('confirm-delete-modal')" class="bg-gray-700 hover:bg-gray-600 text-gray-300 font-semibold py-2 px-5 rounded-lg">Annulla</button>
            </div>
        </div>
    </div>

    <!-- Modale Cronologia Contributi -->
    <div id="history-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50 opacity-0 modal-backdrop" onclick="closeModal('history-modal')"></div>
        <div class="bg-gray-800 rounded-2xl w-full max-w-2xl p-6 transform scale-95 opacity-0 modal-content">
            <h2 class="text-2xl font-bold text-white mb-2">Cronologia per <span id="history-goal-name" class="text-primary-500"></span></h2>
            <p class="text-gray-400 mb-6">Visualizza, modifica o elimina i singoli contributi.</p>
            <div id="history-list-container" class="max-h-96 overflow-y-auto space-y-3 pr-2">
                <div id="history-loading" class="text-center p-8"><p class="text-gray-400">Caricamento cronologia...</p></div>
            </div>
             <div class="mt-8 flex justify-end">
                <button type="button" onclick="closeModal('history-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Chiudi</button>
            </div>
        </div>
    </div>

    <!-- Modale Modifica Contributo -->
    <div id="edit-contribution-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50 opacity-0 modal-backdrop" onclick="closeModal('edit-contribution-modal')"></div>
        <div class="bg-gray-800 rounded-2xl w-full max-w-md p-6 transform scale-95 opacity-0 modal-content">
            <h2 class="text-2xl font-bold text-white mb-6">Modifica Contributo</h2>
            <form id="edit-contribution-form" action="edit_contribution.php" method="POST" class="space-y-4">
                <input type="hidden" name="transaction_id" id="edit-contribution-tx-id">
                <div>
                    <label for="edit-contribution-amount" class="block text-sm font-medium text-gray-300 mb-1">Nuovo Importo (€)</label>
                    <input type="number" step="0.01" name="amount" id="edit-contribution-amount" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label for="edit-contribution-date" class="block text-sm font-medium text-gray-300 mb-1">Nuova Data</label>
                    <input type="date" name="date" id="edit-contribution-date" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div class="pt-4 flex justify-end space-x-4">
                    <button type="button" onclick="closeModal('edit-contribution-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Annulla</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg">Salva Modifiche</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale di Conferma Eliminazione Contributo -->
    <div id="confirm-delete-contribution-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-60 opacity-0 modal-backdrop" onclick="closeModal('confirm-delete-contribution-modal')"></div>
        <div class="bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 transform scale-95 opacity-0 modal-content text-center">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-900">
                <svg class="h-6 w-6 text-red-400" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg leading-6 font-bold text-white mt-4">Eliminare Contributo?</h3>
            <p class="mt-2 text-sm text-gray-400">Questa azione è irreversibile e ripristinerà l'importo sul conto di origine. Sei sicuro?</p>
            <div class="mt-8 flex justify-center space-x-4">
                <button id="confirm-delete-contribution-btn" type="button" class="bg-danger hover:bg-red-700 text-white font-semibold py-2 px-5 rounded-lg">Elimina</button>
                <button type="button" onclick="closeModal('confirm-delete-contribution-modal')" class="bg-gray-700 hover:bg-gray-600 text-gray-300 font-semibold py-2 px-5 rounded-lg">Annulla</button>
            </div>
        </div>
    </div>

    <!-- Modale per Note Transazione (riutilizzato) -->
    <div id="note-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop" onclick="closeModal('note-modal')"></div>
        <div class="bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg p-6 transform scale-95 opacity-0 modal-content">
            <h2 class="text-2xl font-bold text-white mb-4">Nota del Contributo</h2>
            <form id="note-form">
                <input type="hidden" id="note-transaction-id" name="transaction_id">
                <textarea id="note-content" name="note_content" rows="6" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2" placeholder="Scrivi qui la tua nota..."></textarea>
                <div class="mt-6 flex justify-end space-x-4">
                    <button type="button" onclick="closeModal('note-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Annulla</button>
                    <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-5 rounded-lg">Salva Nota</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // --- FUNZIONI DI BASE (MODALI, TOAST, ESCAPE) ---
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            const backdrop = modal.querySelector('.modal-backdrop');
            const content = modal.querySelector('.modal-content');
            modal.classList.remove('hidden');
            setTimeout(() => {
                if(backdrop) backdrop.classList.remove('opacity-0');
                if(content) content.classList.remove('opacity-0', 'scale-95');
            }, 10);
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            const backdrop = modal.querySelector('.modal-backdrop');
            const content = modal.querySelector('.modal-content');
            if(backdrop) backdrop.classList.add('opacity-0');
            if(content) content.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast-notification');
            if (!toast) return;
            const toastMessage = document.getElementById('toast-message');
            const toastIcon = document.getElementById('toast-icon');
            toastMessage.textContent = message;
            toast.classList.remove('bg-success', 'bg-danger');
            if (type === 'success') {
                toast.classList.add('bg-success');
                toastIcon.innerHTML = `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>`;
            } else {
                toast.classList.add('bg-danger');
                toastIcon.innerHTML = `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"></path></svg>`;
            }
            toast.classList.remove('hidden', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('opacity-0');
                setTimeout(() => toast.classList.add('hidden'), 300);
            }, 5000);
        }

        function escapeHTML(str) { const div = document.createElement('div'); div.textContent = str; return div.innerHTML; }

        let currentOpenGoalId = null;

        function openEditGoalModal(goal) {
    document.getElementById('edit-goal-prefixed-id').textContent = 'ID: GOAL-' + goal.id;
    document.getElementById('edit-goal-id').value = goal.id;
    document.getElementById('edit-name').value = goal.name;
    document.getElementById('edit-target-amount').value = goal.target_amount;
    document.getElementById('edit-target-date').value = goal.target_date;
    openModal('edit-goal-modal');
}

function openContributionModal(goalId, goalName) {
    document.getElementById('contribution-goal-id').value = goalId;
    document.getElementById('contribution-goal-name').textContent = `a "${goalName}"`;
    openModal('add-contribution-modal');
}

function openNoteModal(transactionId) {
    document.getElementById('note-transaction-id').value = transactionId;
    const noteContentTextarea = document.getElementById('note-content');
    noteContentTextarea.value = 'Caricamento...';
    fetch(`ajax_note_handler.php?action=get_note&transaction_id=${transactionId}`)
        .then(res => res.json())
        .then(data => {
            noteContentTextarea.value = data.success ? data.content : '';
            if(!data.success) showToast(data.message || 'Impossibile caricare la nota.', 'error');
        })
        .catch(() => {
            noteContentTextarea.value = '';
            showToast('Errore di rete nel caricare la nota.', 'error');
        });
    openModal('note-modal');
}

function openHistoryModal(goalId, goalName) {
    currentOpenGoalId = goalId;
    document.getElementById('history-goal-name').textContent = goalName;
    const container = document.getElementById('history-list-container');
    const loadingEl = document.getElementById('history-loading');
    container.innerHTML = '';
    loadingEl.style.display = 'block';
    container.appendChild(loadingEl);
    openModal('history-modal');

    fetch(`ajax_get_goal_contributions.php?goal_id=${goalId}`)
        .then(res => res.json())
        .then(data => {
            loadingEl.style.display = 'none';
            if (data.success && data.contributions.length > 0) {
                data.contributions.forEach(c => {
                    const date = new Date(c.transaction_date).toLocaleDateString('it-IT', { year: 'numeric', month: 'long', day: 'numeric' });
                    const amount = new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(Math.abs(c.amount));
                    
                    const historyItem = document.createElement('div');
                    historyItem.className = 'bg-gray-700/50 p-3 rounded-lg flex justify-between items-center contribution-item';
                    historyItem.setAttribute('data-transaction-id', c.id);
                    historyItem.setAttribute('data-amount', Math.abs(c.amount));
                    historyItem.setAttribute('data-date', c.transaction_date);
                    historyItem.innerHTML = `
                        <div>
                            <p class="font-bold text-white">${amount}</p>
                            <p class="text-sm text-gray-400">versato il ${date} dal conto '${escapeHTML(c.account_name)}'</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="openNoteModal(${c.id})" title="Aggiungi Nota" class="p-2 hover:bg-gray-600 rounded-full"><svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></button>
                            <button class="p-2 hover:bg-gray-600 rounded-full btn-edit-contribution"><svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536L16.732 3.732z"></path></svg></button>
                            <button class="p-2 hover:bg-gray-600 rounded-full btn-delete-contribution"><svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                        </div>
                    `;
                    container.appendChild(historyItem);
                });
            } else {
                container.innerHTML = '<p class="text-center text-gray-400 p-8">Nessun contributo trovato.</p>';
            }
        })
        .catch(err => {
            loadingEl.style.display = 'none';
            container.innerHTML = '<p class="text-center text-red-400 p-8">Errore nel caricamento della cronologia.</p>';
        });
}

function updateGoalCard(goalData) {
    const goalCard = document.querySelector(`[data-goal-id="${goalData.id}"]`);
    if (goalCard) {
        const currentAmountEl = goalCard.querySelector('.current-amount');
        const progressBarEl = goalCard.querySelector('.progress-bar');
        const nameEl = goalCard.querySelector('.goal-name');
        const targetEl = goalCard.querySelector('.target-amount');
        const dateEl = goalCard.querySelector('.goal-date');
        
        if(goalData.name) nameEl.textContent = escapeHTML(goalData.name);
        if(goalData.target_amount) targetEl.textContent = 'di ' + new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(goalData.target_amount);

        if (goalData.target_date) {
            const date = new Date(goalData.target_date);
            const formattedDate = date.toLocaleDateString('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric' });
            if(dateEl) {
                dateEl.querySelector('span').textContent = `Scadenza: ${formattedDate}`;
            } else {
                const newDateEl = document.createElement('div');
                newDateEl.className = 'text-sm text-gray-400 mt-3 flex items-center gap-2 goal-date';
                newDateEl.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg><span>Scadenza: ${formattedDate}</span>`;
                goalCard.querySelector('.flex-grow').appendChild(newDateEl);
            }
        } else {
            if (dateEl) dateEl.remove();
        }

        const currentAmount = goalData.current_amount !== undefined ? goalData.current_amount : parseFloat(currentAmountEl.textContent.replace(/[^0-9,.-]/g, '').replace('.', '').replace(',', '.'));
        const targetAmount = goalData.target_amount !== undefined ? goalData.target_amount : parseFloat(targetEl.textContent.replace(/[^0-9,.-]/g, '').replace('.', '').replace(',', '.'));
        
        const percentage = (targetAmount > 0) ? (currentAmount / targetAmount) * 100 : 0;
        currentAmountEl.textContent = new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(currentAmount);
        progressBarEl.style.width = `${Math.min(percentage, 100)}%`;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const addGoalForm = document.getElementById('add-goal-form');
    if(addGoalForm) {
        addGoalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(addGoalForm);
            fetch('add_goal.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message);
                        setTimeout(() => window.location.reload(), 500);
                    } else { showToast(data.message, 'error'); }
                }).catch(err => showToast('Errore di rete.', 'error'));
        });
    }

    const editGoalForm = document.getElementById('edit-goal-form');
    if(editGoalForm) {
        editGoalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(editGoalForm);
            fetch('update_goal.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message);
                        updateGoalCard(data.goal);
                        closeModal('edit-goal-modal');
                    } else { showToast(data.message, 'error'); }
                }).catch(err => showToast('Errore di rete.', 'error'));
        });
    }

    const addContributionForm = document.getElementById('add-contribution-form');
    if(addContributionForm) {
        addContributionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(addContributionForm);
            fetch('add_contribution.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message);
                        updateGoalCard(data.goal);
                        addContributionForm.reset();
                        closeModal('add-contribution-modal');
                    } else { showToast(data.message, 'error'); }
                }).catch(err => showToast('Errore di rete.', 'error'));
        });
    }

    const goalsGrid = document.getElementById('goals-grid');
    let formToDelete = null;
    if(goalsGrid) {
        goalsGrid.addEventListener('submit', function(e) {
            const form = e.target.closest('.delete-form');
            if (form) {
                e.preventDefault();
                formToDelete = form;
                openModal('confirm-delete-modal');
            }
        });
    }

    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    if(confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (formToDelete) {
                const formData = new FormData(formToDelete);
                fetch('delete_goal.php', { method: 'POST', body: formData })
                    .then(res => res.json()).then(data => {
                        if (data.success) {
                            showToast(data.message);
                            const card = formToDelete.closest('[data-goal-id]');
                            if(card) card.remove();
                        } else { showToast(data.message, 'error'); }
                    }).catch(err => showToast('Errore di rete.', 'error')).finally(() => {
                        closeModal('confirm-delete-modal');
                        formToDelete = null;
                    });
            }
        });
    }

    const historyContainer = document.getElementById('history-list-container');
    let transactionIdToDelete = null;
    if(historyContainer) {
        historyContainer.addEventListener('click', function(e) {
            const deleteButton = e.target.closest('.btn-delete-contribution');
            const editButton = e.target.closest('.btn-edit-contribution');
            
            if (deleteButton) {
                const item = deleteButton.closest('.contribution-item');
                transactionIdToDelete = item.getAttribute('data-transaction-id');
                openModal('confirm-delete-contribution-modal');
            }
            
            if (editButton) {
                const item = editButton.closest('.contribution-item');
                const txId = item.getAttribute('data-transaction-id');
                const amount = item.getAttribute('data-amount');
                const date = item.getAttribute('data-date');
                
                document.getElementById('edit-contribution-tx-id').value = txId;
                document.getElementById('edit-contribution-amount').value = amount;
                document.getElementById('edit-contribution-date').value = date;
                openModal('edit-contribution-modal');
            }
        });
    }

    const confirmDeleteContributionBtn = document.getElementById('confirm-delete-contribution-btn');
    if(confirmDeleteContributionBtn) {
        confirmDeleteContributionBtn.addEventListener('click', function() {
            if (!transactionIdToDelete) return;
            const formData = new FormData();
            formData.append('transaction_id', transactionIdToDelete);
            fetch('delete_contribution.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        const itemToRemove = historyContainer.querySelector(`[data-transaction-id="${transactionIdToDelete}"]`);
                        if (itemToRemove) itemToRemove.remove();
                        updateGoalCard(data.goal);
                    } else { showToast(data.message, 'error'); }
                })
                .catch(err => showToast('Errore di rete.', 'error'))
                .finally(() => {
                    closeModal('confirm-delete-contribution-modal');
                    transactionIdToDelete = null;
                });
        });
    }

    const editContributionForm = document.getElementById('edit-contribution-form');
    if(editContributionForm) {
        editContributionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(editContributionForm);
            fetch('edit_contribution.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        openHistoryModal(currentOpenGoalId, document.getElementById('history-goal-name').textContent);
                        updateGoalCard(data.goal);
                        closeModal('edit-contribution-modal');
                    } else { showToast(data.message, 'error'); }
                })
                .catch(err => showToast('Errore di rete.', 'error'));
        });
    }

    const noteForm = document.getElementById('note-form');
    if (noteForm) {
        noteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(noteForm);
            formData.append('action', 'save_note');
            fetch('ajax_note_handler.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Nota salvata.');
                        closeModal('note-modal');
                    } else { showToast(data.message, 'error'); }
                })
                .catch(() => showToast('Errore di rete.', 'error'));
        });
    }

    const sidebar = document.getElementById('sidebar');
    const menuButton = document.getElementById('menu-button');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');
    if(sidebar) {
        const toggleSidebar = () => {
            sidebar.classList.toggle('-translate-x-full');
            sidebarBackdrop.classList.toggle('hidden');
        };
        if (menuButton) { menuButton.addEventListener('click', toggleSidebar); }
        if (sidebarBackdrop) { sidebarBackdrop.addEventListener('click', toggleSidebar); }
    }
});
    </script>

</body>
</html>
