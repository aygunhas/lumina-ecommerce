<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Yeni kategori</h1>

<?php if (!empty($errors)): ?>
    <ul style="margin: 0 0 1rem; padding-left: 1.25rem; color: #c00;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/categories/create" style="max-width: 500px;">
    <p style="margin-bottom: 0.5rem;"><label for="name">Kategori adı <span style="color: #c00;">*</span></label></p>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="parent_id">Üst kategori</label></p>
    <select id="parent_id" name="parent_id" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <option value="">— Yok (ana kategori) —</option>
        <?php foreach ($parents as $p): ?>
            <option value="<?= (int) $p['id'] ?>" <?= isset($old['parent_id']) && (int) $old['parent_id'] === (int) $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <p style="margin-bottom: 0.5rem;"><label for="description">Açıklama</label></p>
    <textarea id="description" name="description" rows="3" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>

    <p style="margin-bottom: 0.5rem;"><label for="sort_order">Sıra</label></p>
    <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($old['sort_order'] ?? 0) ?>" min="0" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px; max-width: 120px;">

    <p style="margin-bottom: 0.5rem;">
        <label><input type="checkbox" name="is_active" value="1" <?= !isset($old['is_active']) || $old['is_active'] ? 'checked' : '' ?>> Aktif</label>
    </p>

    <p style="margin-top: 1.5rem;">
        <button type="submit" style="padding: 0.5rem 1.25rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Kaydet</button>
        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/categories" style="margin-left: 0.5rem; color: #666;">İptal</a>
    </p>
</form>
