<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
    <span style="color: #999;"> / </span>
    <a href="<?= htmlspecialchars($baseUrl) ?>/sepet" style="color: #666;">Sepet</a>
    <span style="color: #999;"> / </span>
    <span>Ödeme</span>
</nav>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Ödeme</h1>

<?php if (!empty($errors)): ?>
    <ul style="margin: 0 0 1rem; padding-left: 1.25rem; color: #c00;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
$old = $old ?? [];
$userAddresses = $userAddresses ?? [];
$userEmail = $userEmail ?? '';
$defaultEmail = $old['guest_email'] ?? $userEmail;
?>
<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/odeme" style="max-width: 600px;" id="checkout-form">
    <h2 style="margin: 0 0 1rem; font-size: 1.1rem;">Teslimat bilgileri</h2>
    <?php if (!empty($userAddresses)): ?>
        <p style="margin-bottom: 0.5rem;"><label for="address_id">Kayıtlı adresim</label></p>
        <select id="address_id" name="address_id" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
            <option value="">— Yeni adres gir —</option>
            <?php foreach ($userAddresses as $a): ?>
                <option value="<?= (int) $a['id'] ?>" data-first="<?= htmlspecialchars($a['first_name'] ?? '') ?>" data-last="<?= htmlspecialchars($a['last_name'] ?? '') ?>" data-phone="<?= htmlspecialchars($a['phone'] ?? '') ?>" data-city="<?= htmlspecialchars($a['city'] ?? '') ?>" data-district="<?= htmlspecialchars($a['district'] ?? '') ?>" data-line="<?= htmlspecialchars($a['address_line'] ?? '') ?>" data-postal="<?= htmlspecialchars($a['postal_code'] ?? '') ?>">
                    <?= htmlspecialchars(($a['title'] ?? '') ? $a['title'] . ' — ' : '') . trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '')) . ', ' . ($a['district'] ?? '') . ' / ' . ($a['city'] ?? '') ?> 
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
    <p style="margin-bottom: 0.5rem;"><label for="guest_email">E-posta <span style="color: #c00;">*</span></label></p>
    <input type="email" id="guest_email" name="guest_email" value="<?= htmlspecialchars($defaultEmail) ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="shipping_first_name">Ad <span style="color: #c00;">*</span></label></p>
    <input type="text" id="shipping_first_name" name="shipping_first_name" value="<?= htmlspecialchars($old['shipping_first_name'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="shipping_last_name">Soyad <span style="color: #c00;">*</span></label></p>
    <input type="text" id="shipping_last_name" name="shipping_last_name" value="<?= htmlspecialchars($old['shipping_last_name'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="shipping_phone">Telefon <span style="color: #c00;">*</span></label></p>
    <input type="text" id="shipping_phone" name="shipping_phone" value="<?= htmlspecialchars($old['shipping_phone'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="shipping_city">İl <span style="color: #c00;">*</span></label></p>
    <input type="text" id="shipping_city" name="shipping_city" value="<?= htmlspecialchars($old['shipping_city'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="shipping_district">İlçe <span style="color: #c00;">*</span></label></p>
    <input type="text" id="shipping_district" name="shipping_district" value="<?= htmlspecialchars($old['shipping_district'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="shipping_address_line">Adres <span style="color: #c00;">*</span></label></p>
    <textarea id="shipping_address_line" name="shipping_address_line" rows="2" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($old['shipping_address_line'] ?? '') ?></textarea>

    <p style="margin-bottom: 0.5rem;"><label for="shipping_postal_code">Posta kodu</label></p>
    <input type="text" id="shipping_postal_code" name="shipping_postal_code" value="<?= htmlspecialchars($old['shipping_postal_code'] ?? '') ?>" style="width: 120px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="customer_notes">Sipariş notu</label></p>
    <textarea id="customer_notes" name="customer_notes" rows="2" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($old['customer_notes'] ?? '') ?></textarea>

    <h2 style="margin: 1.5rem 0 1rem; font-size: 1.1rem;">Kupon kodu</h2>
    <p style="margin-bottom: 0.5rem;"><label for="coupon_code">Kuponunuz varsa girin</label></p>
    <p style="margin-bottom: 1rem; display: flex; gap: 0.5rem; align-items: center;">
        <input type="text" id="coupon_code" name="coupon_code" value="<?= htmlspecialchars($old['coupon_code'] ?? '') ?>" placeholder="Örn: HOSGELDIN10" style="padding: 0.5rem; width: 180px; border: 1px solid #ccc; border-radius: 4px;">
    </p>
    <?php if (!empty($appliedCoupon)): ?>
        <p style="margin: -0.5rem 0 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px; font-size: 0.9rem;">✓ Kupon uygulandı: <strong><?= htmlspecialchars($appliedCoupon['code']) ?></strong> (<?= htmlspecialchars($appliedCoupon['label']) ?>) — <?= number_format($discountAmount ?? 0, 2, ',', '.') ?> ₺ indirim</p>
    <?php endif; ?>

    <h2 style="margin: 1.5rem 0 1rem; font-size: 1.1rem;">Ödeme yöntemi</h2>
    <p style="margin-bottom: 0.5rem;">
        <label><input type="radio" name="payment_method" value="cod" <?= ($old['payment_method'] ?? 'cod') === 'cod' ? 'checked' : '' ?>> Kapıda ödeme</label>
    </p>
    <p style="margin-bottom: 0.5rem;">
        <label><input type="radio" name="payment_method" value="bank_transfer" <?= ($old['payment_method'] ?? '') === 'bank_transfer' ? 'checked' : '' ?>> Havale / EFT</label>
    </p>
    <p style="margin-bottom: 1rem;">
        <label><input type="radio" name="payment_method" value="stripe" <?= ($old['payment_method'] ?? '') === 'stripe' ? 'checked' : '' ?>> Kredi kartı (Stripe — yakında)</label>
    </p>

    <div style="background: #f9f9f9; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <p style="margin: 0 0 0.25rem;">Sipariş özeti: <strong><?= count($items) ?></strong> ürün</p>
        <p style="margin: 0 0 0.25rem;">Ara toplam: <?= number_format($subtotal ?? 0, 2, ',', '.') ?> ₺</p>
        <p style="margin: 0 0 0.25rem;">Kargo: <?= number_format($shippingCost ?? 0, 2, ',', '.') ?> ₺</p>
        <?php if (($discountAmount ?? 0) > 0): ?>
            <p style="margin: 0 0 0.25rem;">İndirim: -<?= number_format($discountAmount, 2, ',', '.') ?> ₺</p>
        <?php endif; ?>
        <p style="margin: 0.25rem 0 0; font-weight: bold;">Toplam: <strong><?= number_format($total ?? 0, 2, ',', '.') ?> ₺</strong></p>
    </div>

    <button type="submit" style="padding: 0.75rem 2rem; background: #27ae60; color: #fff; border: none; border-radius: 6px; font-size: 1rem; cursor: pointer;">Siparişi tamamla</button>
    <a href="<?= htmlspecialchars($baseUrl) ?>/sepet" style="margin-left: 1rem; color: #666;">← Sepete dön</a>
</form>
<?php if (!empty($userAddresses)): ?>
<script>
(function() {
    var sel = document.getElementById('address_id');
    if (!sel) return;
    sel.addEventListener('change', function() {
        var opt = this.options[this.selectedIndex];
        if (!opt || opt.value === '') return;
        document.getElementById('shipping_first_name').value = opt.getAttribute('data-first') || '';
        document.getElementById('shipping_last_name').value = opt.getAttribute('data-last') || '';
        document.getElementById('shipping_phone').value = opt.getAttribute('data-phone') || '';
        document.getElementById('shipping_city').value = opt.getAttribute('data-city') || '';
        document.getElementById('shipping_district').value = opt.getAttribute('data-district') || '';
        document.getElementById('shipping_address_line').value = opt.getAttribute('data-line') || '';
        document.getElementById('shipping_postal_code').value = opt.getAttribute('data-postal') || '';
    });
})();
</script>
<?php endif; ?>
