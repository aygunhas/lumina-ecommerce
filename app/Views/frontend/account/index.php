<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
    <span style="color: #999;"> / </span>
    <span>Hesabım</span>
</nav>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Hesabım</h1>
<p style="margin: 0 0 1.5rem; color: #666;">Merhaba, <?= htmlspecialchars($userName) ?>.</p>

<ul style="list-style: none; padding: 0; margin: 0;">
    <li style="margin-bottom: 0.75rem;">
        <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim/siparisler" style="display: block; padding: 1rem; background: #fff; border: 1px solid #eee; border-radius: 8px; text-decoration: none; color: #333;">📦 Siparişlerim</a>
    </li>
    <li style="margin-bottom: 0.75rem;">
        <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim/adresler" style="display: block; padding: 1rem; background: #fff; border: 1px solid #eee; border-radius: 8px; text-decoration: none; color: #333;">📍 Adreslerim</a>
    </li>
    <li style="margin-bottom: 0.75rem;">
        <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim/bilgilerim" style="display: block; padding: 1rem; background: #fff; border: 1px solid #eee; border-radius: 8px; text-decoration: none; color: #333;">👤 Bilgilerim</a>
    </li>
    <li style="margin-bottom: 0.75rem;">
        <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim/favoriler" style="display: block; padding: 1rem; background: #fff; border: 1px solid #eee; border-radius: 8px; text-decoration: none; color: #333;">❤️ Favorilerim</a>
    </li>
</ul>
