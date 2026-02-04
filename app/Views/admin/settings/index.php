<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Ayarlar</h1>

<?php if (!empty($_GET['updated'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Ayarlar kaydedildi.</p>
<?php endif; ?>

<?php
$g = $general ?? [];
$s = $shipping ?? [];
$p = $payment ?? [];
?>
<form method="post" action="<?= $baseUrl ?>/admin/settings" style="max-width: 600px;">
    <fieldset style="margin-bottom: 2rem; padding: 1rem; border: 1px solid #ddd; border-radius: 8px;">
        <legend style="font-weight: bold;">Genel (B35)</legend>
        <p style="margin-bottom: 0.5rem;"><label for="site_name">Site adı</label></p>
        <input type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($g['site_name'] ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <p style="margin-bottom: 0.5rem;"><label for="contact_email">İletişim e-posta</label></p>
        <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($g['contact_email'] ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <p style="margin-bottom: 0.5rem;"><label for="contact_phone">İletişim telefon</label></p>
        <input type="text" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($g['contact_phone'] ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <p style="margin-bottom: 0.5rem;"><label for="contact_address">İletişim adres</label></p>
        <textarea id="contact_address" name="contact_address" rows="2" style="width: 100%; padding: 0.5rem; margin-bottom: 0; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($g['contact_address'] ?? '') ?></textarea>
    </fieldset>

    <fieldset style="margin-bottom: 2rem; padding: 1rem; border: 1px solid #ddd; border-radius: 8px;">
        <legend style="font-weight: bold;">Kargo (B36)</legend>
        <p style="margin-bottom: 0.5rem;"><label for="shipping_cost">Sabit kargo ücreti (₺) — boş: ücretsiz</label></p>
        <input type="text" id="shipping_cost" name="shipping_cost" value="<?= htmlspecialchars($s['shipping_cost'] ?? '') ?>" placeholder="0" style="width: 120px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <p style="margin-bottom: 0.5rem;"><label for="free_shipping_min">Ücretsiz kargo eşiği (₺) — bu tutar ve üzeri siparişte kargo ücretsiz</label></p>
        <input type="text" id="free_shipping_min" name="free_shipping_min" value="<?= htmlspecialchars($s['free_shipping_min'] ?? '') ?>" placeholder="Örn: 500" style="width: 120px; padding: 0.5rem; margin-bottom: 0; border: 1px solid #ccc; border-radius: 4px;">
    </fieldset>

    <fieldset style="margin-bottom: 2rem; padding: 1rem; border: 1px solid #ddd; border-radius: 8px;">
        <legend style="font-weight: bold;">Ödeme (B37)</legend>
        <p style="margin-bottom: 1rem;">
            <label><input type="checkbox" name="cod_enabled" value="1" <?= ($p['cod_enabled'] ?? '1') === '1' ? 'checked' : '' ?>> Kapıda ödeme açık</label><br>
            <label><input type="checkbox" name="bank_transfer_enabled" value="1" <?= ($p['bank_transfer_enabled'] ?? '1') === '1' ? 'checked' : '' ?>> Havale/EFT açık</label>
        </p>
        <p style="margin-bottom: 0.5rem;"><label for="bank_name">Banka adı</label></p>
        <input type="text" id="bank_name" name="bank_name" value="<?= htmlspecialchars($p['bank_name'] ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <p style="margin-bottom: 0.5rem;"><label for="bank_iban">IBAN</label></p>
        <input type="text" id="bank_iban" name="bank_iban" value="<?= htmlspecialchars($p['bank_iban'] ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <p style="margin-bottom: 0.5rem;"><label for="bank_account_name">Hesap sahibi / Açıklama</label></p>
        <input type="text" id="bank_account_name" name="bank_account_name" value="<?= htmlspecialchars($p['bank_account_name'] ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 0; border: 1px solid #ccc; border-radius: 4px;">
    </fieldset>

    <button type="submit" style="padding: 0.5rem 1.25rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Kaydet</button>
</form>
