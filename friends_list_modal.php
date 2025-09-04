<?php
// modals/friends_list_modal.php
// Expects a $prefix variable to be defined before include, e.g., 'note' or 'fund'
?>
<div id="friends-list-modal-<?php echo $prefix; ?>" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <div class="fixed inset-0 bg-black bg-opacity-60 opacity-0 modal-backdrop" onclick="closeModal('friends-list-modal-<?php echo $prefix; ?>')"></div>
    <div class="bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 transform scale-95 opacity-0 modal-content">
        <h3 class="text-2xl font-bold text-white mb-4">Scegli un Amico</h3>
        <div class="mb-4">
            <input type="text" id="friend-search-input-<?php echo $prefix; ?>" placeholder="Cerca per nome utente..." class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 text-sm border border-gray-600 focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div id="friends-list-container-<?php echo $prefix; ?>" class="space-y-2 max-h-60 overflow-y-auto">
            <!-- Friend list will be inserted here by JS -->
        </div>
        <div class="flex justify-end mt-6">
            <button type="button" class="text-gray-400 hover:text-white" onclick="closeModal('friends-list-modal-<?php echo $prefix; ?>')">Chiudi</button>
        </div>
    </div>
</div>
