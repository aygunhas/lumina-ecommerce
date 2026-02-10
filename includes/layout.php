<?php include_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($title) ? htmlspecialchars($title) : 'Lumina Boutique' ?></title>
    <?php 
    $baseUrl = $baseUrl ?? '';
    // baseUrl boşsa, otomatik olarak hesapla
    if (empty($baseUrl)) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = dirname($script);
        $baseUrl = ($base === '/' || $base === '\\') ? '' : $base;
    }
    // Debug için baseUrl'i console'a yazdır
    ?>
    <script>
    console.log('baseUrl:', <?= json_encode($baseUrl) ?>);
    </script>
    <link rel="icon" href="<?= $baseUrl ?>/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="alternate icon" href="<?= $baseUrl ?>/assets/images/lumina-logo.png" type="image/png">
    <!-- Google Fonts: Cinzel (400, 500, 600) ve Inter (300, 400, 500) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <!-- Tailwind CSS (Build) -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/main.css" onerror="console.error('CSS yüklenemedi:', this.href);" onload="console.log('CSS yüklendi:', this.href);">
    <!-- Alpine.js + Intersect + Collapse (SSS akordiyon vb.) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js" onerror="console.error('Alpine Intersect yüklenemedi');" onload="console.log('Alpine Intersect yüklendi');"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" onerror="console.error('Alpine Collapse yüklenemedi');" onload="console.log('Alpine Collapse yüklendi');"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js" onerror="console.error('Alpine.js yüklenemedi');" onload="console.log('Alpine.js yüklendi');"></script>
</head>
<body class="font-body text-primary antialiased bg-white selection:bg-black selection:text-white"
      x-data="{ loaded: false }"
      x-init="
        const self = this;
        function hideLoader() {
          setTimeout(() => { 
            const loader = document.querySelector('[x-show]');
            if (loader) loader.style.display = 'none';
            self.loaded = true;
          }, 800);
        }
        if (document.readyState === 'complete') {
          hideLoader();
        } else {
          window.addEventListener('load', hideLoader);
        }
        // Fallback: 3 saniye sonra zorla kapat
        setTimeout(() => { 
          const loader = document.querySelector('[x-show]');
          if (loader) loader.style.display = 'none';
          self.loaded = true;
        }, 3000);
      ">
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
