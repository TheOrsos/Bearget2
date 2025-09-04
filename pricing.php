<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

// Recupera l'ID dell'utente per passarlo a Stripe
$user_id = $_SESSION['id'];

// *** INCOLLA QUI IL TUO PAYMENT LINK DI STRIPE ***
$stripe_payment_link = "https://buy.stripe.com/test_28E9ATfAN0HY5aj9I4ejK00";

// Aggiungiamo l'ID dell'utente al link per poterlo identificare dopo il pagamento
$final_link = $stripe_payment_link . "?client_reference_id=" . $user_id;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Abbonati a Bearget Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="theme.php">
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: { 500: 'var(--color-primary-500)', 600: 'var(--color-primary-600)', 700: 'var(--color-primary-700)' },
              gray: { 900: 'var(--color-gray-900)', 800: 'var(--color-gray-800)' },
            }
          }
        }
      }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; background-color: var(--color-gray-900); } </style>
</head>
<body class="text-gray-300">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-gray-800 rounded-2xl p-8 max-w-md w-full text-center">
            <h1 class="text-3xl font-bold text-white">Passa a Bearget Pro (Beta)</h1>
            <p class="text-gray-400 mt-2">Sblocca tutte le funzionalità per un controllo totale. Pagamento a titolo di supporto durante la fase beta.</p>
            
            <div class="my-8">
                <span class="text-5xl font-extrabold text-white">€4,99</span>
                <span class="text-gray-400">/ mese</span>
            </div>

            <ul class="space-y-3 text-left text-gray-300 mb-4">
                <li class="flex items-center">✓ Report, Budget e Obiettivi</li>
                <li class="flex items-center">✓ Transazioni Ricorrenti</li>
                <li class="flex items-center">✓ Fondi Comuni con gli amici</li>
                <li class="flex items-center">✓ Note con To-Do List</li>
            </ul>

        <!-- Riquadro legale Beta con icona info -->
        <div class="mb-4 p-4 bg-gray-700 rounded-lg text-left text-gray-300">
            <label class="flex items-start space-x-2">
                <input type="checkbox" id="betaConsent" class="form-checkbox mt-1 h-5 w-5 text-indigo-600">
                <span class="text-sm flex-1">
                    Ho letto e accetto che Bearget Pro è in fase beta e che i pagamenti sono a titolo di supporto. Non riceverò fattura.
                </span>
                <!-- Icona info con tooltip -->
                <span class="relative group ml-2">
                    <svg class="w-5 h-5 text-gray-400 cursor-pointer" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"></circle>
                        <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                        <circle cx="12" cy="16" r="1" fill="currentColor"></circle>
                    </svg>
                    <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 w-64 bg-gray-900 text-gray-100 text-xs rounded-lg p-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                        Bearget Pro è attualmente in fase beta. Tutti i pagamenti raccolti sono a titolo di supporto e non costituiscono acquisto commerciale. Nessuna fattura verrà emessa. L'utente partecipa consapevolmente al test del servizio.
                    </div>
                </span>
            </label>
        </div>

        <!-- Bottone Stripe -->
        <a href="<?php echo htmlspecialchars($final_link); ?>" id="proButton" class="block w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 rounded-lg text-lg transition-colors opacity-50">
            Abbonati Ora
        </a>
            
            <a href="dashboard.php" class="block mt-4 text-sm text-gray-500 hover:text-gray-400">Torna alla dashboard</a>
        </div>
    </div>

    <script>
        const betaConsent = document.getElementById('betaConsent');
        const proButton = document.getElementById('proButton');

        // Disabilita il bottone finché la checkbox non è spuntata
        proButton.addEventListener('click', function(e) {
            if (!betaConsent.checked) {
                e.preventDefault();
                alert('Devi accettare i termini della beta prima di procedere.');
            }
        });

        // Cambia l’aspetto del bottone quando la checkbox viene selezionata
        betaConsent.addEventListener('change', function() {
            if (this.checked) {
                proButton.classList.remove('opacity-50');
            } else {
                proButton.classList.add('opacity-50');
            }
        });
    </script>
</body>
</html>
