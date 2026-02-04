<?php
$isEdit = isset($address);
$formAction = $isEdit ? $baseUrl . '/hesabim/adresler/duzenle?id=' . (int) $address['id'] : $baseUrl . '/hesabim/adresler/ekle';
$backLink = $baseUrl . '/hesabim/adresler';
$formTitle = $isEdit ? 'Adres düzenle' : 'Yeni adres';
$old = $old ?? [];
if ($isEdit && empty($old)) {
    $old = [
        'title' => $address['title'] ?? '',
        'first_name' => $address['first_name'] ?? '',
        'last_name' => $address['last_name'] ?? '',
        'phone' => $address['phone'] ?? '',
        'city' => $address['city'] ?? '',
        'district' => $address['district'] ?? '',
        'address_line' => $address['address_line'] ?? '',
        'postal_code' => $address['postal_code'] ?? '',
        'is_default' => (int)($address['is_default'] ?? 0),
    ];
}
?>
<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
    <span style="color: #999;"> / </span>
    <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim" style="color: #666;">Hesabım</a>
    <span style="color: #999;"> / </span>
    <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim/adresler" style="color: #666;">Adreslerim</a>
    <span style="color: #999;"> / </span>
    <span><?= $formTitle ?></span>
</nav>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;"><?= $formTitle ?></h1>

<?php if (!empty($errors)): ?>
    <ul style="margin: 0 0 1rem; padding-left: 1.25rem; color: #c00;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars(is_string($err) ? $err : implode(' ', $err)) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars($formAction) ?>" style="max-width: 500px;">
    <p style="margin-bottom: 0.5rem;"><label for="title">Başlık (Ev, İş vb.)</label></p>
    <input type="text" id="title" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>" placeholder="Örn: Ev" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="first_name">Ad <span style="color: #c00;">*</span></label></p>
    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="last_name">Soyad <span style="color: #c00;">*</span></label></p>
    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="phone">Telefon <span style="color: #c00;">*</span></label></p>
    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="city">İl <span style="color: #c00;">*</span></label></p>
    <input type="text" id="city" name="city" value="<?= htmlspecialchars($old['city'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="district">İlçe <span style="color: #c00;">*</span></label></p>
    <input type="text" id="district" name="district" value="<?= htmlspecialchars($old['district'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="address_line">Adres <span style="color: #c00;">*</span></label></p>
    <textarea id="address_line" name="address_line" rows="2" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($old['address_line'] ?? '') ?></textarea>

    <p style="margin-bottom: 0.5rem;"><label for="postal_code">Posta kodu</label></p>
    <input type="text" id="postal_code" name="postal_code" value="<?= htmlspecialchars($old['postal_code'] ?? '') ?>" style="width: 120px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 1rem;">
        <label><input type="checkbox" name="is_default" value="1" <?= !empty($old['is_default']) ? 'checked' : '' ?>> Varsayılan adres olarak kullan</label>
    </p>

    <p>
        <button type="submit" style="padding: 0.5rem 1.25rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer;"><?= $isEdit ? 'Güncelle' : 'Kaydet' ?></button>
        <a href="<?= htmlspecialchars($backLink) ?>" style="margin-left: 1rem; color: #666;">İptal</a>
    </p>
</form>
