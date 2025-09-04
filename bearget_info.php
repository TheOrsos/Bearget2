<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bearget - Informazioni sul Servizio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .section-title {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 0.5rem;
            display: inline-block;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-6 py-4 flex items-center">
            <svg class="h-10 w-10 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m12-3h.008v.008H15V6z" />
            </svg>
            <a href="https://bearget.kesug.com/" class="ml-3 text-3xl font-extrabold text-gray-900">Bearget</a>
        </div>
    </header>

    <main class="container mx-auto px-6 py-12">

        <!-- Sezione Descrizione Prodotto -->
        <section id="description" class="mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-6 section-title">Cos'è Bearget</h2>
            <p class="text-lg text-gray-700 leading-relaxed">
                Bearget è un'applicazione web di finanza personale (Software as a Service - SaaS) progettata per aiutare gli utenti a prendere il pieno controllo delle proprie finanze in modo semplice e intuitivo. L'applicazione permette di tracciare entrate e uscite, gestire più conti, creare budget personalizzati, impostare obiettivi di risparmio e automatizzare le transazioni ricorrenti.
            </p>
            <p class="mt-4 text-lg text-gray-700 leading-relaxed">
                Una delle nostre funzionalità distintive è la gestione dei <strong>Fondi Comuni</strong>, che permette a gruppi di amici o familiari di collaborare per raggiungere obiettivi finanziari condivisi, come l'organizzazione di una vacanza o la raccolta di fondi per un regalo.
            </p>
        </section>

        <!-- Sezione Funzionalità e Prezzi -->
        <section id="pricing" class="mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-8 section-title">Piani e Prezzi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Piano Gratuito -->
                <div class="bg-white rounded-2xl shadow-lg p-8 border">
                    <h3 class="text-2xl font-bold text-gray-900">Bearget Free</h3>
                    <p class="text-gray-600 mt-2">Le funzionalità essenziali per iniziare a gestire le tue finanze.</p>
                    <p class="text-4xl font-extrabold text-gray-900 my-6">€0 <span class="text-lg font-medium text-gray-500">/ per sempre</span></p>
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Gestione Transazioni Illimitate</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Gestione Conti e Categorie</li>
                    </ul>
                </div>
                <!-- Piano Pro Beta -->
                <div class="bg-white rounded-2xl shadow-lg p-8 border-2 border-indigo-600 relative">
                    <span class="absolute top-0 -translate-y-1/2 bg-indigo-600 text-white text-sm font-semibold px-3 py-1 rounded-full">Beta</span>
                    <h3 class="text-2xl font-bold text-indigo-600">Bearget Pro (Beta)</h3>
                    <p class="text-gray-600 mt-2">Sblocca tutte le funzionalità avanzate per un controllo totale. Pagamento a titolo di supporto durante la fase beta.</p>
                    <p class="text-4xl font-extrabold text-gray-900 my-6">€4,99 <span class="text-lg font-medium text-gray-500">/ mese</span></p>
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Tutto del piano Free, più:</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Report Finanziari Dettagliati</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Creazione di Budget Mensili</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Obiettivi di Risparmio</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Transazioni Ricorrenti Automatiche</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Fondi Comuni Collaborativi</li>
                    </ul>
                    <p class="mt-4 text-sm text-yellow-700 bg-yellow-100 p-3 rounded">
                        <strong>Nota legale:</strong> questa versione è in fase beta. I pagamenti servono a sostenere lo sviluppo del progetto. Non costituiscono vendita commerciale ufficiale e non verranno emesse fatture. Quando il servizio sarà ufficialmente lanciato, tutte le vendite saranno gestite legalmente con partita IVA.
                    </p>
                </div>
            </div>
        </section>

        <!-- Sezione Termini e Privacy -->
        <section id="legal" class="mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-6 section-title">Termini e Condizioni</h2>
            <div class="space-y-4 text-gray-700 bg-white p-6 rounded-lg shadow">
                <h4 class="font-bold">1. Accettazione dei Termini</h4>
                <p>Utilizzando l'applicazione Bearget, l'utente accetta di essere vincolato da questi Termini di Servizio. Il servizio è fornito "così com'è".</p>
                <h4 class="font-bold">2. Descrizione del Servizio</h4>
                <p>Bearget offre un piano gratuito e un abbonamento a pagamento ("Bearget Pro Beta"). I pagamenti sono raccolti a titolo di supporto durante la fase beta e non costituiscono vendita commerciale ufficiale. L'abbonamento si rinnova automaticamente su base mensile, salvo annullamento da parte dell’utente. I pagamenti vengono elaborati in modo sicuro tramite <a href="https://stripe.com" target="_blank" class="text-indigo-600 underline">Stripe</a>, che può richiedere autenticazione aggiuntiva (Strong Customer Authentication - PSD2).</p>
                <h4 class="font-bold">3. Politica di Rimborso e Recesso</h4>
                <p>I pagamenti per gli abbonamenti beta non sono rimborsabili, salvo diversa indicazione prevista dalla legge. Gli utenti dell’UE hanno diritto a recedere entro 14 giorni, salvo che abbiano accettato l’avvio immediato del servizio e la conseguente rinuncia al diritto di recesso.</p>
                <h4 class="font-bold">4. Sicurezza</h4>
                <p>Stripe è certificata PCI DSS Level 1. L'integrazione di Bearget segue le pratiche raccomandate di sicurezza, ma l'utente è responsabile della riservatezza delle proprie credenziali di accesso.</p>
            </div>

            <h2 class="text-4xl font-bold text-gray-900 mt-12 mb-6 section-title">Informativa sulla Privacy</h2>
            <div class="space-y-4 text-gray-700 bg-white p-6 rounded-lg shadow">
                <h4 class="font-bold">1. Dati Raccolti</h4>
                <p>Raccogliamo solo i dati strettamente necessari per il funzionamento del servizio (es. email, nome utente). I dati finanziari inseriti dall'utente restano di sua esclusiva proprietà.</p>
                <h4 class="font-bold">2. Dati di Pagamento</h4>
                <p>Non memorizziamo i dati delle carte di credito. Tutte le transazioni sono gestite in modo sicuro da Stripe, conforme agli standard PCI DSS.</p>
                <h4 class="font-bold">3. Finalità e Conservazione</h4>
                <p>I dati sono trattati per fornire il servizio, adempiere a obblighi legali e, se prestato consenso, inviare comunicazioni promozionali. Sono conservati solo per il tempo necessario.</p>
                <h4 class="font-bold">4. Diritti dell’Utente</h4>
                <p>L’utente può esercitare i diritti di accesso, rettifica, cancellazione, portabilità, limitazione e opposizione.</p>
                <h4 class="font-bold">5. Cookie</h4>
                <p>Il sito utilizza cookie tecnici e, previo consenso, cookie analitici o di terze parti. Consulta la <a href="/cookie-policy.html" class="text-indigo-600 underline">Cookie Policy</a>.</p>
            </div>
        </section>

        <!-- Sezione Contatti -->
        <section id="contact">
            <h2 class="text-4xl font-bold text-gray-900 mb-6 section-title">Contatti</h2>
            <div class="bg-white p-6 rounded-lg shadow">
                <p class="text-lg text-gray-700">Per qualsiasi domanda, richiesta di supporto o informazione, non esitare a contattarci.</p>
                <ul class="mt-4 space-y-2">
                    <li><strong>Nome:</strong> Orso Christian</li>
                    <li><strong>Email:</strong> <a href="bearget.theorsos@gmail.com" class="text-indigo-600 hover:underline">bearget.theorsos@gmail.com</a></li>
                    <li><strong>Locazione:</strong> Italia</li>
                </ul>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-white mt-12">
        <div class="container mx-auto px-6 py-4 text-center text-gray-600">
            &copy; <?php echo date("Y"); ?> Bearget. Tutti i diritti riservati.
        </div>
    </footer>

    <!-- Banner Cookie -->
    <div id="cookie-banner" class="fixed bottom-0 left-0 w-full bg-white shadow-lg p-4 z-50 hidden">
      <div class="max-w-4xl mx-auto flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0">
        <p class="text-gray-700 text-sm">
          Questo sito utilizza cookie tecnici e, previo consenso, cookie analitici e di terze parti.
          Puoi accettare, rifiutare o personalizzare le tue preferenze.
        </p>
        <div class="flex space-x-2">
          <button id="accept-cookies" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">Accetta</button>
          <button id="reject-cookies" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">Rifiuta</button>
          <button id="customize-cookies" class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700">Personalizza</button>
        </div>
      </div>
    </div>

    <script>
    // Mostra banner se non è stata fatta una scelta
    if (!localStorage.getItem("cookieChoice")) {
      document.getElementById("cookie-banner").classList.remove("hidden");
    }

    document.getElementById("accept-cookies").addEventListener("click", function() {
      localStorage.setItem("cookieChoice", "accepted");
      document.getElementById("cookie-banner").classList.add("hidden");
    });

    document.getElementById("reject-cookies").addEventListener("click", function() {
      localStorage.setItem("cookieChoice", "rejected");
      document.getElementById("cookie-banner").classList.add("hidden");
    });

    document.getElementById("customize-cookies").addEventListener("click", function() {
      alert("Qui puoi aprire un pannello per gestire le preferenze dei cookie.");
    });
    </script>

</body>
</html>
