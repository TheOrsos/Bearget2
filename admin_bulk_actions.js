document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-users');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const applyButton = document.getElementById('apply-bulk-action');
    const actionSelect = document.getElementById('bulk-action');

    if (!selectAllCheckbox || !applyButton || !actionSelect) {
        console.error("Bulk action elements not found. Make sure the HTML is correct.");
        return;
    }

    // "Select All" functionality
    selectAllCheckbox.addEventListener('change', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // "Apply" button functionality
    applyButton.addEventListener('click', function() {
        const selectedAction = actionSelect.value;
        if (!selectedAction) {
            alert('Per favore, seleziona un\'azione da eseguire.');
            return;
        }

        const selectedUserIds = Array.from(userCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);

        if (selectedUserIds.length === 0) {
            alert('Per favore, seleziona almeno un utente.');
            return;
        }
        
        if (confirm(`Sei sicuro di voler eseguire l'azione "${selectedAction}" su ${selectedUserIds.length} utenti?`)) {
            fetch('admin_user_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: selectedAction,
                    userIds: selectedUserIds
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
                console.error('Error:', error);
                alert('Si è verificato un errore durante l\'esecuzione dell\'azione.');
            });
        }
    });
});
