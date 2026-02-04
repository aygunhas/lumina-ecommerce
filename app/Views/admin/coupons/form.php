<?php
$isEdit = $coupon !== null;
$formAction = $isEdit ? $baseUrl . '/admin/coupons/edit?id=' . (int) $coupon['id'] : $baseUrl . '/admin/coupons/create';
$old = $old ?? [];
if ($isEdit && empty($old)) {
    $old = [
        'code' => $coupon['code'],
        'type' => $coupon['type'],
        'value' => $coupon['value'],
        'min_order_amount' => $coupon['min_order_amount'] !== null ? $coupon['min_order_amount'] : '',
        'max_use_count' => $coupon['max_use_count'] !== null ? $coupon['max_use_count'] : '',
        'starts_at' => $coupon['starts_at'] ? date('Y-m-d', strtotime($coupon['starts_at'])) : '',
        'ends_at' => $coupon['ends_at'] ? date('Y-m-d', strtotime($coupon['ends_at'])) : '',
        'is_active' => (int) $coupon['is_active'],
    ];
}
?>
<p style="margin-bottom: 1rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/admin/coupons">← Kupon listesine dön</a></p>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;"><?= $isEdit ? 'Kupon düzenle' : 'Yeni kupon' ?></h1>

<?php if (!empty($errors)): ?>
    <ul style="margin: 0 0 1rem; padding-left: 1.25rem; color: #c00;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars(is_string($err) ? $err : implode(' ', $err)) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars($formAction) ?>" style="max-width: 500px;">
    <p style="margin-bottom: 0.5rem;"><label for="code">Kupon kodu <span style="color: #c00;">*</span></label></p>
    <input type="text" id="code" name="code" value="<?= htmlspecialchars($old['code'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;" placeholder="Örn: HOSGELDIN10">

    <p style="margin-bottom: 0.5rem;"><label for="type">İndirim tipi</label></p>
    <select id="type" name="type" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <option value="percent" <?= ($old['type'] ?? 'percent') === 'percent' ? 'selected' : '' ?>>Yüzde (%)</option>
        <option value="fixed" <?= ($old['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Sabit tutar (₺)</option>
    </select>

    <p style="margin-bottom: 0.5rem;"><label for="value">İndirim değeri <span style="color: #c00;">*</span></label></p>
    <input type="text" id="value" name="value" value="<?= htmlspecialchars($old['value'] ?? '') ?>" required style="width: 120px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;" placeholder="10 veya 25.50">

    <p style="margin-bottom: 0.5rem;"><label for="min_order_amount">Minimum sepet tutarı (₺)</label></p>
    <input type="text" id="min_order_amount" name="min_order_amount" value="<?= htmlspecialchars($old['min_order_amount'] ?? '') ?>" style="width: 120px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;" placeholder="Boş = yok">

    <p style="margin-bottom: 0.5rem;"><label for="max_use_count">Maksimum kullanım sayısı</label></p>
    <input type="number" id="max_use_count" name="max_use_count" value="<?= htmlspecialchars($old['max_use_count'] ?? '') ?>" min="0" style="width: 120px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;" placeholder="Boş = sınırsız">

    <p style="margin-bottom: 0.5rem;"><label for="starts_at">Başlangıç tarihi</label></p>
    <input type="date" id="starts_at" name="starts_at" value="<?= htmlspecialchars($old['starts_at'] ?? '') ?>" style="padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="ends_at">Bitiş tarihi</label></p>
    <input type="date" id="ends_at" name="ends_at" value="<?= htmlspecialchars($old['ends_at'] ?? '') ?>" style="padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 1rem;">
        <label><input type="checkbox" name="is_active" value="1" <?= !empty($old['is_active']) ? 'checked' : '' ?>> Aktif</label>
    </p>

    <p>
        <button type="submit" style="padding: 0.5rem 1.25rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer;"><?= $isEdit ? 'Güncelle' : 'Oluştur' ?></button>
        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/coupons" style="margin-left: 1rem; color: #666;">İptal</a>
    </p>
</form>
