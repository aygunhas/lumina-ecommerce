<?php
$isEdit = $slider !== null;
$formAction = $isEdit ? $baseUrl . '/admin/sliders/edit?id=' . (int) $slider['id'] : $baseUrl . '/admin/sliders/create';
$old = $old ?? [];
?>
<p style="margin-bottom: 1rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/admin/sliders">← Slider listesine dön</a></p>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;"><?= $isEdit ? 'Slider düzenle' : 'Yeni slider' ?></h1>

<?php if (!empty($errors)): ?>
    <ul style="margin: 0 0 1rem; padding-left: 1.25rem; color: #c00;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars(is_string($err) ? $err : implode(' ', $err)) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars($formAction) ?>" enctype="multipart/form-data" style="max-width: 600px;">
    <p style="margin-bottom: 0.5rem;"><label for="image">Görsel <?= $isEdit ? '' : '<span style="color: #c00;">*</span>' ?></label></p>
    <?php if ($isEdit && !empty($slider['image'])): ?>
        <p style="margin-bottom: 0.5rem;"><img src="<?= htmlspecialchars($baseUrl) ?>/<?= htmlspecialchars($slider['image']) ?>" alt="" style="max-width: 300px; max-height: 150px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px;"></p>
    <?php endif; ?>
    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" style="margin-bottom: 1rem;">
    <p style="margin: -0.5rem 0 1rem; font-size: 0.85rem; color: #666;">JPG, PNG veya WebP. Max 3 MB. <?= $isEdit ? 'Boş bırakırsanız mevcut görsel kalır.' : '' ?></p>

    <p style="margin-bottom: 0.5rem;"><label for="title">Başlık</label></p>
    <input type="text" id="title" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="subtitle">Alt başlık</label></p>
    <input type="text" id="subtitle" name="subtitle" value="<?= htmlspecialchars($old['subtitle'] ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="link">Link (URL)</label></p>
    <input type="text" id="link" name="link" value="<?= htmlspecialchars($old['link'] ?? '') ?>" placeholder="https:// veya /kategori/..." style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="link_text">Link metni</label></p>
    <input type="text" id="link_text" name="link_text" value="<?= htmlspecialchars($old['link_text'] ?? '') ?>" placeholder="Alışverişe başla" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="sort_order">Sıra</label></p>
    <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($old['sort_order'] ?? 0) ?>" min="0" style="width: 80px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 1rem;">
        <label><input type="checkbox" name="is_active" value="1" <?= !empty($old['is_active']) ? 'checked' : '' ?>> Aktif (anasayfada görünsün)</label>
    </p>

    <p>
        <button type="submit" style="padding: 0.5rem 1.25rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer;"><?= $isEdit ? 'Güncelle' : 'Oluştur' ?></button>
        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/sliders" style="margin-left: 1rem; color: #666;">İptal</a>
    </p>
</form>
