<?php
$baseUrl = $baseUrl ?? '';
$items = $items ?? [];
$subtotal = $subtotal ?? 0;
$freeShippingMin = $freeShippingMin ?? 0;
$shippingCost = $shippingCost ?? 0;
$total = $total ?? $subtotal + $shippingCost;
$itemCount = 0;
foreach ($items as $item) {
    $itemCount += (int) ($item['quantity'] ?? 0);
}
$freeShippingRemaining = $freeShippingMin > 0 && $subtotal < $freeShippingMin ? max(0, $freeShippingMin - $subtotal) : 0;
?>
<div class="max-w-[1400px] mx-auto px-6 pt-12 pb-8">
    <!-- Sayfa Başlığı -->
    <header class="pt-12 pb-8">
        <h1 class="font-display text-3xl tracking-tight text-primary">
            ALIŞVERİŞ SEPETİNİZ (<span id="cart-page-item-count"><?= (int) $itemCount ?></span>)
        </h1>
        <?php if ($freeShippingRemaining > 0): ?>
            <p class="mt-3 text-sm text-gray-500">
                Ücretsiz kargo hakkınızın dolmasına <strong>₺<?= number_format($freeShippingRemaining, 2, ',', '.') ?></strong> kaldı.
            </p>
        <?php elseif ($freeShippingMin > 0 && $subtotal >= $freeShippingMin): ?>
            <p class="mt-3 text-sm text-gray-500">Ücretsiz kargo hakkınızı kazandınız.</p>
        <?php endif; ?>
    </header>

<?php if (!empty($_SESSION['cart_error'])): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded text-sm text-red-700">
            <?= htmlspecialchars($_SESSION['cart_error']) ?>
        </div>
    <?php unset($_SESSION['cart_error']); ?>
<?php endif; ?>

<?php if (empty($items)): ?>
        <div class="py-16 text-center">
            <p class="text-secondary mb-4">Sepetiniz boş.</p>
            <a href="<?= htmlspecialchars($baseUrl) ?>/" class="inline-block text-xs font-bold uppercase tracking-widest text-primary underline hover:no-underline">Alışverişe başlayın</a>
        </div>
