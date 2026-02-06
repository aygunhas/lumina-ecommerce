<?php include_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($title) ? htmlspecialchars($title) : 'Lumina Boutique' ?></title>
    <?php $baseUrl = $baseUrl ?? ''; ?>
    <link rel="icon" href="<?= $baseUrl ?>/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="alternate icon" href="<?= $baseUrl ?>/assets/images/lumina-logo.png" type="image/png">
    <!-- Google Fonts: Cinzel (400, 500, 600) ve Inter (300, 400, 500) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/main.css">
    <style>
        [x-cloak] { display: none !important; }
        /* Custom scrollbar - WebKit (Chrome, Safari, Edge) */
        *::-webkit-scrollbar { width: 4px; height: 4px; }
        *::-webkit-scrollbar-track { background: transparent; }
        *::-webkit-scrollbar-thumb { background: #000; border-radius: 9999px; }
        *::-webkit-scrollbar-thumb:hover { background: #0a0a0a; }
        /* Custom scrollbar - Firefox */
        * { scrollbar-width: thin; scrollbar-color: #000 transparent; }
    </style>
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
    <!-- Alpine.js (İnteraktivite) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
</head>
<body class="font-body text-primary antialiased bg-white selection:bg-black selection:text-white"
      x-data="{ loaded: false }"
      x-init="window.addEventListener('load', () => { setTimeout(() => loaded = true, 800); })">
    <?php include __DIR__ . '/loader.php'; ?>
    <div class="fixed top-0 left-0 w-full z-50 flex flex-col">
        <?php include __DIR__ . '/top-bar.php'; ?>
        <?php include __DIR__ . '/header.php'; ?>
    </div>
    <main class="min-h-screen pt-[140px]">
        <?php echo $content ?? ''; ?>
    </main>
    <?php include __DIR__ . '/footer.php'; ?>
    <?php include __DIR__ . '/cart-drawer.php'; ?>
    <?php include __DIR__ . '/toast.php'; ?>
    <?php
    if (!empty($_SESSION['toast_message'])) {
        $toastMsg = $_SESSION['toast_message'];
        $toastType = $_SESSION['toast_type'] ?? 'success';
        unset($_SESSION['toast_message'], $_SESSION['toast_type']);
    ?>
    <script>
    document.addEventListener('alpine:initialized', function() {
        window.dispatchEvent(new CustomEvent('notify', { detail: { message: <?= json_encode($toastMsg, JSON_UNESCAPED_UNICODE) ?>, type: <?= json_encode($toastType) ?> } }));
    });
    </script>
    <?php } ?>
    <?php include __DIR__ . '/cookie-banner.php'; ?>
</body>
</html>
