<?php
// Questo file presuppone che una sessione sia già attiva
// e che la variabile $current_page sia stata definita prima di includerlo.

if (isset($_SESSION['id'])) {
    if (!function_exists('get_user_by_id')) {
        require_once 'functions.php';
    }
    if (file_exists('friends_functions.php')) {
        require_once 'friends_functions.php';
        $unread_chat_counts = get_unread_message_counts($conn, $_SESSION['id']);
        $unread_chats_count = count($unread_chat_counts);
    } else {
        $unread_chats_count = 0;
    }
    $user_sidebar_data = get_user_by_id($conn, $_SESSION['id']);
    $is_pro_user_sidebar = ($user_sidebar_data['subscription_status'] === 'active' || $user_sidebar_data['subscription_status'] === 'lifetime');

    // Funzione per ottenere l'ultima versione del changelog
    function get_latest_changelog_version($conn) {
        $sql = "SELECT version FROM changelog_updates WHERE is_published = 1 ORDER BY created_at DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc()['version'];
        }
        return null;
    }
    $latest_version = get_latest_changelog_version($conn);

} else {
    $is_pro_user_sidebar = false;
    $latest_version = null;
}
?>
<style>
/* Hide scrollbar for Chrome, Safari and Opera */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
/* Hide scrollbar for IE, Edge and Firefox */
.no-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>
<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-gray-800 p-4 flex flex-col transform -translate-x-full lg:relative lg:translate-x-0 transition-transform duration-300 ease-in-out z-40">
    <!-- Header (non-scrollable) -->
    <div class="flex-shrink-0">
        <div class="flex justify-between items-center mb-10">
            <div class="flex items-center">
                <?php if (isset($is_pro_user_sidebar) && $is_pro_user_sidebar): ?>
                    <img src="assets/images/logo_pro.png" alt="Bearget Pro Logo" class="w-10 h-10">
                <?php else: ?>
                    <img src="assets/images/white-logo_free.png" alt="Bearget Free Logo" class="w-10 h-10">
                <?php endif; ?>
                <span class="ml-3 text-2xl font-extrabold text-white">Bearget</span>
            </div>
            <?php if ($latest_version): ?>
                <a href="changelog.php" class="inline-block px-2 py-0.5 bg-gray-700 text-gray-300 text-xs font-semibold rounded-full hover:bg-gray-600 transition-colors">
                    <?php echo htmlspecialchars($latest_version); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Navigation (scrollable) -->
    <nav class="flex-grow overflow-y-auto space-y-2 no-scrollbar">
        <?php if (isset($_SESSION['id']) && $_SESSION['id'] == 1): ?>
            <a href="admin.php" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?php echo ($current_page == 'admin') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                Admin
            </a>
        <?php endif; ?>
        <a href="dashboard.php" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?php echo ($current_page == 'dashboard') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Dashboard
        </a>
        <a href="transactions.php" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?php echo ($current_page == 'transactions') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            Transazioni
        </a>
        <a href="accounts.php" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?php echo ($current_page == 'accounts') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            Conti
        </a>
        <a href="categories.php" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?php echo ($current_page == 'categories') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            Categorie
        </a>
        <a href="purchase_planner.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg transition-colors <?php echo ($current_page == 'purchase_planner') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                Pianificatore
            </div>
            <?php if (!$is_pro_user_sidebar): ?>
                <img src="assets/images/lucchetto-bianco.svg" alt="Lock" class="w-5 h-5">
            <?php endif; ?>
        </a>
        <a href="reports.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg transition-colors <?php echo ($current_page == 'reports') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Report
            </div>
            <?php if (!$is_pro_user_sidebar): ?>
                <img src="assets/images/lucchetto-bianco.svg" alt="Lock" class="w-5 h-5">
            <?php endif; ?>
        </a>
        <a href="budgets.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg transition-colors <?php echo ($current_page == 'budgets') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                Budget
            </div>
            <?php if (!$is_pro_user_sidebar): ?>
                <img src="assets/images/lucchetto-bianco.svg" alt="Lock" class="w-5 h-5">
            <?php endif; ?>
        </a>
        <a href="goals.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg transition-colors <?php echo ($current_page == 'goals') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.25278C12 6.25278 10.8333 5 9.5 5C8.16667 5 7 6.25278 7 6.25278V9.74722C7 9.74722 8.16667 11 9.5 11C10.8333 11 12 9.74722 12 9.74722V6.25278ZM12 6.25278C12 6.25278 13.1667 5 14.5 5C15.8333 5 17 6.25278 17 6.25278V9.74722C17 9.74722 15.8333 11 14.5 11C13.1667 11 12 9.74722 12 9.74722V6.25278Z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11V14"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14H15"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17H15"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20H14"></path></svg>
                Obiettivi
            </div>
            <?php if (!$is_pro_user_sidebar): ?>
                <img src="assets/images/lucchetto-bianco.svg" alt="Lock" class="w-5 h-5">
            <?php endif; ?>
        </a>
        <a href="recurring.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg transition-colors <?php echo ($current_page == 'recurring') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5m11 2a9 9 0 11-2.93-6.93"></path></svg>
                Ricorrenti
            </div>
            <?php if (!$is_pro_user_sidebar): ?>
                <img src="assets/images/lucchetto-bianco.svg" alt="Lock" class="w-5 h-5">
            <?php endif; ?>
        </a>
        <a href="shared_funds.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg transition-colors <?php echo ($current_page == 'shared_funds') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.122-1.28-.356-1.857M7 20v-2c0-.653.122-1.28.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Fondi Comuni
            </div>
            <?php if (!$is_pro_user_sidebar): ?>
                <img src="assets/images/lucchetto-bianco.svg" alt="Lock" class="w-5 h-5">
            <?php endif; ?>
        </a>
        <a href="friends.php" class="flex items-center px-4 py-2.5 rounded-lg transition-colors relative <?php echo ($current_page == 'friends') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" viewBox="0 0 20 20" fill="currentColor"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3.004 3.004 0 013.75-2.906z" /></svg>
            Amici
        </a>
        <a href="tags.php" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?php echo ($current_page == 'tags') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A2 2 0 013 8V3z"></path></svg>
            Etichette
        </a>
        <a href="notes.php" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?php echo ($current_page == 'notes') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Note
        </a>
        <a href="notifications.php" class="flex items-center px-4 py-2.5 rounded-lg transition-colors relative <?php echo ($current_page == 'notifications') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            Notifiche
            <span class="notification-badge-sidebar absolute top-1 right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full" style="display: none;"></span>
        </a>
        <a href="changelog.php" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?php echo ($current_page == 'changelog') ? 'text-white bg-gray-900 font-semibold' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
            Novità
        </a>
    </nav>

    <!-- Footer (non-scrollable) -->
    <div class="border-t border-gray-700 pt-4 flex-shrink-0">
        <a href="settings.php" class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-400 hover:bg-gray-700 hover:text-white">
            <?php
            $avatar_path = $user_sidebar_data['profile_picture_path'] ?? '';
            if (!empty($avatar_path) && file_exists($avatar_path)):
            ?>
                <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="User Avatar" class="w-10 h-10 rounded-full object-cover mr-3">
            <?php else: ?>
                <div class="w-10 h-10 rounded-full bg-gray-700 mr-3 flex items-center justify-center">
                    <span class="text-white font-bold"><?php echo strtoupper(substr($user_sidebar_data['username'], 0, 1)); ?></span>
                </div>
            <?php endif; ?>
            <div>
                <span class="font-semibold text-white"><?php echo htmlspecialchars($user_sidebar_data['username']); ?></span>
                <p class="text-xs text-gray-500">Visualizza profilo</p>
            </div>
        </a>
        <a href="bearget_info.html" target="_blank" class="flex items-center px-4 py-2.5 mt-2 text-gray-400 hover:bg-gray-700 hover:text-white rounded-lg transition-colors">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Info & Supporto
        </a>
        <a href="logout.php" class="flex items-center px-4 py-2.5 mt-2 text-red-400 hover:bg-red-500 hover:text-white rounded-lg transition-colors">Logout</a>
    </div>
