<?php
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}
?>
<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
    <span style="color: #999;"> / </span>
    <span>Sepetim</span>
</nav>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Sepetim</h1>

<?php if (!empty($_SESSION['cart_error'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.75rem; background: #fee; color: #c00; border-radius: 6px;"><?= htmlspecialchars($_SESSION['cart_error']) ?></p>
    <?php unset($_SESSION['cart_error']); ?>
<?php endif; ?>

<?php if (empty($items)): ?>
    <p style="color: #666;">Sepetiniz boş. <a href="<?= htmlspecialchars($baseUrl) ?>/">Alışverişe başlayın</a>.</p>
<?php else: ?>
    <table style="width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden; margin-bottom: 1.5rem;">
        <thead>
            <tr style="background: #f5f5f5;">
                <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Ürün</th>
                <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Fiyat</th>
                <th style="text-align: center; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Adet</th>
                <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Toplam</th>
                <th style="width: 80px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.75rem 1rem;">
                        <a href="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($item['slug']) ?>" style="color: #333; text-decoration: none;"><?= htmlspecialchars($item['name']) ?></a>
                        <?php if (!empty($item['attributes_summary'])): ?>
                            <span style="display: block; font-size: 0.85rem; color: #666;"><?= htmlspecialchars($item['attributes_summary']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 0.75rem 1rem; text-align: right;"><?= number_format($item['price'], 2, ',', '.') ?> ₺</td>
                    <td style="padding: 0.75rem 1rem; text-align: center;">
                        <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/sepet/guncelle" style="display: inline;">
                            <input type="hidden" name="cart_key" value="<?= htmlspecialchars($item['cart_key'] ?? 'p' . $item['id']) ?>">
                            <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="1" max="<?= (int) ($item['stock'] ?? 0) ?: 999 ?>" style="width: 60px; padding: 0.25rem; text-align: center;">
                            <button type="submit" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Güncelle</button>
                        </form>
                    </td>
                    <td style="padding: 0.75rem 1rem; text-align: right;"><?= number_format($item['total'], 2, ',', '.') ?> ₺</td>
                    <td style="padding: 0.75rem 1rem;">
                        <a href="<?= htmlspecialchars($baseUrl) ?>/sepet/sil?cart_key=<?= htmlspecialchars(urlencode($item['cart_key'] ?? 'p' . $item['id'])) ?>" style="color: #c0392b; font-size: 0.9rem;">Kaldır</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="max-width: 320px; margin-left: auto; background: #f9f9f9; padding: 1.25rem; border-radius: 8px;">
        <p style="margin: 0 0 0.5rem; display: flex; justify-content: space-between;"><span>Ara toplam</span> <strong><?= number_format($subtotal, 2, ',', '.') ?> ₺</strong></p>
        <p style="margin: 0 0 1rem; display: flex; justify-content: space-between;"><span>Kargo</span> <strong><?= number_format(0, 2, ',', '.') ?> ₺</strong></p>
        <p style="margin: 0 0 1rem; display: flex; justify-content: space-between; font-size: 1.1rem;"><span>Toplam</span> <strong><?= number_format($subtotal, 2, ',', '.') ?> ₺</strong></p>
        <a href="<?= htmlspecialchars($baseUrl) ?>/odeme" style="display: block; text-align: center; padding: 0.75rem 1.5rem; background: #2c3e50; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600;">Ödemeye geç</a>
    </div>
    <p style="margin-top: 1.5rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #3498db;">← Alışverişe devam et</a></p>
<?php endif; ?>
