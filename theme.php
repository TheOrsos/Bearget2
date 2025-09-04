<?php
session_start();
header("Content-type: text/css");

// Tema di default se non impostato
$theme = $_SESSION['theme'] ?? 'dark-indigo';

// Definisci le palette di colori per ogni tema
$palettes = [
    'dark-indigo' => [
        '500' => '#6366f1', '600' => '#4f46e5', '700' => '#4338ca',
        'gray-100' => '#f3f4f6', 'gray-200' => '#e5e7eb', 'gray-300' => '#d1d5db', 'gray-400' => '#9ca3af',
        'gray-700' => '#374151', 'gray-800' => '#1f2937', 'gray-900' => '#111827',
    ],
    'forest-green' => [
        '500' => '#22c55e', '600' => '#16a34a', '700' => '#15803d',
        'gray-100' => '#f3f4f6', 'gray-200' => '#e5e7eb', 'gray-300' => '#d1d5db', 'gray-400' => '#9ca3af',
        'gray-700' => '#3f3f46', 'gray-800' => '#27272a', 'gray-900' => '#18181b',
    ],
    'ocean-blue' => [
        '500' => '#3b82f6', '600' => '#2563eb', '700' => '#1d4ed8',
        'gray-100' => '#f3f4f6', 'gray-200' => '#e5e7eb', 'gray-300' => '#d1d5db', 'gray-400' => '#9ca3af',
        'gray-700' => '#374151', 'gray-800' => '#1f2937', 'gray-900' => '#111827',
    ],
    'sunset-orange' => [
        '500' => '#f97316', '600' => '#ea580c', '700' => '#c2410c',
        'gray-100' => '#f3f4f6', 'gray-200' => '#e5e7eb', 'gray-300' => '#d1d5db', 'gray-400' => '#9ca3af',
        'gray-700' => '#44403c', 'gray-800' => '#292524', 'gray-900' => '#1c1917',
    ],
    'royal-purple' => [
        '500' => '#a855f7', '600' => '#9333ea', '700' => '#7e22ce',
        'gray-100' => '#f3f4f6', 'gray-200' => '#e5e7eb', 'gray-300' => '#d1d5db', 'gray-400' => '#9ca3af',
        'gray-700' => '#3730a3', 'gray-800' => '#312e81', 'gray-900' => '#1e1b4b',
    ],
    'graphite-gray' => [
        '500' => '#6b7280', '600' => '#4b5563', '700' => '#374151',
        'gray-100' => '#f3f4f6', 'gray-200' => '#e5e7eb', 'gray-300' => '#d1d5db', 'gray-400' => '#9ca3af',
        'gray-700' => '#374151', 'gray-800' => '#1f2937', 'gray-900' => '#111827',
    ],
    'dark-gold' => [
        '500' => '#d69e2e', '600' => '#b7791f', '700' => '#975a16',
        'gray-100' => '#f7fafc', 'gray-200' => '#edf2f7', 'gray-300' => '#e2e8f0', 'gray-400' => '#cbd5e0',
        'gray-700' => '#2d3748', 'gray-800' => '#1a202c', 'gray-900' => '#12151f',
    ],
    'modern-dark' => [
        '500' => '#a78bfa', '600' => '#8b5cf6', '700' => '#7c3aed',
        'gray-100' => '#f1f5f9', 'gray-200' => '#e2e8f0', 'gray-300' => '#cbd5e0', 'gray-400' => '#94a3b8',
        'gray-700' => '#334155', 'gray-800' => '#1e293b', 'gray-900' => '#0f172a',
    ],
    'foggy-gray' => [
        '500' => '#9ca3af', '600' => '#6b7280', '700' => '#4b5563',
        'gray-100' => '#f9fafb', 'gray-200' => '#f3f4f6', 'gray-300' => '#e5e7eb', 'gray-400' => '#d1d5db',
        'gray-700' => '#374151', 'gray-800' => '#1f2937', 'gray-900' => '#111827',
    ],
];

$current_palette = $palettes[$theme] ?? $palettes['dark-indigo'];
?>

:root {
    --color-primary-500: <?php echo $current_palette['500']; ?>;
    --color-primary-600: <?php echo $current_palette['600']; ?>;
    --color-primary-700: <?php echo $current_palette['700']; ?>;

    --color-gray-100: <?php echo $current_palette['gray-100']; ?>;
    --color-gray-200: <?php echo $current_palette['gray-200']; ?>;
    --color-gray-300: <?php echo $current_palette['gray-300']; ?>;
    --color-gray-400: <?php echo $current_palette['gray-400']; ?>;
    --color-gray-700: <?php echo $current_palette['gray-700']; ?>;
    --color-gray-800: <?php echo $current_palette['gray-800']; ?>;
    --color-gray-900: <?php echo $current_palette['gray-900']; ?>;

    --color-success: #22c55e; /* Green */
    --color-danger: #ef4444; /* Red */
    --color-warning: #f59e0b; /* Amber */
}