<?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20">
            <!-- Sol Kolon: Ürün Listesi -->
            <div class="lg:col-span-8">
                <!-- Başlık Satırı -->
                <div class="border-b border-gray-100 pb-4 text-xs tracking-widest text-gray-400 mb-6 hidden md:grid md:grid-cols-12 gap-4">
                    <div class="col-span-6">ÜRÜN</div>
                    <div class="col-span-3">MİKTAR</div>
                    <div class="col-span-3 text-right">FİYAT</div>
                </div>

                <div id="cart-page-rows">
            <?php foreach ($items as $item): ?>
                    <?php
                    $cartKey = $item['cart_key'] ?? ('p' . ($item['id'] ?? ''));
                    $qty = (int) ($item['quantity'] ?? 1);
                    $maxQty = (int) ($item['stock'] ?? 0) > 0 ? (int) $item['stock'] : 999;
                    $imgSrc = !empty($item['image_path']) ? $baseUrl . '/' . $item['image_path'] : '';
                    ?>
                    <div class="cart-page-row grid grid-cols-12 items-center gap-4 py-6 border-b border-gray-100" data-cart-key="<?= htmlspecialchars($cartKey) ?>" data-max-qty="<?= $maxQty ?>">
                        <!-- Görsel & Bilgi -->
                        <div class="col-span-12 md:col-span-6 flex gap-6">
                            <a href="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($item['slug'] ?? '') ?>" class="flex-shrink-0 w-24 aspect-[3/4] bg-gray-100 overflow-hidden rounded-sm">
                                <?php if ($imgSrc): ?>
                                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="w-full h-full flex items-center justify-center text-[10px] text-gray-400">Görsel yok</span>
                                <?php endif; ?>
                            </a>
                            <div class="min-w-0 flex-1">
                                <a href="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($item['slug'] ?? '') ?>" class="font-medium text-primary hover:underline block">
                                    <?= htmlspecialchars($item['name'] ?? '') ?>
                                </a>
                                <?php if (!empty($item['attributes_summary'])): ?>
                                    <p class="text-gray-500 text-xs mt-1"><?= htmlspecialchars($item['attributes_summary']) ?></p>
                                <?php endif; ?>
                                <?php if ($maxQty > 0 && $maxQty < 999 && $maxQty <= 5): ?>
                                    <p class="text-xs text-amber-600 mt-1 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                        </svg>
                                        Stokta sadece <?= $maxQty ?> adet kaldı
                                    </p>
                                <?php elseif ($maxQty > 0 && $maxQty < 999 && $maxQty <= 10): ?>
                                    <p class="text-xs text-gray-500 mt-1">Stok: <?= $maxQty ?> adet</p>
                                <?php endif; ?>
                                <button type="button" 
                                        class="cart-remove-btn text-xs text-red-900 underline mt-3 hover:no-underline cursor-pointer inline-block bg-transparent border-0 p-0"
                                        data-cart-key="<?= htmlspecialchars($cartKey) ?>"
                                        aria-label="Ürünü sepetten çıkar">
                                    Sil
                                </button>
                            </div>
                        </div>

                        <!-- Miktar (AJAX) -->
                        <div class="col-span-12 md:col-span-3 flex items-center">
                            <div class="flex items-center border border-gray-200 w-24 h-10 rounded-sm overflow-hidden">
                                <button type="button" class="cart-qty-minus w-8 h-full flex items-center justify-center hover:bg-gray-50 text-primary transition" aria-label="Azalt">−</button>
                                <span class="cart-row-qty flex-1 text-center text-sm py-2 select-none"><?= $qty ?></span>
                                <button type="button" class="cart-qty-plus w-8 h-full flex items-center justify-center hover:bg-gray-50 text-primary transition disabled:opacity-40 disabled:cursor-not-allowed" <?= $qty >= $maxQty ? 'disabled' : '' ?> aria-label="Artır">+</button>
                            </div>
                        </div>

                        <!-- Fiyat -->
                        <div class="col-span-12 md:col-span-3 text-right">
                            <p class="cart-row-line-total text-sm font-medium text-primary">₺<?= number_format((float) ($item['total'] ?? 0), 2, ',', '.') ?></p>
                        </div>
                    </div>
            <?php endforeach; ?>
                </div>
            </div>

            <!-- Sağ Kolon: Sipariş Özeti -->
            <div class="lg:col-span-4">
                <div class="lg:sticky lg:top-32 lg:h-fit bg-gray-50 p-8 rounded-sm">
                    <h2 class="text-xs font-bold tracking-widest text-primary mb-6">SİPARİŞ ÖZETİ</h2>
                    <div class="flex justify-between text-sm text-gray-600 mb-4">
                        <span>Ara Toplam</span>
                        <span id="cart-page-subtotal">₺<?= number_format($subtotal, 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600 mb-4">
                        <span>Kargo</span>
                        <span id="cart-page-shipping"><?= $shippingCost > 0 ? '₺' . number_format($shippingCost, 2, ',', '.') : 'Ücretsiz' ?></span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600 mb-4">
                        <span>Vergi</span>
                        <span>Dahil</span>
                    </div>
                    <div class="border-t border-gray-200 pt-4 mt-4 flex justify-between text-lg font-medium text-black">
                        <span>Toplam</span>
                        <span id="cart-page-total">₺<?= number_format($total, 2, ',', '.') ?></span>
                    </div>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/odeme" class="w-full bg-black text-white py-4 mt-8 text-xs font-bold tracking-widest uppercase hover:bg-gray-800 transition block text-center">
                        Ödemeye geç
                    </a>
                    <p class="flex items-center justify-center gap-2 mt-4 text-[10px] text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        Güvenli Ödeme
                    </p>
                </div>
            </div>
        </div>

        <p class="mt-10 text-center">
            <a href="<?= htmlspecialchars($baseUrl) ?>/" class="text-sm text-gray-500 hover:text-primary transition">← Alışverişe devam et</a>
        </p>
    <?php endif; ?>
    </div>

<?php if (!empty($items)): ?>
<script>
(function() {
    var baseUrl = <?= json_encode($baseUrl) ?>;
    var updateUrl = (baseUrl || '') + '/sepet/guncelle';

    function formatMoney(n) {
        return '₺' + (typeof n === 'number' ? n.toFixed(2) : String(n)).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function updateSummary(data) {
        var el;
        if (data.subtotal != null && (el = document.getElementById('cart-page-subtotal'))) el.textContent = formatMoney(data.subtotal);
        if (data.shipping_cost != null && (el = document.getElementById('cart-page-shipping'))) el.textContent = data.shipping_cost > 0 ? formatMoney(data.shipping_cost) : 'Ücretsiz';
        if (data.total != null && (el = document.getElementById('cart-page-total'))) el.textContent = formatMoney(data.total);
        if (data.cart_count != null && (el = document.getElementById('cart-page-item-count'))) el.textContent = data.cart_count;
        if (typeof window.dispatchEvent === 'function') {
            window.dispatchEvent(new CustomEvent('cart-count-updated', { detail: { count: data.cart_count || 0 } }));
        }
    }

    function sendUpdate(cartKey, qty, row, onSuccess) {
        var fd = new FormData();
        fd.append('cart_key', cartKey);
        fd.append('quantity', String(qty));
        var req = new XMLHttpRequest();
        req.open('POST', updateUrl);
        req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        req.setRequestHeader('Accept', 'application/json');
        req.onload = function() {
            try {
                var data = JSON.parse(req.responseText);
                if (!data.success) return;
                if (data.removed && row) {
                    row.remove();
                    var list = document.getElementById('cart-page-rows');
                    if (list && list.querySelectorAll('.cart-page-row').length === 0) {
                        window.location.reload();
                        return;
                    }
                } else if (row) {
                    var qtyEl = row.querySelector('.cart-row-qty');
                    var plus = row.querySelector('.cart-qty-plus');
                    if (qtyEl) qtyEl.textContent = data.quantity;
                    var lineEl = row.querySelector('.cart-row-line-total');
                    if (lineEl) lineEl.textContent = formatMoney(data.line_total);
                    if (plus) plus.disabled = data.quantity >= parseInt(row.dataset.maxQty || 999, 10);
                }
                updateSummary(data);
                if (onSuccess) onSuccess(data);
            } catch (err) {}
        };
        req.send(fd);
    }

    window.addEventListener('cart-drawer-updated', function(e) {
        var d = e.detail || {};
        var el;
        var key = String(d.cart_key || '');
        var rows = document.querySelectorAll('.cart-page-row');
        var row = null;
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].dataset.cartKey === key) { row = rows[i]; break; }
        }
        if (!row) return;
        if (d.removed) {
            row.remove();
            var list = document.getElementById('cart-page-rows');
            if (list && list.querySelectorAll('.cart-page-row').length === 0) {
                window.location.reload();
                return;
            }
        } else {
            var qtyEl = row.querySelector('.cart-row-qty');
            var lineEl = row.querySelector('.cart-row-line-total');
            var plus = row.querySelector('.cart-qty-plus');
            if (qtyEl) qtyEl.textContent = d.quantity;
            if (lineEl) lineEl.textContent = formatMoney(d.line_total);
            if (plus) plus.disabled = d.quantity >= parseInt(row.dataset.maxQty || 999, 10);
        }
        if (d.subtotal != null && (el = document.getElementById('cart-page-subtotal'))) el.textContent = formatMoney(d.subtotal);
        if (d.shipping_cost != null && (el = document.getElementById('cart-page-shipping'))) el.textContent = d.shipping_cost > 0 ? formatMoney(d.shipping_cost) : 'Ücretsiz';
        if (d.total != null && (el = document.getElementById('cart-page-total'))) el.textContent = formatMoney(d.total);
        if (d.cart_count != null && (el = document.getElementById('cart-page-item-count'))) el.textContent = d.cart_count;
    });


    function removeItem(cartKey, row) {
        var removeUrl = (baseUrl || '') + '/sepet/sil';
        var fd = new FormData();
        fd.append('cart_key', cartKey);
        var req = new XMLHttpRequest();
        req.open('POST', removeUrl);
        req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        req.setRequestHeader('Accept', 'application/json');
        req.onload = function() {
            try {
                var data = JSON.parse(req.responseText);
                if (data.success) {
                    // Satırı kaldır
                    if (row) {
                        row.style.transition = 'opacity 0.3s ease-out';
                        row.style.opacity = '0';
                        setTimeout(function() {
                            row.remove();
                            var list = document.getElementById('cart-page-rows');
                            var remainingRows = list ? list.querySelectorAll('.cart-page-row').length : 0;
                            if (remainingRows === 0) {
                                // Sepet boş, boş sepet mesajını göster
                                var emptyCartHtml = '<div class="py-16 text-center"><p class="text-secondary mb-4">Sepetiniz boş.</p><a href="' + (baseUrl || '') + '/" class="inline-block text-xs font-bold uppercase tracking-widest text-primary underline hover:no-underline">Alışverişe başlayın</a></div>';
                                var cartContent = document.querySelector('.lg\\:col-span-8');
                                if (cartContent) {
                                    cartContent.innerHTML = emptyCartHtml;
                                }
                                // Sağ kolon özetini gizle
                                var summaryCol = document.querySelector('.lg\\:col-span-4');
                                if (summaryCol) {
                                    summaryCol.style.display = 'none';
                                }
                                return;
                            }
                        }, 300);
                    }
                    // Özeti güncelle
                    updateSummary(data);
                    // Toast göster
                    if (typeof window.dispatchEvent === 'function') {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || 'Ürün sepetten çıkarıldı.' } }));
                    }
                    // Sepet drawer'ı güncelle
                    if (typeof window.dispatchEvent === 'function') {
                        window.dispatchEvent(new CustomEvent('cart-updated'));
                    }
                } else {
                    if (typeof window.dispatchEvent === 'function') {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || 'Bir hata oluştu.' } }));
                    }
                }
            } catch (err) {
                if (typeof window.dispatchEvent === 'function') {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Bir hata oluştu.' } }));
                }
            }
        };
        req.onerror = function() {
            if (typeof window.dispatchEvent === 'function') {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Bir hata oluştu.' } }));
            }
        };
        req.send(fd);
    }

    document.getElementById('cart-page-rows').addEventListener('click', function(e) {
        var removeBtn = e.target.closest('.cart-remove-btn');
        if (removeBtn) {
            e.preventDefault();
            var cartKey = removeBtn.dataset.cartKey;
            var row = removeBtn.closest('.cart-page-row');
            if (cartKey && row) {
                removeItem(cartKey, row);
            }
            return;
        }
        
        var minus = e.target.closest('.cart-qty-minus');
        var plus = e.target.closest('.cart-qty-plus');
        if (!minus && !plus) return;
        var row = (minus || plus).closest('.cart-page-row');
        if (!row) return;
        var cartKey = row.dataset.cartKey;
        var maxQty = parseInt(row.dataset.maxQty || 999, 10);
        var qtyEl = row.querySelector('.cart-row-qty');
        var qty = parseInt(qtyEl.textContent || 0, 10);
        if (minus) {
            if (qty <= 1) {
                if (typeof window.cartRemoveConfirm === 'function') {
                    window.cartRemoveConfirm(function() {
                        sendUpdate(cartKey, 0, row);
                    });
                } else {
                    sendUpdate(cartKey, 0, row);
                }
                return;
            }
            qty = qty - 1;
        } else {
            qty = Math.min(maxQty, qty + 1);
        }
        sendUpdate(cartKey, qty, row);
    });
})();
</script>
<?php endif; ?>
