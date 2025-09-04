<?php
// modals/confirm_action_modal.php
?>
<div id="confirm-action-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <div class="fixed inset-0 bg-black bg-opacity-60 opacity-0 modal-backdrop" onclick="closeModal('confirm-action-modal')"></div>
    <div class="bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 transform scale-95 opacity-0 modal-content text-center">
        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-900">
            <svg class="h-6 w-6 text-blue-400" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h3 id="confirm-modal-title" class="text-lg leading-6 font-bold text-white mt-4">Conferma Azione</h3>
        <p id="confirm-modal-message" class="mt-2 text-sm text-gray-400">Sei sicuro di voler procedere?</p>
        <div class="mt-8 flex justify-center space-x-4">
            <button id="confirm-modal-cancel-btn" type="button" class="bg-gray-700 hover:bg-gray-600 text-gray-300 font-semibold py-2 px-5 rounded-lg">Annulla</button>
            <button id="confirm-modal-confirm-btn" type="button" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-5 rounded-lg">Conferma</button>
        </div>
    </div>
</div>