/* Rimosso per fixare il bug delle animazioni. Le transizioni andrebbero applicate a classi specifiche. */

/* Nasconde la scrollbar della sidebar */
#sidebar > div:first-of-type {
    scrollbar-width: none; /* Per Firefox */
    -ms-overflow-style: none;  /* Per Internet Explorer e Edge */
}
#sidebar > div:first-of-type::-webkit-scrollbar {
    display: none; /* Per Chrome, Safari e Opera */
}

<?php if ($theme === 'dark-gold'): ?>
@import url('https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&display=swap');
body {
    font-family: 'Lora', serif !important;
}
#sidebar {
    background-image: repeating-linear-gradient(
      45deg,
      rgba(214, 158, 46, 0.05),
      rgba(214, 158, 46, 0.05) 1px,
      transparent 1px,
      transparent 10px
    );
}
.bg-gray-800 {
    border: 1px solid var(--color-primary-600);
}
a.flex.items-center:hover, button:hover {
    background-color: var(--color-gray-700);
    border-color: var(--color-primary-500);
}
<?php endif; ?>

<?php if ($theme === 'modern-dark'): ?>

@keyframes moveBackground {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

body {
    background-color: #0f172a;
    background-image:
      radial-gradient(at 20% 20%, hsla(258, 80%, 30%, 0.2) 0px, transparent 50%),
      radial-gradient(at 80% 10%, hsla(280, 80%, 40%, 0.15) 0px, transparent 50%),
      radial-gradient(at 70% 80%, hsla(260, 80%, 35%, 0.1) 0px, transparent 50%),
      radial-gradient(at 10% 90%, hsla(240, 80%, 30%, 0.1) 0px, transparent 50%);
    background-size: 400% 400%;
    animation: moveBackground 20s ease-in-out infinite;
}

#sidebar, .bg-gray-800 {
    background-color: rgba(15, 23, 42, 0.6) !important; /* Use RGBA for transparency */
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.08);
}
a.flex.items-center:hover, button:hover {
    box-shadow: 0 0 20px -3px var(--color-primary-500);
    background-color: rgba(167, 139, 250, 0.15);
}
.bg-primary-600 {
    background: linear-gradient(45deg, var(--color-primary-500), var(--color-primary-700));
    box-shadow: 0 0 20px -5px var(--color-primary-600);
}
<?php endif; ?>

<?php if ($theme === 'foggy-gray'): ?>

@keyframes fogLayer1 {
  0% { transform: translate(-10%, -10%); }
  25% { transform: translate(10%, -20%); }
  50% { transform: translate(20%, 20%); }
  75% { transform: translate(-10%, 20%); }
  100% { transform: translate(-10%, -10%); }
}

@keyframes fogLayer2 {
  0% { transform: translate(10%, 10%); }
  25% { transform: translate(-10%, 20%); }
  50% { transform: translate(-20%, -20%); }
  75% { transform: translate(10%, -20%); }
  100% { transform: translate(10%, 10%); }
}

body {
    background-color: #1f2937; /* var(--color-gray-800) */
    position: relative;
    overflow: hidden;
}

body::before, body::after {
    content: '';
    position: fixed;
    top: -50%; left: -50%;
    width: 200%; height: 200%;
    z-index: -1; /* Place behind all content */
}

/* Layer 1 - Slower, larger fog banks */
body::before {
    background:
        radial-gradient(ellipse at 20% 30%, rgba(229, 231, 235, 0.25) 0%, transparent 40%),
        radial-gradient(ellipse at 80% 60%, rgba(209, 213, 219, 0.2) 0%, transparent 50%);
    animation: fogLayer1 60s ease-in-out infinite;
}

/* Layer 2 - Faster, smaller wisps */
body::after {
    background:
        radial-gradient(ellipse at 50% 50%, rgba(243, 244, 246, 0.15) 0%, transparent 30%),
        radial-gradient(ellipse at 10% 80%, rgba(229, 231, 235, 0.2) 0%, transparent 40%),
        radial-gradient(ellipse at 90% 10%, rgba(209, 213, 219, 0.1) 0%, transparent 35%);
    animation: fogLayer2 45s ease-in-out infinite alternate;
}

#sidebar, .bg-gray-800 {
    background-color: rgba(31, 41, 55, 0.6) !important; /* var(--color-gray-800) with alpha */
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    opacity: 0.85;
}

<?php endif; ?>