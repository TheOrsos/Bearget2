<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}
require_once 'db_connect.php';
require_once 'functions.php';

$user_id = $_SESSION["id"];
$filters = [
    'start_date' => $_GET['start_date'] ?? '',
    'end_date' => $_GET['end_date'] ?? '',
    'description' => $_GET['description'] ?? '',
    'category_id' => $_GET['category_id'] ?? '',
    'account_id' => $_GET['account_id'] ?? '',
    'tag_id' => $_GET['tag_id'] ?? ''
];

$allTransactions = get_all_transactions($conn, $user_id, $filters);
$userAccounts = get_user_accounts($conn, $user_id);
$expenseCategories = get_user_categories($conn, $user_id, 'expense');
$incomeCategories = get_user_categories($conn, $user_id, 'income');
$userTags = get_user_tags($conn, $user_id);
$user = get_user_by_id($conn, $user_id);
$is_pro_user = ($user['subscription_status'] === 'active' || $user['subscription_status'] === 'lifetime');
$export_query_string = http_build_query($filters);
$current_page = 'transactions';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transazioni - Bearget</title>
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
        .transition-all { transition: all 0.3s ease-in-out; }
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
                            Transazioni
                        </h1>
                        <p class="text-gray-400 mt-1">Visualizza, filtra e gestisci tutti i tuoi movimenti.</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button id="toggle-filter-btn" class="bg-gray-600 hover:bg-gray-500 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        <span>Filtri</span>
                    </button>
                    <button onclick="openModal('import-modal')" class="bg-gray-700 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors">Importa</button>
                    <a href="export_transactions.php?<?php echo $export_query_string; ?>" class="bg-gray-700 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors">Esporta</a>
                    <button onclick="openModal('add-transaction-modal')" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-5 rounded-lg flex items-center transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Aggiungi Movimento
                    </button>
                </div>
            </header>

            <!-- MODULO FILTRI -->
            <div id="filter-container" class="bg-gray-800 rounded-2xl p-4 mb-6 hidden transition-all duration-300">
                <form action="transactions.php" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 items-end">
                    <div>
                        <label for="start_date" class="text-sm font-medium text-gray-400">Da</label>
                        <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($filters['start_date']); ?>" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 mt-1 text-sm">
                    </div>
                    <div>
                        <label for="end_date" class="text-sm font-medium text-gray-400">A</label>
                        <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($filters['end_date']); ?>" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 mt-1 text-sm">
                    </div>
                    <div>
                        <label for="description" class="text-sm font-medium text-gray-400">Descrizione</label>
                        <input type="text" name="description" id="description" value="<?php echo htmlspecialchars($filters['description']); ?>" placeholder="Cerca..." class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 mt-1 text-sm">
                    </div>
                    <div>
                        <label for="account_id" class="text-sm font-medium text-gray-400">Conto</label>
                        <select name="account_id" id="account_id" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 mt-1 text-sm">
                            <option value="">Tutti i conti</option>
                            <?php foreach($userAccounts as $account): ?>
                                <option value="<?php echo $account['id']; ?>" <?php echo ($filters['account_id'] == $account['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($account['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="category_id" class="text-sm font-medium text-gray-400">Categoria</label>
                        <select name="category_id" id="category_id" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 mt-1 text-sm">
                            <option value="">Tutte le categorie</option>
                            <optgroup label="Spese">
                                <?php foreach($expenseCategories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($filters['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Entrate">
                                <?php foreach($incomeCategories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($filters['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div>
                        <label for="tag_id" class="text-sm font-medium text-gray-400">Etichetta</label>
                        <select name="tag_id" id="tag_id" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 mt-1 text-sm">
                            <option value="">Tutte le etichette</option>
                            <?php foreach($userTags as $tag): ?>
                                <option value="<?php echo $tag['id']; ?>" <?php echo ($filters['tag_id'] == $tag['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($tag['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex gap-2 lg:col-start-4 xl:col-start-6">
                        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-4 rounded-lg">Filtra</button>
                        <a href="transactions.php" class="w-full text-center bg-gray-600 hover:bg-gray-500 text-white font-semibold py-2 px-4 rounded-lg">Resetta</a>
                    </div>
                </form>
            </div>

            <div class="bg-gray-800 rounded-2xl p-2">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-sm text-gray-400 uppercase">
                            <tr>
                                <th class="p-4">Data</th>
                                <th class="p-4">Descrizione</th>
                                <th class="p-4">Categoria</th>
                                <th class="p-4">Conto</th>
                                <th class="p-4 text-right">Importo</th>
                                <th class="p-4 text-center">Azioni</th>
                            </tr>
                        </thead>
                        <tbody id="transactions-table-body" class="text-white">
                            <?php if (empty($allTransactions)): ?>
                                <tr id="empty-state-transactions"><td colspan="6" class="text-center p-10">Nessuna transazione trovata. Prova a modificare i filtri o aggiungi un nuovo movimento.</td></tr>
                            <?php else: ?>
                                <?php foreach ($allTransactions as $tx): ?>
                                <tr class="border-b border-gray-700 last:border-b-0 transition-colors hover:bg-gray-700/50" data-transaction-id="<?php echo $tx['id']; ?>">
                                    <td class="p-4 whitespace-nowrap date-cell"><?php echo date("d/m/Y", strtotime($tx['transaction_date'])); ?></td>
                                    <td class="p-4 font-semibold description-cell">
                                        <?php echo htmlspecialchars($tx['description']); ?>
                                        <?php if (!empty($tx['tags'])): ?>
                                            <div class="text-xs text-indigo-400 mt-1 font-normal tags-cell"><?php echo htmlspecialchars($tx['tags']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 category-cell"><?php echo htmlspecialchars($tx['category_name'] ?? 'N/A'); ?></td>
                                    <td class="p-4 text-gray-400 account-cell"><?php echo htmlspecialchars($tx['account_name']); ?></td>
                                    <td class="p-4 text-right font-bold amount-cell <?php echo $tx['amount'] > 0 ? 'text-green-400' : 'text-red-400'; ?>">
                                        <?php echo ($tx['amount'] > 0 ? '+' : '') . '€' . number_format($tx['amount'], 2, ',', '.'); ?>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-center items-center space-x-2 action-buttons">
                                            <button onclick="openNoteModal(<?php echo $tx['id']; ?>)" title="Aggiungi/Modifica Nota" class="p-2 hover:bg-gray-700 rounded-full transition-colors">
                                                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </button>
                                            <?php if (!empty($tx['invoice_path'])): ?>
                                                <a href="<?php echo htmlspecialchars($tx['invoice_path']); ?>" target="_blank" title="Visualizza allegato" class="p-2 hover:bg-gray-700 rounded-full transition-colors invoice-link">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                </a>
                                            <?php endif; ?>
                                            <button onclick='openEditTransactionModal(<?php echo json_encode($tx); ?>)' title="Modifica" class="p-2 hover:bg-gray-700 rounded-full transition-colors">
                                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536L16.732 3.732z"></path></svg>
                                            </button>
                                            <form action="delete_transaction.php" method="POST" class="delete-form">
                                                <input type="hidden" name="transaction_id" value="<?php echo $tx['id']; ?>">
                                                <button type="submit" title="Elimina" class="p-2 hover:bg-gray-700 rounded-full transition-colors">
                                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modale Aggiungi Movimento -->
    <div id="add-transaction-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50 opacity-0 modal-backdrop" onclick="closeModal('add-transaction-modal')"></div>
        <div class="bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg p-6 transform scale-95 opacity-0 modal-content">
            <h2 class="text-2xl font-bold text-white mb-2">Aggiungi Movimento</h2>
            <p class="text-gray-400 mb-6">Inserisci i dettagli del movimento.</p>
            <div class="mb-4">
                <div class="flex border-b border-gray-700">
                    <button type="button" id="tab-expense" class="tab-btn flex-1 py-2 font-semibold text-white border-b-2 border-primary-500">Uscita</button>
                    <button type="button" id="tab-income" class="tab-btn flex-1 py-2 font-semibold text-gray-400 border-b-2 border-transparent">Entrata</button>
                    <button type="button" id="tab-transfer" class="tab-btn flex-1 py-2 font-semibold text-gray-400 border-b-2 border-transparent">Trasferimento</button>
                </div>
            </div>
            <form id="form-expense-income" action="add_transaction.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="type" id="transaction-type" value="expense">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Importo</label>
                        <input type="number" step="0.01" name="amount" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Data</label>
                        <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Descrizione</label>
                        <input type="text" name="description" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Conto</label>
                        <select name="account_id" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                            <?php foreach($userAccounts as $account): ?><option value="<?php echo $account['id']; ?>"><?php echo htmlspecialchars($account['name']); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Categoria</label>
                        <div class="flex gap-2">
                            <input type="hidden" name="category_id" id="add-category-id-hidden" required>
                            <input type="text" id="add-category-name-display" placeholder="Seleziona una categoria" readonly required class="flex-grow bg-gray-700 text-white rounded-lg px-3 py-2 cursor-pointer">
                            <button type="button" id="choose-category-btn-add" class="bg-gray-600 hover:bg-gray-500 text-white font-semibold py-2 px-4 rounded-lg">Scegli</button>
                        </div>
                    </div>
                    <?php if ($is_pro_user): ?>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Etichette</label>
                        <div class="flex gap-2">
                            <input type="text" name="tags" id="tags-input-add" placeholder="Es. vacanze, lavoro, regali" class="flex-grow bg-gray-700 text-white rounded-lg px-3 py-2">
                            <button type="button" id="choose-tags-btn-add" class="bg-gray-600 hover:bg-gray-500 text-white font-semibold py-2 px-4 rounded-lg">Scegli</button>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Allega Fattura (Max 2MB)</label>
                        <input type="file" name="invoice_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-600 file:text-white hover:file:bg-primary-700">
                    </div>
                    <?php endif; ?>
                </div>
                <div class="mt-8 flex justify-end space-x-4">
                    <button type="button" onclick="closeModal('add-transaction-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Annulla</button>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-5 rounded-lg">Salva</button>
                </div>
            </form>
            <form id="form-transfer" action="add_transfer.php" method="POST" class="hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Importo</label>
                        <input type="number" step="0.01" name="amount" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Data</label>
                        <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Descrizione</label>
                        <input type="text" name="description" placeholder="Es. Spostamento su conto risparmi" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Da Conto</label>
                        <select name="from_account_id" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                            <?php foreach($userAccounts as $account): ?><option value="<?php echo $account['id']; ?>"><?php echo htmlspecialchars($account['name']); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">A Conto</label>
                        <select name="to_account_id" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                            <?php foreach($userAccounts as $account): ?><option value="<?php echo $account['id']; ?>"><?php echo htmlspecialchars($account['name']); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-8 flex justify-end space-x-4">
                    <button type="button" onclick="closeModal('add-transaction-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Annulla</button>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-5 rounded-lg">Salva Trasferimento</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale Modifica Movimento -->
    <div id="edit-transaction-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50 opacity-0 modal-backdrop" onclick="closeModal('edit-transaction-modal')"></div>
        <div class="bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg p-6 transform scale-95 opacity-0 modal-content">
            <h2 class="text-2xl font-bold text-white mb-6">Modifica Movimento</h2>
            <form id="edit-transaction-form" action="update_transaction.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="transaction_id" id="edit-transaction-id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Importo</label>
                        <input type="number" step="0.01" name="amount" id="edit-amount" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Data</label>
                        <input type="date" name="transaction_date" id="edit-date" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Descrizione</label>
                        <input type="text" name="description" id="edit-description" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Conto</label>
                        <select name="account_id" id="edit-account" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                            <?php foreach($userAccounts as $account): ?><option value="<?php echo $account['id']; ?>"><?php echo htmlspecialchars($account['name']); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Categoria</label>
                        <div class="flex gap-2">
                            <input type="hidden" name="category_id" id="edit-category-id-hidden" required>
                            <input type="text" id="edit-category-name-display" placeholder="Seleziona una categoria" readonly required class="flex-grow bg-gray-700 text-white rounded-lg px-3 py-2 cursor-pointer">
                            <button type="button" id="choose-category-btn-edit" class="bg-gray-600 hover:bg-gray-500 text-white font-semibold py-2 px-4 rounded-lg">Scegli</button>
                        </div>
                    </div>
                    <?php if ($is_pro_user): ?>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Etichette</label>
                        <div class="flex gap-2">
                            <input type="text" name="tags" id="edit-tags" class="flex-grow bg-gray-700 text-white rounded-lg px-3 py-2">
                            <button type="button" id="choose-tags-btn-edit" class="bg-gray-600 hover:bg-gray-500 text-white font-semibold py-2 px-4 rounded-lg">Scegli</button>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Ricevuta</label>
                        <div id="invoice-management-area" class="space-y-2">
                            <div id="current-invoice-container" class="hidden items-center justify-between bg-gray-700 p-2 rounded-lg">
                                <a id="current-invoice-link" href="#" target="_blank" class="text-sm text-indigo-400 hover:underline truncate">Visualizza ricevuta corrente</a>
                                <div class="flex items-center">
                                    <input type="checkbox" name="delete_invoice" id="delete_invoice" class="h-4 w-4 rounded bg-gray-900 border-gray-600 text-primary-600 focus:ring-primary-500">
                                    <label for="delete_invoice" class="ml-2 text-sm text-gray-400">Elimina</label>
                                </div>
                            </div>
                            <input type="file" name="invoice_file" id="edit-invoice-file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-600 file:text-white hover:file:bg-primary-700">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="mt-8 flex justify-end space-x-4">
                    <button type="button" onclick="closeModal('edit-transaction-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Annulla</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg">Salva Modifiche</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale di Conferma Eliminazione -->
    <div id="confirm-delete-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-60 opacity-0 modal-backdrop" onclick="closeModal('confirm-delete-modal')"></div>
        <div class="bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 transform scale-95 opacity-0 modal-content text-center">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-900">
                <svg class="h-6 w-6 text-red-400" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg leading-6 font-bold text-white mt-4">Eliminare Transazione?</h3>
            <p class="mt-2 text-sm text-gray-400">Se la transazione fa parte di un trasferimento, verranno eliminate entrambe le voci. L'azione è irreversibile.</p>

            <div class="mt-4 text-left">
                <label for="restore_balance_checkbox" class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" id="restore_balance_checkbox" name="restore_balance" class="h-5 w-5 rounded bg-gray-700 border-gray-600 text-primary-600 focus:ring-primary-500 focus:ring-offset-gray-800" checked>
                    <span class="text-sm font-medium text-gray-300">Ripristina l'importo sul saldo del conto</span>
                </label>
            </div>

            <div class="mt-8 flex justify-center space-x-4">
                <button id="confirm-delete-btn" type="button" class="bg-danger hover:bg-red-700 text-white font-semibold py-2 px-5 rounded-lg">Elimina</button>
                <button type="button" onclick="closeModal('confirm-delete-modal')" class="bg-gray-700 hover:bg-gray-600 text-gray-300 font-semibold py-2 px-5 rounded-lg">Annulla</button>
            </div>
        </div>
    </div>

    <!-- Modale per Note Transazione -->
    <div id="note-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop" onclick="closeModal('note-modal')"></div>
        <div class="bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg p-6 transform scale-95 opacity-0 modal-content">
            <h2 class="text-2xl font-bold text-white mb-4">Nota della Transazione</h2>
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

        document.addEventListener('DOMContentLoaded', function() {
            // --- GESTIONE GENERALE DELLE MODALI E FORM ---
            const addTransactionForm = document.getElementById('form-expense-income');
            const addTransferForm = document.getElementById('form-transfer');
            const editTransactionForm = document.getElementById('edit-transaction-form');
            const tableBody = document.getElementById('transactions-table-body');
            const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
            let formToDelete = null;

            function handleFormSubmit(e) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                fetch(form.action, { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message || 'Operazione completata.');
                            if (form.id === 'edit-transaction-form') {
                                updateTransactionInUI(data.transaction);
                                closeModal('edit-transaction-modal');
                            } else {
                                setTimeout(() => window.location.reload(), 1000);
                            }
                        } else {
                            showToast(data.message, 'error');
                        }
                    }).catch(err => showToast('Errore di rete.', 'error'));
            }

            addTransactionForm.addEventListener('submit', handleFormSubmit);
            addTransferForm.addEventListener('submit', handleFormSubmit);
            editTransactionForm.addEventListener('submit', handleFormSubmit);

            tableBody.addEventListener('submit', function(e) {
                const form = e.target.closest('.delete-form');
                if (form) {
                    e.preventDefault();
                    formToDelete = form;
                    openModal('confirm-delete-modal');
                }
            });
            
            confirmDeleteBtn.addEventListener('click', function() {
                if (formToDelete) {
                    const formData = new FormData(formToDelete);
                     if (document.getElementById('restore_balance_checkbox').checked) {
                        formData.append('restore_balance', 'yes');
                     } else {
                        formData.append('restore_balance', 'no');
                     }
                    const row = formToDelete.closest('tr');
                    fetch(formToDelete.action, { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                showToast(data.message);
                                row.classList.add('row-fade-out');
                                setTimeout(() => {
                                    row.remove();
                                    if (tableBody.getElementsByTagName('tr').length === 0) {
                                        tableBody.innerHTML = `<tr id="empty-state-transactions"><td colspan="6" class="text-center p-10">Nessuna transazione trovata.</td></tr>`;
                                    }
                                }, 500);
                            } else {
                                showToast(data.message, 'error');
                            }
                        })
                        .catch(err => showToast('Errore di rete.', 'error'))
                        .finally(() => {
                            closeModal('confirm-delete-modal');
                            formToDelete = null;
                        });
                }
            });

            // --- GESTIONE TAB MODALE AGGIUNGI ---
            const tabExpense = document.getElementById('tab-expense');
            const tabIncome = document.getElementById('tab-income');
            const tabTransfer = document.getElementById('tab-transfer');

            function switchTab(activeTab) {
                [tabExpense, tabIncome, tabTransfer].forEach(tab => {
                    tab.classList.remove('text-white', 'border-primary-500');
                    tab.classList.add('text-gray-400', 'border-transparent');
                });
                activeTab.classList.add('text-white', 'border-primary-500');
                activeTab.classList.remove('text-gray-400', 'border-transparent');

                if (activeTab === tabTransfer) {
                    addTransactionForm.classList.add('hidden');
                    addTransferForm.classList.remove('hidden');
                } else {
                    addTransactionForm.classList.remove('hidden');
                    addTransferForm.classList.add('hidden');
                    document.getElementById('transaction-type').value = activeTab === tabIncome ? 'income' : 'expense';
                }
            }
            tabExpense.addEventListener('click', () => switchTab(tabExpense));
            tabIncome.addEventListener('click', () => switchTab(tabIncome));
            tabTransfer.addEventListener('click', () => switchTab(tabTransfer));
            switchTab(tabExpense);

            // --- SETUP SELETTORE CATEGORIE ---
            function setupCategorySelector(prefix) {
                const chooseBtn = document.getElementById(`choose-category-btn-${prefix}`);
                const hiddenInput = document.getElementById(`${prefix}-category-id-hidden`);
                const displayInput = document.getElementById(`${prefix}-category-name-display`);
                const modalId = `categories-list-modal-${prefix}`;
                const searchInput = document.getElementById(`category-search-input-${prefix}`);
                const listContainer = document.getElementById(`categories-list-container-${prefix}`);
                let allCategories = [];

                if (!chooseBtn || !displayInput) return;

                const openCategoryModal = () => {
                    openModal(modalId);
                    if (allCategories.length === 0) {
                        fetch('ajax_get_categories.php')
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    allCategories = data.categories;
                                    displayCategories(allCategories);
                                }
                            });
                    } else {
                        displayCategories(allCategories);
                    }
                };
                
                chooseBtn.addEventListener('click', openCategoryModal);
                displayInput.addEventListener('click', openCategoryModal);

                function displayCategories(categories) {
                    listContainer.innerHTML = '';
                    const incomeCats = categories.filter(c => c.type === 'income');
                    const expenseCats = categories.filter(c => c.type === 'expense');

                    if (expenseCats.length > 0) {
                        const groupTitle = document.createElement('h4');
                        groupTitle.className = 'font-bold text-gray-300 text-sm py-1';
                        groupTitle.textContent = 'Spese';
                        listContainer.appendChild(groupTitle);
                        expenseCats.forEach(cat => listContainer.appendChild(createCategoryItem(cat)));
                    }
                    if (incomeCats.length > 0) {
                        const groupTitle = document.createElement('h4');
                        groupTitle.className = 'font-bold text-gray-300 text-sm py-1 mt-3';
                        groupTitle.textContent = 'Entrate';
                        listContainer.appendChild(groupTitle);
                        incomeCats.forEach(cat => listContainer.appendChild(createCategoryItem(cat)));
                    }
                }

                function createCategoryItem(category) {
                    const item = document.createElement('div');
                    item.className = 'p-2 rounded-lg hover:bg-gray-700 cursor-pointer';
                    item.textContent = category.name;
                    item.addEventListener('click', () => {
                        hiddenInput.value = category.id;
                        displayInput.value = category.name;
                        if (prefix === 'add') {
                            const tabToClick = document.getElementById(category.type === 'income' ? 'tab-income' : 'tab-expense');
                            if(tabToClick) tabToClick.click();
                        }
                        closeModal(modalId);
                    });
                    return item;
                }
                searchInput.addEventListener('input', () => {
                    const searchTerm = searchInput.value.toLowerCase();
                    const filtered = allCategories.filter(cat => cat.name.toLowerCase().includes(searchTerm));
                    displayCategories(filtered);
                });
            }
            setupCategorySelector('add');
            setupCategorySelector('edit');
            
            // --- SETUP SELETTORE ETICHETTE (TAGS) ---
            function setupTagSelector(prefix) {
                const chooseBtn = document.getElementById(`choose-tags-btn-${prefix}`);
                const tagsInput = document.getElementById(prefix === 'add' ? 'tags-input-add' : 'edit-tags');
                const modalId = `tags-list-modal-${prefix}`;
                const searchInput = document.getElementById(`tag-search-input-${prefix}`);
                const listContainer = document.getElementById(`tags-list-container-${prefix}`);
                let allTags = [];

                if (!chooseBtn) return;

                chooseBtn.addEventListener('click', () => {
                    openModal(modalId);
                    if (allTags.length === 0) {
                        fetch('ajax_get_tags.php')
                            .then(res => res.json())
                            .then(data => {
                                if(data.success) {
                                    allTags = data.tags;
                                    displayTags(allTags);
                                }
                            });
                    } else {
                        displayTags(allTags);
                    }
                });

                function displayTags(tags) {
                    listContainer.innerHTML = '';
                    tags.forEach(tag => {
                        const item = document.createElement('div');
                        item.className = 'p-2 rounded-lg hover:bg-gray-700 cursor-pointer';
                        item.textContent = tag.name;
                        item.addEventListener('click', () => {
                            const currentTags = tagsInput.value.split(',').map(t => t.trim()).filter(t => t);
                            if (!currentTags.includes(tag.name)) {
                                currentTags.push(tag.name);
                                tagsInput.value = currentTags.join(', ') + ' ';
                            }
                            closeModal(modalId);
                        });
                        listContainer.appendChild(item);
                    });
                }
                searchInput.addEventListener('input', () => {
                    const searchTerm = searchInput.value.toLowerCase();
                    const filtered = allTags.filter(tag => tag.name.toLowerCase().includes(searchTerm));
                    displayTags(filtered);
                });
            }
            setupTagSelector('add');
            setupTagSelector('edit');

            // --- GESTIONE FILTRI ---
            const toggleFilterBtn = document.getElementById('toggle-filter-btn');
            const filterContainer = document.getElementById('filter-container');
            if (toggleFilterBtn && filterContainer) {
                const urlParams = new URLSearchParams(window.location.search);
                const filtersAreActive = Array.from(urlParams.keys()).some(key => key !== 'page');
                if (filtersAreActive) {
                    filterContainer.classList.remove('hidden');
                }
                toggleFilterBtn.addEventListener('click', () => filterContainer.classList.toggle('hidden'));
            }
        });

        function openEditTransactionModal(tx) {
            document.getElementById('edit-transaction-id').value = tx.id;
            document.getElementById('edit-description').value = tx.description;
            document.getElementById('edit-amount').value = Math.abs(tx.amount);
            document.getElementById('edit-date').value = tx.transaction_date;
            document.getElementById('edit-account').value = tx.account_id;
            document.getElementById('edit-category-id-hidden').value = tx.category_id;
            document.getElementById('edit-category-name-display').value = tx.category_name || '';

            const tagsInput = document.getElementById('edit-tags');
            if(tagsInput) tagsInput.value = tx.tags || '';

            const invoiceContainer = document.getElementById('current-invoice-container');
            const invoiceLink = document.getElementById('current-invoice-link');
            const deleteCheckbox = document.getElementById('delete_invoice');
            const fileInput = document.getElementById('edit-invoice-file');

            if (tx.invoice_path) {
                invoiceLink.href = tx.invoice_path;
                invoiceContainer.classList.remove('hidden');
                invoiceContainer.classList.add('flex');
            } else {
                invoiceContainer.classList.add('hidden');
                invoiceContainer.classList.remove('flex');
            }

            if(deleteCheckbox) deleteCheckbox.checked = false;
            if(fileInput) fileInput.value = '';

            openModal('edit-transaction-modal');
        }
        
        function updateTransactionInUI(tx) {
            const row = document.querySelector(`tr[data-transaction-id="${tx.id}"]`);
            if (row) {
                const formattedAmount = new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(tx.amount);
                const formattedDate = new Date(tx.transaction_date + 'T00:00:00').toLocaleDateString('it-IT');
                const amountCell = row.querySelector('.amount-cell');

                row.querySelector('.date-cell').textContent = formattedDate;
                row.querySelector('.description-cell').firstChild.textContent = escapeHTML(tx.description) + ' ';
                row.querySelector('.category-cell').textContent = escapeHTML(tx.category_name);
                row.querySelector('.account-cell').textContent = escapeHTML(tx.account_name);

                const tagsCell = row.querySelector('.tags-cell');
                if(tagsCell) {
                    tagsCell.textContent = escapeHTML(tx.tags || '');
                } else if (tx.tags) {
                    const descriptionCell = row.querySelector('.description-cell');
                    const newTagsDiv = document.createElement('div');
                    newTagsDiv.className = 'text-xs text-indigo-400 mt-1 font-normal tags-cell';
                    newTagsDiv.textContent = escapeHTML(tx.tags);
                    descriptionCell.appendChild(newTagsDiv);
                }

                amountCell.textContent = (tx.amount > 0 ? '+' : '') + formattedAmount;
                amountCell.className = `p-4 text-right font-bold amount-cell ${tx.amount > 0 ? 'text-green-400' : 'text-red-400'}`;
                
                const actionCell = row.querySelector('.action-buttons');
                let invoiceButtonHTML = '';
                if (tx.invoice_path) {
                    invoiceButtonHTML = `
                        <a href="${escapeHTML(tx.invoice_path)}" target="_blank" title="Visualizza allegato" class="p-2 hover:bg-gray-700 rounded-full transition-colors invoice-link">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        </a>`;
                }

                actionCell.innerHTML = `
                    <button onclick="openNoteModal(${tx.id})" title="Aggiungi/Modifica Nota" class="p-2 hover:bg-gray-700 rounded-full transition-colors">
                        <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </button>
                ` + invoiceButtonHTML + `
                    <button onclick='openEditTransactionModal(${JSON.stringify(tx)})' title="Modifica" class="p-2 hover:bg-gray-700 rounded-full transition-colors">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536L16.732 3.732z"></path></svg>
                    </button>
                    <form action="delete_transaction.php" method="POST" class="delete-form">
                        <input type="hidden" name="transaction_id" value="${tx.id}">
                        <button type="submit" title="Elimina" class="p-2 hover:bg-gray-700 rounded-full transition-colors">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                `;
            }
        }

        function openNoteModal(transactionId) {
            document.getElementById('note-transaction-id').value = transactionId;
            const noteContentTextarea = document.getElementById('note-content');
            noteContentTextarea.value = 'Caricamento...';

            fetch(`ajax_note_handler.php?action=get_note&transaction_id=${transactionId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        noteContentTextarea.value = data.content;
                    } else {
                        noteContentTextarea.value = '';
                        showToast(data.message || 'Impossibile caricare la nota.', 'error');
                    }
                })
                .catch(() => {
                    noteContentTextarea.value = '';
                    showToast('Errore di rete nel caricare la nota.', 'error');
                });

            openModal('note-modal');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const noteForm = document.getElementById('note-form');
            if (noteForm) {
                noteForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const transactionId = document.getElementById('note-transaction-id').value;
                    const noteContent = document.getElementById('note-content').value;
                    const formData = new FormData();
                    formData.append('action', 'save_note');
                    formData.append('transaction_id', transactionId);
                    formData.append('note_content', noteContent);

                    fetch('ajax_note_handler.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                showToast(data.message || 'Nota salvata.');
                                closeModal('note-modal');
                            } else {
                                showToast(data.message || 'Errore.', 'error');
                            }
                        })
                        .catch(() => showToast('Errore di rete.', 'error'));
                });
            }
        });
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

    <div id="import-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-60 opacity-0 modal-backdrop" onclick="closeModal('import-modal')"></div>
        <div class="bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg p-6 transform scale-95 opacity-0 modal-content">
            <h2 class="text-2xl font-bold text-white mb-4">Importa Transazioni da File</h2>
            <p class="text-gray-400 mb-6">Carica un file CSV dal tuo servizio di home banking per importare movimenti in massa.</p>
            <form action="import_transactions.php" method="POST" enctype="multipart/form-data">
                <div class="space-y-4">
                    <div>
                        <label for="import-account-id" class="block text-sm font-medium text-gray-300 mb-1">Conto di Destinazione</label>
                        <select name="account_id" id="import-account-id" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                            <?php foreach($userAccounts as $account): ?>
                                <option value="<?php echo $account['id']; ?>"><?php echo htmlspecialchars($account['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="mt-2">
                            <label for="override-account" class="flex items-center text-sm text-gray-400">
                                <input type="checkbox" name="override_account" id="override-account" class="h-4 w-4 rounded bg-gray-900 border-gray-600 text-primary-600 focus:ring-primary-500">
                                <span class="ml-2">Ignora la colonna 'Conto' del file e importa tutto in questo conto.</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label for="bank-format" class="block text-sm font-medium text-gray-300 mb-1">Formato della Banca</label>
                        <select name="bank_format" id="bank-format" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                            <option value="default">Standard (Data,Descrizione,Importo,Categoria)</option>
                            <!-- Altri formati verranno aggiunti qui -->
                        </select>
                    </div>
                    <div>
                        <label for="csv-file" class="block text-sm font-medium text-gray-300 mb-1">File CSV</label>
                        <input type="file" name="csv_file" id="csv-file" required accept=".csv" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-600 file:text-white hover:file:bg-primary-700">
                    </div>
                </div>
                <div class="mt-8 flex justify-end space-x-4">
                    <button type="button" onclick="closeModal('import-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Annulla</button>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-5 rounded-lg">Importa</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale Scelta Categoria (per Aggiungi) -->
    <?php $prefix = 'add'; include 'categories_list_modal.php'; ?>

    <!-- Modale Scelta Categoria (per Modifica) -->
    <?php $prefix = 'edit'; include 'categories_list_modal.php'; ?>

    <!-- Modale Scelta Tag (per Aggiungi) -->
    <?php $prefix = 'add'; include 'tags_list_modal.php'; ?>

    <!-- Modale Scelta Tag (per Modifica) -->
    <?php $prefix = 'edit'; include 'tags_list_modal.php'; ?>
</body>
</html>