</aside>

<?php
// NUOVO: Includi il componente per le notifiche toast
// Questo renderà il pop-up disponibile su tutte le pagine che usano la sidebar.
include 'toast_notification.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // This function will be responsible for updating all real-time counters
    function updateUnreadCounters() {
        fetch('api_get_unread_counts.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update sidebar counter for chats
                    const friendsLink = document.querySelector('a[href="friends.php"]');
                    if (friendsLink) {
                        let badge = friendsLink.querySelector('.notification-badge-chat');
                        if (data.total_chats > 0) {
                            if (!badge) {
                                badge = document.createElement('span');
                                badge.classList.add('notification-badge-chat', 'absolute', 'top-1', 'right-1', 'inline-flex', 'items-center', 'justify-center', 'px-2', 'py-1', 'text-xs', 'font-bold', 'leading-none', 'text-red-100', 'bg-red-600', 'rounded-full');
                                friendsLink.appendChild(badge);
                            }
                            badge.textContent = data.total_chats;
                        } else {
                            if (badge) {
                                badge.remove();
                            }
                        }
                    }

                    // Update sidebar counter for notifications
                    const notificationsLink = document.querySelector('a[href="notifications.php"]');
                    if (notificationsLink) {
                        let badge = notificationsLink.querySelector('.notification-badge-sidebar');
                        if (data.notification_count > 0) {
                            if (!badge) {
                                badge = document.createElement('span');
                                badge.classList.add('notification-badge-sidebar', 'absolute', 'top-1', 'right-1', 'inline-flex', 'items-center', 'justify-center', 'px-2', 'py-1', 'text-xs', 'font-bold', 'leading-none', 'text-red-100', 'bg-red-600', 'rounded-full');
                                notificationsLink.appendChild(badge);
                            }
                            badge.textContent = data.notification_count;
                            badge.style.display = 'inline-flex';
                        } else {
                            if (badge) {
                                badge.style.display = 'none';
                            }
                        }
                    }

                    // Update per-friend counters on friends.php page
                    const friendsTable = document.querySelector('#friends-table-body');
                    if (friendsTable) {
                        const chatButtons = friendsTable.querySelectorAll('.chat-button');
                        chatButtons.forEach(button => {
                            const friendId = button.getAttribute('data-friend-id');
                            let badge = button.querySelector('.notification-badge-friend');
                            const count = data.by_user[friendId] || 0;

                            if (count > 0) {
                                if (!badge) {
                                    badge = document.createElement('span');
                                    badge.classList.add('notification-badge-friend', 'absolute', '-top-2', '-right-2', 'inline-flex', 'items-center', 'justify-center', 'px-2', 'py-1', 'text-xs', 'font-bold', 'leading-none', 'text-red-100', 'bg-red-600', 'rounded-full');
                                    button.appendChild(badge);
                                }
                                badge.textContent = count;
                            } else {
                                if (badge) {
                                    badge.remove();
                                }
                            }
                        });
                    }
                }
            })
            .catch(error => console.error('Error fetching unread counts:', error));
    }

    // Poll every 15 seconds
    setInterval(updateUnreadCounters, 15000);
    // Initial call to populate counters on page load
    updateUnreadCounters();
});
</script>