<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

// Funzione per recuperare gli aggiornamenti pubblicati
function get_published_changelog_updates($conn) {
    $updates = [];
    $sql = "SELECT * FROM changelog_updates WHERE is_published = 1 ORDER BY created_at DESC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $updates[] = $row;
        }
    }
    return $updates;
}

$updates = get_published_changelog_updates($conn);
$current_page = 'changelog'; // Per la sidebar, se necessario
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novità e Aggiornamenti - Bearget</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="theme.php">
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
        .changelog-content h2 { font-size: 1.5rem; font-weight: 700; margin-top: 1.5rem; margin-bottom: 0.5rem; }
        .changelog-content h3 { font-size: 1.25rem; font-weight: 600; margin-top: 1.25rem; margin-bottom: 0.5rem; }
        .changelog-content p { margin-bottom: 1rem; line-height: 1.6; }
        .changelog-content a { color: var(--color-primary-500); text-decoration: underline; }
        .changelog-content ul { list-style-type: disc; margin-left: 1.5rem; margin-bottom: 1rem; }
        .changelog-content ol { list-style-type: decimal; margin-left: 1.5rem; margin-bottom: 1rem; }
    </style>
</head>
<body class="text-gray-300">
    <div class="flex h-screen">
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-10 overflow-y-auto">
            <header class="mb-8">
                <h1 class="text-4xl font-bold text-white">Novità e Aggiornamenti</h1>
                <p class="text-lg text-gray-400 mt-2">Scopri le ultime funzionalità e miglioramenti di Bearget.</p>
            </header>

            <div class="space-y-12">
                <?php if (empty($updates)): ?>
                    <div class="text-center py-16">
                        <p class="text-gray-400">Nessun aggiornamento da mostrare al momento. Torna più tardi!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($updates as $update): ?>
                    <article class="bg-gray-800/50 rounded-2xl overflow-hidden">
                        <?php if (!empty($update['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($update['image_url']); ?>" alt="Immagine per l'aggiornamento <?php echo htmlspecialchars($update['title']); ?>" class="w-full h-64 object-cover">
                        <?php endif; ?>
                        <div class="p-8">
                            <div class="flex items-center gap-4 mb-2">
                                <span class="px-3 py-1 bg-primary-600 text-white text-sm font-bold rounded-full"><?php echo htmlspecialchars($update['version']); ?></span>
                                <time datetime="<?php echo date('Y-m-d', strtotime($update['created_at'])); ?>" class="text-sm text-gray-400"><?php echo date("d F Y", strtotime($update['created_at'])); ?></time>
                            </div>
                            <h2 class="text-3xl font-bold text-white mt-2"><?php echo htmlspecialchars($update['title']); ?></h2>
                            <p class="text-lg text-gray-400 mt-2"><?php echo htmlspecialchars($update['description']); ?></p>
                            <hr class="border-gray-700 my-6">
                            <div class="prose prose-invert max-w-none changelog-content text-gray-300">
                                <?php echo $update['content']; // HTML is expected and should be sanitized on input ?>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>