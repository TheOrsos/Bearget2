<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["id"] != 1) {
    header("location: dashboard.php");
    exit;
}
require_once 'db_connect.php';
require_once 'functions.php';

// Funzione per recuperare tutti gli aggiornamenti del changelog
function get_all_changelog_updates($conn) {
    $updates = [];
    $sql = "SELECT * FROM changelog_updates ORDER BY created_at DESC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $updates[] = $row;
        }
    }
    return $updates;
}

$changelog_updates = get_all_changelog_updates($conn);
$current_page = 'admin';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Changelog - Bearget</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="theme.php">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 500: 'var(--color-primary-500)', 600: 'var(--color-primary-600)', 700: 'var(--color-primary-700)' },
                        gray: { 100: 'var(--color-gray-100)', 200: 'var(--color-gray-200)', 300: 'var(--color-gray-300)', 400: 'var(--color-gray-400)', 700: 'var(--color-gray-700)', 800: 'var(--color-gray-800)', 900: 'var(--color-gray-900)' },
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
        .tox-tinymce { border-radius: 0.5rem; }
    </style>
</head>
<body class="text-gray-300">
    <div class="flex h-screen">
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-10 overflow-y-auto">
            <header class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-white">Gestione Changelog</h1>
                <button onclick="openChangelogModal()" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-4 rounded-lg">Aggiungi Aggiornamento</button>
            </header>

            <div class="bg-gray-800 rounded-2xl p-2">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-sm text-gray-400 uppercase">
                            <tr>
                                <th class="p-4">Versione</th>
                                <th class="p-4">Titolo</th>
                                <th class="p-4">Stato</th>
                                <th class="p-4">Data Creazione</th>
                                <th class="p-4 text-center">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="text-white">
                            <?php if (empty($changelog_updates)): ?>
                                <tr><td colspan="5" class="text-center p-6 text-gray-400">Nessun aggiornamento trovato.</td></tr>
                            <?php else: ?>
                                <?php foreach ($changelog_updates as $update): ?>
                                <tr class="border-b border-gray-700 last:border-b-0">
                                    <td class="p-4 font-mono"><?php echo htmlspecialchars($update['version']); ?></td>
                                    <td class="p-4 font-semibold"><?php echo htmlspecialchars($update['title']); ?></td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $update['is_published'] ? 'bg-green-700 text-green-100' : 'bg-yellow-700 text-yellow-100'; ?>">
                                            <?php echo $update['is_published'] ? 'Pubblicato' : 'Bozza'; ?>
                                        </span>
                                    </td>
                                    <td class="p-4"><?php echo date("d/m/Y H:i", strtotime($update['created_at'])); ?></td>
                                    <td class="p-4">
                                        <div class="flex justify-center items-center space-x-2">
                                            <button onclick='openChangelogModal(<?php echo json_encode($update); ?>)' class="p-2 hover:bg-gray-700 rounded-full" title="Modifica"><svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536L16.732 3.732z"></path></svg></button>
                                            <button onclick='deleteChangelog(<?php echo $update['id']; ?>)' class="p-2 hover:bg-gray-700 rounded-full" title="Elimina"><svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
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

    <!-- Modale Aggiungi/Modifica Changelog -->
    <div id="changelog-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-60 opacity-0 modal-backdrop" onclick="closeModal('changelog-modal')"></div>
        <div class="bg-gray-800 rounded-2xl shadow-xl w-full max-w-3xl p-6 transform scale-95 opacity-0 modal-content">
            <h2 id="modal-title" class="text-2xl font-bold text-white mb-6">Aggiungi Aggiornamento</h2>
            <form id="changelog-form">
                <input type="hidden" name="id" id="changelog-id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="version" class="block text-sm font-medium text-gray-300 mb-1">Versione (es. v1.2.3)</label>
                        <input type="text" name="version" id="version" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label for="image_url" class="block text-sm font-medium text-gray-300 mb-1">URL Immagine (Opzionale)</label>
                        <input type="url" name="image_url" id="image_url" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                    </div>
                </div>
                <div class="mt-4">
                    <label for="title" class="block text-sm font-medium text-gray-300 mb-1">Titolo</label>
                    <input type="text" name="title" id="title" required class="w-full bg-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div class="mt-4">
                    <label for="description" class="block text-sm font-medium text-gray-300 mb-1">Breve Descrizione</label>
                    <textarea name="description" id="description" rows="2" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2"></textarea>
                </div>
                <div class="mt-4">
                    <label for="content" class="block text-sm font-medium text-gray-300 mb-1">Contenuto Principale</label>
                    <textarea name="content" id="content" class="w-full bg-gray-700 text-white rounded-lg px-3 py-2"></textarea>
                </div>
                <div class="mt-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_published" id="is_published" class="h-4 w-4 rounded bg-gray-900 border-gray-600 text-primary-600 focus:ring-primary-500">
                        <span class="ml-2 text-sm text-gray-300">Pubblica questo aggiornamento</span>
                    </label>
                </div>
                <div class="mt-8 flex justify-end space-x-4">
                    <button type="button" onclick="closeModal('changelog-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg">Annulla</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg">Salva</button>
                </div>
            </form>
        </div>
    </div>

<script>
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
    
    function openChangelogModal(update = null) {
        const form = document.getElementById('changelog-form');
        form.reset();
        document.getElementById('changelog-id').value = '';
        document.getElementById('modal-title').textContent = 'Aggiungi Aggiornamento';
        
        tinymce.get('content').setContent('');

        if (update) {
            document.getElementById('modal-title').textContent = 'Modifica Aggiornamento';
            document.getElementById('changelog-id').value = update.id;
            document.getElementById('version').value = update.version;
            document.getElementById('title').value = update.title;
            document.getElementById('description').value = update.description;
            document.getElementById('image_url').value = update.image_url;
            tinymce.get('content').setContent(update.content);
            document.getElementById('is_published').checked = !!parseInt(update.is_published);
        }
        
        openModal('changelog-modal');
    }

    document.addEventListener('DOMContentLoaded', function() {
        tinymce.init({
            selector: 'textarea#content',
            plugins: 'link image code lists', // Rimosso 'textcolor'
            toolbar: 'undo redo | blocks | bold italic underline | forecolor backcolor | bullist numlist | link image | code',
            skin: 'oxide-dark',
            content_css: 'dark',
            height: 300
        });

        const form = document.getElementById('changelog-form');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Aggiorna il contenuto della textarea prima di inviare
            tinymce.triggerSave();
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // TODO: Creare e chiamare admin_update_changelog.php
            console.log('Dati da salvare:', data);
            
            // Esempio di chiamata fetch (da implementare)
            /*
            fetch('admin_update_changelog.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                if(result.success) {
                    closeModal('changelog-modal');
                    // Ricarica la pagina per mostrare le modifiche
                    window.location.reload();
                } else {
                    alert('Errore: ' + result.message);
                }
            });
            */
        });
    });

    function deleteChangelog(id) {
        if (confirm('Sei sicuro di voler eliminare questo aggiornamento?')) {
            // TODO: Creare e chiamare script per eliminare
            console.log('Elimina ID:', id);
            /*
            fetch('admin_delete_changelog.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(res => res.json())
            .then(result => {
                if(result.success) {
                    window.location.reload();
                } else {
                    alert('Errore: ' + result.message);
                }
            });
            */
        }
    }
</script>
</body>
</html>
