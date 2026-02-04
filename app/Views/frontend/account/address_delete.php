<?php $a = $address; ?>
<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
    <span style="color: #999;"> / </span>
    <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim" style="color: #666;">Hesabım</a>
    <span style="color: #999;"> / </span>
    <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim/adresler" style="color: #666;">Adreslerim</a>
    <span style="color: #999;"> / </span>
    <span>Adresi sil</span>
</nav>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Adresi sil</h1>
<p style="margin: 0 0 1rem;">Bu adresi silmek istediğinize emin misiniz?</p>
<div style="padding: 1rem; background: #fff; border: 1px solid #eee; border-radius: 8px; margin-bottom: 1rem;">
    <?= htmlspecialchars(trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''))) ?><br>
    <?= htmlspecialchars($a['address_line']) ?><br>
    <?= htmlspecialchars(($a['district'] ?? '') . ' / ' . ($a['city'] ?? '')) ?>
</div>
<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/hesabim/adresler/sil?id=<?= (int) $a['id'] ?>">
    <button type="submit" style="padding: 0.5rem 1rem; background: #c0392b; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Evet, sil</button>
    <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim/adresler" style="margin-left: 1rem; color: #666;">İptal</a>
</form>
