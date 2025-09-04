<?php
// Questo file mostra il modulo per inserire la nuova password.
require_once 'db_connect.php';

$token = $_GET['token'] ?? '';
$error = '';
$token_valid = false;

if (empty($token)) {
    $error = "Token non fornito o non valido.";
} else {
    // Verifica che il token esista e non sia scaduto
    $sql = "SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
            $token_valid = true;
        } else {
            $error = "Token non valido o scaduto. Richiedine uno nuovo.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resetta Password - Bearget</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-2xl shadow-lg">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">Crea una nuova password</h2>
        </div>

        <?php if (!empty($error)): ?>
            <div class="p-4 text-sm rounded-lg bg-red-100 text-red-800">
                <?php echo htmlspecialchars($error); ?>
                <p class="mt-2"><a href="forgot_password.php" class="font-semibold underline">Torna alla pagina di recupero</a></p>
            </div>
        <?php elseif (isset($_GET['message'])): ?>
            <div class="p-4 text-sm rounded-lg bg-red-100 text-red-800">
                <?php echo htmlspecialchars(urldecode($_GET['message'])); ?>
            </div>
        <?php endif; ?>

        <?php if ($token_valid): ?>
            <form class="mt-8 space-y-6" action="update_password_from_reset.php" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="space-y-4">
                    <div>
                        <label for="new_password" class="sr-only">Nuova Password</label>
                        <input id="new_password" name="new_password" type="password" required class="relative block w-full appearance-none rounded-md border border-gray-300 px-3 py-3" placeholder="Nuova Password">
                        <p id="requirement-text" class="mt-2 text-xs text-gray-500 transition-colors">
                            La password deve contenere almeno 8 caratteri. 
                            <span id="char-count" class="font-medium">0/8</span>
                        </p>
                    </div>
                    <div>
                        <label for="confirm_password" class="sr-only">Conferma Nuova Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" required class="relative block w-full appearance-none rounded-md border border-gray-300 px-3 py-3" placeholder="Conferma Nuova Password">
                    </div>
                </div>
                <div>
                    <button type="submit" class="group relative flex w-full justify-center rounded-md border border-transparent bg-indigo-600 py-3 px-4 text-sm font-semibold text-white hover:bg-indigo-700">
                        Salva nuova password
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        const passwordInput = document.getElementById('new_password');
        const charCountSpan = document.getElementById('char-count');
        const requirementText = document.getElementById('requirement-text');

        if (passwordInput && charCountSpan && requirementText) {
            passwordInput.addEventListener('input', () => {
                const count = passwordInput.value.length;
                charCountSpan.textContent = `${count}/8`;
                if (count >= 8) {
                    requirementText.classList.remove('text-gray-500');
                    requirementText.classList.add('text-green-600');
                } else {
                    requirementText.classList.remove('text-green-600');
                    requirementText.classList.add('text-gray-500');
                }
            });
        }
    </script>
</body>
</html>