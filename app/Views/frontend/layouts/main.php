<?php
$baseUrl = $baseUrl ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($title) ? htmlspecialchars($title) : 'Lumina Boutique' ?></title>
    <link rel="icon" href="<?= $baseUrl ?>/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="alternate icon" href="<?= $baseUrl ?>/assets/images/lumina-logo.png" type="image/png">
    <!-- Google Fonts: Cinzel (400, 500, 600) ve Inter (300, 400, 500) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0a0a0a',
                        secondary: '#525252',
                        subtle: '#e5e5e5',
                        accent: '#000000',
                    },
                    fontFamily: {
                        display: ['Cinzel', 'serif'],
                        body: ['Inter', 'sans-serif'],
                    },
                    letterSpacing: {
                        luxury: '0.2em',
                    },
                    container: {
                        center: true,
                        padding: '2rem',
                    },
                },
            },
        }
    </script>
    <!-- Alpine.js + Intersect (lazy-load / scroll tetikleyici) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-body text-primary antialiased bg-white selection:bg-black selection:text-white">
    <?php require (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3)) . '/includes/header.php'; ?>
    <main class="min-h-screen">
        <?= $content ?? '' ?>
    </main>
    <?php require (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3)) . '/includes/footer.php'; ?>
</body>
</html>
