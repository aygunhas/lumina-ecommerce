<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Lumina Boutique Panel</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; background: #f0f0f0; }
        .admin-wrap { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 220px; background: #2c3e50; color: #fff; flex-shrink: 0; }
        .admin-sidebar a { display: block; padding: 0.75rem 1.25rem; color: #ecf0f1; text-decoration: none; border-left: 3px solid transparent; }
        .admin-sidebar a:hover { background: #34495e; color: #fff; }
        .admin-sidebar a.active { background: #34495e; border-left-color: #3498db; color: #fff; }
        .admin-sidebar .brand { padding: 1.25rem; font-weight: bold; font-size: 1rem; border-bottom: 1px solid #34495e; }
        .admin-main { flex: 1; display: flex; flex-direction: column; }
        .admin-top { background: #fff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .admin-top a { color: #333; text-decoration: none; }
        .admin-content { flex: 1; padding: 2rem; overflow: auto; }
    </style>
</head>
<body>
    <div class="admin-wrap">
        <aside class="admin-sidebar">
            <div class="brand">Lumina Boutique</div>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin" class="<?= ($currentUri === '/admin' || $currentUri === '/admin/') ? 'active' : '' ?>">Kontrol paneli</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/categories" class="<?= strpos($currentUri, '/admin/categories') === 0 ? 'active' : '' ?>">Kategoriler</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/attributes" class="<?= strpos($currentUri, '/admin/attributes') === 0 ? 'active' : '' ?>">Özellikler (Beden/Renk)</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products" class="<?= strpos($currentUri, '/admin/products') === 0 ? 'active' : '' ?>">Ürünler</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/orders" class="<?= strpos($currentUri, '/admin/orders') === 0 ? 'active' : '' ?>">Siparişler</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/customers" class="<?= strpos($currentUri, '/admin/customers') === 0 ? 'active' : '' ?>">Müşteriler</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/coupons" class="<?= strpos($currentUri, '/admin/coupons') === 0 ? 'active' : '' ?>">Kuponlar</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/reports" class="<?= strpos($currentUri, '/admin/reports') === 0 ? 'active' : '' ?>">Raporlar</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/contact-messages" class="<?= strpos($currentUri, '/admin/contact-messages') === 0 ? 'active' : '' ?>">İletişim mesajları</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pages" class="<?= strpos($currentUri, '/admin/pages') === 0 ? 'active' : '' ?>">Sayfalar</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/sliders" class="<?= strpos($currentUri, '/admin/sliders') === 0 ? 'active' : '' ?>">Slider</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/settings" class="<?= strpos($currentUri, '/admin/settings') === 0 ? 'active' : '' ?>">Ayarlar</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/logout">Çıkış</a>
        </aside>
        <main class="admin-main">
            <header class="admin-top">
                <strong>Yönetim paneli</strong>
                <a href="<?= htmlspecialchars($baseUrl) ?>/">Mağazayı aç</a>
            </header>
            <div class="admin-content">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>
</body>
</html>
