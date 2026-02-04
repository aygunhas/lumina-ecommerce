<h1 style="margin: 0 0 1rem; font-size: 1.5rem;">Kategori sil</h1>
<p style="margin-bottom: 1rem;">“<strong><?= htmlspecialchars($category['name']) ?></strong>” kategorisini silmek istediğinize emin misiniz?</p>
<p style="margin-bottom: 1.5rem; color: #666; font-size: 0.95rem;">Alt kategoriler üst kategorisiz kalacak; bu kategorideki ürünler kategorisiz kalacak.</p>
<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/categories/delete?id=<?= (int) $category['id'] ?>" style="display: inline;">
    <button type="submit" style="padding: 0.5rem 1rem; background: #c0392b; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Evet, sil</button>
</form>
<a href="<?= htmlspecialchars($baseUrl) ?>/admin/categories" style="margin-left: 0.5rem; color: #666;">İptal</a>
