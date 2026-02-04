<?php
$isEdit = $attribute !== null;
$formAction = $isEdit ? $baseUrl . '/admin/attributes/edit?id=' . (int) $attribute['id'] : $baseUrl . '/admin/attributes/create';
$old = $old ?? [];
if ($isEdit && empty($old)) {
    $old = [
        'name' => $attribute['name'],
        'type' => $attribute['type'],
        'sort_order' => (int) $attribute['sort_order'],
    ];
}
$typeLabels = ['size' => 'Beden', 'color' => 'Renk', 'other' => 'Diğer'];
?>
<p style="margin-bottom: 1rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/admin/attributes">← Özellik listesine dön</a></p>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;"><?= $isEdit ? 'Özellik düzenle' : 'Yeni özellik' ?></h1>

<?php if (!empty($errors)): ?>
    <ul style="margin: 0 0 1rem; padding-left: 1.25rem; color: #c00;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars(is_string($err) ? $err : implode(' ', $err)) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($isEdit && !empty($_GET['updated'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Özellik güncellendi.</p>
<?php endif; ?>
<?php if ($isEdit && !empty($_GET['value_added'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Değer eklendi.</p>
<?php endif; ?>
<?php if ($isEdit && !empty($_GET['value_deleted'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Değer silindi.</p>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars($formAction) ?>" style="max-width: 500px; margin-bottom: 2rem;">
    <input type="hidden" name="_form" value="attribute">
    <p style="margin-bottom: 0.5rem;"><label for="name">Özellik adı <span style="color: #c00;">*</span></label></p>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;" placeholder="Beden, Renk vb.">

    <p style="margin-bottom: 0.5rem;"><label for="type">Tip</label></p>
    <select id="type" name="type" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <option value="size" <?= ($old['type'] ?? '') === 'size' ? 'selected' : '' ?>>Beden</option>
        <option value="color" <?= ($old['type'] ?? '') === 'color' ? 'selected' : '' ?>>Renk</option>
        <option value="other" <?= ($old['type'] ?? 'other') === 'other' ? 'selected' : '' ?>>Diğer</option>
    </select>

    <p style="margin-bottom: 0.5rem;"><label for="sort_order">Sıra</label></p>
    <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($old['sort_order'] ?? 0) ?>" min="0" style="width: 80px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p>
        <button type="submit" style="padding: 0.5rem 1.25rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer;"><?= $isEdit ? 'Güncelle' : 'Oluştur' ?></button>
        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/attributes" style="margin-left: 1rem; color: #666;">İptal</a>
    </p>
</form>

<?php if ($isEdit): ?>
    <h2 style="margin: 0 0 1rem; font-size: 1.2rem;">Değerler (<?= htmlspecialchars($attribute['name']) ?>)</h2>
    <?php if (!empty($attribute['values'])): ?>
        <table style="width: 100%; max-width: 500px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden; margin-bottom: 1.5rem;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="text-align: left; padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee;">Değer</th>
                    <?php if (($attribute['type'] ?? '') === 'color'): ?>
                        <th style="text-align: left; padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee;">Renk</th>
                    <?php endif; ?>
                    <th style="text-align: left; padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attribute['values'] as $v): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.6rem 0.75rem;"><?= htmlspecialchars($v['value']) ?></td>
                        <?php if (($attribute['type'] ?? '') === 'color'): ?>
                            <td style="padding: 0.6rem 0.75rem;">
                                <?php if (!empty($v['color_hex'])): ?>
                                    <span style="display: inline-block; width: 20px; height: 20px; background: <?= htmlspecialchars($v['color_hex']) ?>; border: 1px solid #ccc; border-radius: 4px; vertical-align: middle;"></span>
                                    <code style="font-size: 0.8rem;"><?= htmlspecialchars($v['color_hex']) ?></code>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td style="padding: 0.6rem 0.75rem;">
                            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/attributes/delete-value?id=<?= (int) $v['id'] ?>&attribute_id=<?= (int) $attribute['id'] ?>" style="color: #c0392b;" onclick="return confirm('Bu değeri silmek istediğinize emin misiniz?');">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color: #666; margin-bottom: 1rem;">Henüz değer yok. Aşağıdan ekleyin (örn. Beden: S, M, L — Renk: Kırmızı, Mavi).</p>
    <?php endif; ?>

    <h3 style="margin: 0 0 0.75rem; font-size: 1rem;">Yeni değer ekle</h3>
    <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/attributes/edit?id=<?= (int) $attribute['id'] ?>" style="max-width: 500px;">
        <input type="hidden" name="_form" value="add_value">
        <p style="margin-bottom: 0.5rem;"><label for="value">Değer <span style="color: #c00;">*</span></label></p>
        <input type="text" id="value" name="value" value="" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;" placeholder="<?= ($attribute['type'] ?? '') === 'color' ? 'Kırmızı' : 'S, M, L' ?>">
        <?php if (($attribute['type'] ?? '') === 'color'): ?>
            <p style="margin-bottom: 0.5rem;"><label for="color_hex">Renk kodu (hex)</label></p>
            <input type="text" id="color_hex" name="color_hex" value="" style="width: 120px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;" placeholder="#c62828">
        <?php endif; ?>
        <p style="margin-bottom: 0.5rem;"><label for="val_sort_order">Sıra</label></p>
        <input type="number" id="val_sort_order" name="sort_order" value="0" min="0" style="width: 80px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <p>
            <button type="submit" style="padding: 0.5rem 1rem; background: #27ae60; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Değer ekle</button>
        </p>
    </form>
<?php endif; ?>
