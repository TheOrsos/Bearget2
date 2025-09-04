document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-users');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkActionButtons = document.querySelectorAll('.bulk-action-btn');

    // Modal elements
    const confirmModal = document.getElementById('bulk-action-confirm-modal');
    const modalMessage = document.getElementById('bulk-action-modal-message');
    const confirmButton = document.getElementById('bulk-action-confirm-button');

    if (!selectAllCheckbox || !confirmModal) {
        console.error("Elementi di base per le azioni di gruppo non trovati.");
        return;
    }

    // "Select All" functionality
    selectAllCheckbox.addEventListener('change', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Add listener to all bulk action buttons
    bulkActionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;

            const selectedUserIds = Array.from(userCheckboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);

            if (selectedUserIds.length === 0) {
                alert('Per favore, seleziona almeno un utente.');
                return;
            }

            // Prepare and open the confirmation modal
            const actionTextMap = {
                'suspend': 'sospendere',
                'reactivate': 'riattivare',
                'delete': 'eliminare definitivamente',
                'disable_emails': 'disattivare la ricezione di email per',
                'enable_emails': 'attivare la ricezione di email per'
            };
            const actionText = actionTextMap[action] || `eseguire l'azione '${action}' su`;

            modalMessage.textContent = `Sei sicuro di voler ${actionText} ${selectedUserIds.length} utente/i?`;

            // Store action and user IDs on the confirm button
            confirmButton.dataset.action = action;
            confirmButton.dataset.userIds = JSON.stringify(selectedUserIds);

            // Change modal button color based on action
            confirmButton.className = 'text-white font-semibold py-2 px-5 rounded-lg'; // Reset classes
            if (action === 'delete' || action === 'suspend') {
                confirmButton.classList.add('bg-red-600', 'hover:bg-red-700');
            } else if (action === 'reactivate' || action === 'enable_emails') {
                 confirmButton.classList.add('bg-green-600', 'hover:bg-green-700');
            } else {
                 confirmButton.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
            }

            openModal('bulk-action-confirm-modal');
        });
    });

    // Add listener for the final confirmation button inside the modal
    confirmButton.addEventListener('click', function() {
        const action = this.dataset.action;
        const userIds = JSON.parse(this.dataset.userIds);

        if (!action || !userIds || userIds.length === 0) {
            alert('Errore: Azione o utenti non specificati.');
            return;
        }

        fetch('admin_user_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: action,
                userIds: userIds
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Errore:', error);
            alert('Si è verificato un errore durante l\'esecuzione dell\'azione.');
        })
        .finally(() => {
            closeModal('bulk-action-confirm-modal');
        });
    });
});
