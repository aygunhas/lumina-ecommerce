<h1 style="margin: 0 0 1rem; font-size: 1.5rem;">Ürün sil</h1>
<?php if (!empty($error)): ?>
    <p style="margin-bottom: 1rem; padding: 0.75rem; background: #fee; color: #c00; border-radius: 6px;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<p style="margin-bottom: 1rem;">“<strong><?= htmlspecialchars($product['name']) ?></strong>” ürününü silmek istediğinize emin misiniz?</p>
<p style="margin-bottom: 1.5rem; color: #666; font-size: 0.95rem;">Bu işlem geri alınamaz. Ürün bu siparişlerde yer almıyorsa silinecektir.</p>
<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/products/delete?id=<?= (int) $product['id'] ?>" style="display: inline;">
    <button type="submit" style="padding: 0.5rem 1rem; background: #c0392b; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Evet, sil</button>
</form>
<a href="<?= htmlspecialchars($baseUrl) ?>/admin/products" style="margin-left: 0.5rem; color: #666;">İptal</a>
