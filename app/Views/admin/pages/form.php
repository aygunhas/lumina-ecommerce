<?php
$isEdit = $page !== null;
$formAction = $isEdit ? $baseUrl . '/admin/pages/edit?id=' . (int) $page['id'] : $baseUrl . '/admin/pages/create';
$old = $old ?? [];
if ($isEdit && empty($old)) {
    $old = [
        'slug' => $page['slug'],
        'title' => $page['title'],
        'content' => $page['content'] ?? '',
        'meta_title' => $page['meta_title'] ?? '',
        'meta_description' => $page['meta_description'] ?? '',
        'sort_order' => (int) $page['sort_order'],
        'is_active' => (int) $page['is_active'],
    ];
}
?>
<p style="margin-bottom: 1rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/admin/pages">← Sayfa listesine dön</a></p>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;"><?= $isEdit ? 'Sayfa düzenle' : 'Yeni sayfa' ?></h1>

<?php if (!empty($errors)): ?>
    <ul style="margin: 0 0 1rem; padding-left: 1.25rem; color: #c00;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars(is_string($err) ? $err : implode(' ', $err)) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars($formAction) ?>" style="max-width: 700px;">
    <p style="margin-bottom: 0.5rem;"><label for="slug">Slug (URL) <span style="color: #c00;">*</span></label></p>
    <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($old['slug'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;" placeholder="ornek: sss, iade-kosullari, kvkk">
    <p style="margin: -0.5rem 0 1rem; font-size: 0.85rem; color: #666;">Mağazada /sayfa/<strong>slug</strong> adresinde açılır. Sadece küçük harf, rakam ve tire.</p>

    <p style="margin-bottom: 0.5rem;"><label for="title">Başlık <span style="color: #c00;">*</span></label></p>
    <input type="text" id="title" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="content">İçerik</label></p>
    <textarea id="content" name="content" rows="12" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px; font-family: inherit;"><?= htmlspecialchars($old['content'] ?? '') ?></textarea>

    <p style="margin-bottom: 0.5rem;"><label for="meta_title">Meta başlık (SEO)</label></p>
    <input type="text" id="meta_title" name="meta_title" value="<?= htmlspecialchars($old['meta_title'] ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="meta_description">Meta açıklama (SEO)</label></p>
    <textarea id="meta_description" name="meta_description" rows="2" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($old['meta_description'] ?? '') ?></textarea>

    <p style="margin-bottom: 0.5rem;"><label for="sort_order">Sıra</label></p>
    <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($old['sort_order'] ?? 0) ?>" min="0" style="width: 80px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 1rem;">
        <label><input type="checkbox" name="is_active" value="1" <?= !empty($old['is_active']) ? 'checked' : '' ?>> Aktif (mağazada görünsün)</label>
    </p>

    <p>
        <button type="submit" style="padding: 0.5rem 1.25rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer;"><?= $isEdit ? 'Güncelle' : 'Oluştur' ?></button>
        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pages" style="margin-left: 1rem; color: #666;">İptal</a>
    </p>
</form>
