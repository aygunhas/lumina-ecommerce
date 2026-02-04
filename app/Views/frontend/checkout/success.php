<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
</nav>

<div style="text-align: center; padding: 2rem 0;">
    <h1 style="margin: 0 0 0.5rem; font-size: 1.5rem; color: #27ae60;">Siparişiniz alındı</h1>
    <?php if ($orderNumber): ?>
        <p style="margin: 0 0 1rem; font-size: 1.1rem;">Sipariş numaranız: <strong><?= htmlspecialchars($orderNumber) ?></strong></p>
        <p style="margin: 0 0 1.5rem; color: #666;">Bu numarayı iletişim ve takip için kullanabilirsiniz.</p>
    <?php else: ?>
        <p style="margin: 0 0 1.5rem; color: #666;">Siparişiniz işleme alınmıştır.</p>
    <?php endif; ?>
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="display: inline-block; padding: 0.75rem 1.5rem; background: #2c3e50; color: #fff; text-decoration: none; border-radius: 6px;">Alışverişe devam et</a>
</div>
