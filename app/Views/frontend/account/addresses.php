<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
    <span style="color: #999;"> / </span>
    <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim" style="color: #666;">Hesabım</a>
    <span style="color: #999;"> / </span>
    <span>Adreslerim</span>
</nav>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Adreslerim</h1>

<?php if (!empty($_GET['added'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Adres eklendi.</p>
<?php endif; ?>
<?php if (!empty($_GET['updated'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Adres güncellendi.</p>
<?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Adres silindi.</p>
<?php endif; ?>

<p style="margin-bottom: 1rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/hesabim/adresler/ekle" style="display: inline-block; padding: 0.5rem 1rem; background: #2c3e50; color: #fff; text-decoration: none; border-radius: 6px;">Yeni adres ekle</a></p>

<?php if (empty($addresses)): ?>
    <p style="color: #666;">Henüz kayıtlı adresiniz yok. Ödeme sırasında adres girebilir veya buradan kaydedebilirsiniz.</p>
<?php else: ?>
    <ul style="list-style: none; padding: 0; margin: 0;">
        <?php foreach ($addresses as $a): ?>
            <li style="margin-bottom: 1rem; padding: 1rem; background: #fff; border: 1px solid #eee; border-radius: 8px;">
                <?php if (!empty($a['title'])): ?><strong><?= htmlspecialchars($a['title']) ?></strong><?php if ((int)($a['is_default']) === 1): ?> <span style="font-size: 0.8rem; background: #27ae60; color: #fff; padding: 0.15rem 0.4rem; border-radius: 4px;">Varsayılan</span><?php endif; ?><br><?php endif; ?>
                <?= htmlspecialchars(trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''))) ?><br>
                <?= htmlspecialchars($a['address_line']) ?><br>
                <?= htmlspecialchars(($a['district'] ?? '') . ' / ' . ($a['city'] ?? '')) ?><?= !empty($a['postal_code']) ? ' ' . htmlspecialchars($a['postal_code']) : '' ?><br>
                <?= htmlspecialchars($a['phone'] ?? '') ?>
                <p style="margin: 0.75rem 0 0; font-size: 0.9rem;">
                    <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim/adresler/duzenle?id=<?= (int) $a['id'] ?>" style="color: #3498db;">Düzenle</a>
                    <span style="color: #ccc;">|</span>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim/adresler/sil?id=<?= (int) $a['id'] ?>" style="color: #c0392b;">Sil</a>
                </p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p style="margin-top: 1.5rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/hesabim">← Hesabıma dön</a></p>
