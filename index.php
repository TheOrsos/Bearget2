<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bearget - Login & Registrazione</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hidden { display: none; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-2xl shadow-lg">

        <div class="text-center">
            <img class="mx-auto h-12 w-auto text-indigo-600" style="width:100px; height: 100px" src="assets/images/logo_free.png" alt="Bearget Free Logo" class="w-10 h-10">
            <h2 id="form-title" class="mt-6 text-3xl font-bold tracking-tight text-gray-900">
                Accedi al tuo account
            </h2>
            <p id="form-subtitle" class="mt-2 text-sm text-gray-600">
                Oppure <button type="button" id="switch-to-register" class="font-medium text-indigo-600 hover:text-indigo-500">crea un nuovo account</button>
            </p>
        </div>

        <div id="message-container" class="hidden p-4 text-sm rounded-lg"></div>
        <div id="verification-link-container" class="hidden text-center"></div>

        <!-- Modulo di Login -->
        <form id="login-form" class="mt-8 space-y-6" action="login.php" method="POST">
            <div class="space-y-4 rounded-md shadow-sm">
                <div>
                    <label for="login-email-address" class="sr-only">Indirizzo email</label>
                    <input id="login-email-address" name="email" type="email" autocomplete="email" required class="relative block w-full appearance-none rounded-md border border-gray-300 px-3 py-3 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm" placeholder="Indirizzo email">
                </div>
                <div>
                    <label for="login-password" class="sr-only">Password</label>
                    <input id="login-password" name="password" type="password" autocomplete="current-password" required class="relative block w-full appearance-none rounded-md border border-gray-300 px-3 py-3 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm" placeholder="Password">
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember_me" type="checkbox" value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900">Ricordami</label>
                </div>
                <div class="text-sm">
                    <a href="forgot_password.php" class="font-medium text-indigo-600 hover:text-indigo-500">Password dimenticata?</a>
                </div>
            </div>
            <div>
                <button type="submit" class="group relative flex w-full justify-center rounded-md border border-transparent bg-indigo-600 py-3 px-4 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Accedi
                </button>
            </div>
        </form>

        <!-- Modulo di Registrazione -->
        <form id="register-form" class="mt-8 space-y-6 hidden" action="register.php" method="POST">
            <div class="space-y-4 rounded-md shadow-sm">
                <div>
                    <label for="register-username" class="sr-only">Username</label>
                    <input id="register-username" name="username" type="text" required class="relative block w-full appearance-none rounded-md border border-gray-300 px-3 py-3 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm" placeholder="Username">
                </div>
                <div>
                    <label for="register-email-address" class="sr-only">Indirizzo email</label>
                    <input id="register-email-address" name="email" type="email" autocomplete="email" required class="relative block w-full appearance-none rounded-md border border-gray-300 px-3 py-3 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm" placeholder="Indirizzo email">
                </div>
                <div>
                    <label for="register-password" class="sr-only">Password</label>
                    <input id="register-password" name="password" type="password" required class="relative block w-full appearance-none rounded-md border border-gray-300 px-3 py-3 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm" placeholder="Password">
                    <p id="register-requirement-text" class="mt-2 text-xs text-gray-500 transition-colors">La password deve contenere almeno 8 caratteri. <span id="register-char-count" class="font-medium">0/8</span></p>
                </div>
                 <div>
                    <label for="confirm-password" class="sr-only">Conferma Password</label>
                    <input id="confirm-password" name="confirm_password" type="password" required class="relative block w-full appearance-none rounded-md border border-gray-300 px-3 py-3 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm transition-colors" placeholder="Conferma Password">
                    <p id="password-match-error" class="mt-2 text-xs text-red-600 hidden">Le password non coincidono.</p>
                </div>
            </div>
            <div>
                <button id="register-submit-button" type="submit" class="group relative flex w-full justify-center rounded-md border border-transparent bg-indigo-600 py-3 px-4 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-opacity">
                    Registrati
                </button>
            </div>
        </form>
    </div>

    <script>
        // --- Gestione cambio form Login/Registrazione ---
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const switchToRegisterBtn = document.getElementById('switch-to-register');
        const formTitle = document.getElementById('form-title');
        const formSubtitle = document.getElementById('form-subtitle');
        const messageContainer = document.getElementById('message-container');
        const verificationContainer = document.getElementById('verification-link-container');

        function switchToRegister() {
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
            formTitle.textContent = 'Crea un nuovo account';
            formSubtitle.innerHTML = 'Sei già dei nostri? <button type="button" id="switch-to-login" class="font-medium text-indigo-600 hover:text-indigo-500">Accedi qui</button>';
            document.getElementById('switch-to-login').addEventListener('click', switchToLogin);
        }

        function switchToLogin() {
            registerForm.classList.add('hidden');
            loginForm.classList.remove('hidden');
            formTitle.textContent = 'Accedi al tuo account';
            formSubtitle.innerHTML = 'Oppure <button type="button" id="switch-to-register" class="font-medium text-indigo-600 hover:text-indigo-500">crea un nuovo account</button>';
            document.getElementById('switch-to-register').addEventListener('click', switchToRegister);
        }

        switchToRegisterBtn.addEventListener('click', switchToRegister);

        // --- Gestione Messaggi e Parametri URL ---
        window.onload = function() {
            const params = new URLSearchParams(window.location.search);
            if (params.has('message')) {
                const message = decodeURIComponent(params.get('message'));
                const type = params.get('type') || 'error';
                displayMessage(message, type);
            }
            if (params.has('action') && params.get('action') === 'register') {
                switchToRegister();
            }
        };

        function displayMessage(message, type) {
            messageContainer.textContent = message;
            messageContainer.className = 'p-4 text-sm rounded-lg'; // Reset classes
            if (type === 'success') {
                messageContainer.classList.add('bg-green-100', 'text-green-800');
            } else {
                messageContainer.classList.add('bg-red-100', 'text-red-800');
            }
        }
        
        // --- NUOVA LOGICA DI VALIDAZIONE REGISTRAZIONE ---
        const regPassword = document.getElementById('register-password');
        const confirmPassword = document.getElementById('confirm-password');
        const regSubmitButton = document.getElementById('register-submit-button');
        const charCountSpan = document.getElementById('register-char-count');
        const requirementText = document.getElementById('register-requirement-text');
        const matchErrorText = document.getElementById('password-match-error');

        function validateRegistrationForm() {
            const password = regPassword.value;
            const confirm = confirmPassword.value;
            const lengthOk = password.length >= 8;
            const passwordsMatch = password === confirm;

            // Aggiorna contatore caratteri
            charCountSpan.textContent = `${password.length}/8`;
            if (lengthOk) {
                requirementText.classList.remove('text-gray-500');
                requirementText.classList.add('text-green-600');
            } else {
                requirementText.classList.remove('text-green-600');
                requirementText.classList.add('text-gray-500');
            }

            // Controlla se le password coincidono
            if (confirm.length > 0 && !passwordsMatch) {
                matchErrorText.classList.remove('hidden');
            } else {
                matchErrorText.classList.add('hidden');
            }
            
            // Abilita/Disabilita il pulsante
            if (lengthOk && passwordsMatch) {
                regSubmitButton.disabled = false;
                regSubmitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                regSubmitButton.disabled = true;
                regSubmitButton.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        regPassword.addEventListener('input', validateRegistrationForm);
        confirmPassword.addEventListener('input', validateRegistrationForm);
        // Esegui al caricamento per disabilitare il pulsante all'inizio
        validateRegistrationForm();

    </script>
</body>
</html>