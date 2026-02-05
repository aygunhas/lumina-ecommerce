<?php
$baseUrl = $baseUrl ?? '';
$cartDrawerItems = [];
$cartDrawerSubtotal = 0.0;
if (class_exists(\App\Controllers\Frontend\CartController::class)) {
    [$cartDrawerItems, $cartDrawerSubtotal] = \App\Controllers\Frontend\CartController::getCartItems();
}
$hasItems = !empty($cartDrawerItems);
/**
 * Sepet çekmecesi – cart-open event'i ile açılır.
 * Miktar +/- AJAX ile güncellenir (sayfa yenilenmez).
 */
?>
<div x-data="{ open: false }" @cart-open.window="open = true" x-cloak class="fixed inset-0 z-50 pointer-events-none" aria-hidden="true">
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="pointer-events-auto fixed inset-0 bg-black/20"
         @click="open = false"
         aria-label="Çekmeceyi kapat"></div>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="pointer-events-auto fixed top-0 right-0 bottom-0 w-full max-w-md bg-white shadow-xl z-50 flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h2 class="text-lg font-display font-semibold tracking-tight text-primary">Sepetim</h2>
            <button type="button" @click="open = false" class="p-2 text-secondary hover:text-primary transition" aria-label="Çekmeceni kapat">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <div id="cart-drawer-empty" class="<?= $hasItems ? 'hidden' : '' ?>">
                <p class="text-sm text-secondary">Sepetiniz boş.</p>
                <a href="<?= htmlspecialchars($baseUrl) ?>/" class="inline-block mt-4 text-xs font-medium uppercase tracking-widest text-primary underline hover:no-underline">Alışverişe başla →</a>
            </div>
            <?php if ($hasItems): ?>
                <ul id="cart-drawer-list" class="space-y-4">
                    <?php foreach ($cartDrawerItems as $item): ?>
                        <?php
                        $imgSrc = !empty($item['image_path']) ? $baseUrl . '/' . $item['image_path'] : '';
                        $qty = (int) $item['quantity'];
                        $stock = (int) ($item['stock'] ?? 0);
                        $maxQty = $stock > 0 ? $stock : 999;
                        $price = (float) $item['price'];
                        $lineTotal = (float) $item['total'];
                        ?>
                        <li class="cart-drawer-row flex gap-4 border-b border-gray-100 pb-4 last:border-0"
                            data-cart-key="<?= htmlspecialchars($item['cart_key']) ?>"
                            data-price="<?= $price ?>"
                            data-quantity="<?= $qty ?>"
                            data-max-qty="<?= $maxQty ?>">
                            <a href="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($item['slug']) ?>" class="flex-shrink-0 w-20 h-24 bg-gray-100 rounded overflow-hidden">
                                <?php if ($imgSrc): ?>
                                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="w-full h-full flex items-center justify-center text-[10px] text-secondary">Görsel yok</span>
                                <?php endif; ?>
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($item['slug']) ?>" class="text-sm font-medium text-primary hover:underline block truncate"><?= htmlspecialchars($item['name']) ?></a>
                                <?php if (!empty($item['attributes_summary'])): ?>
                                    <span class="text-xs text-secondary block mt-0.5"><?= htmlspecialchars($item['attributes_summary']) ?></span>
                                <?php endif; ?>
                                <div class="flex items-center gap-2 mt-2">
                                    <button type="button" class="cart-drawer-minus w-7 h-7 flex items-center justify-center border border-gray-200 text-primary hover:border-black text-sm disabled:opacity-40 disabled:cursor-not-allowed" <?= $qty <= 1 ? 'disabled' : '' ?> aria-label="Azalt">−</button>
                                    <span class="cart-drawer-qty text-xs font-medium w-6 text-center" aria-live="polite"><?= $qty ?></span>
                                    <button type="button" class="cart-drawer-plus w-7 h-7 flex items-center justify-center border border-gray-200 text-primary hover:border-black text-sm disabled:opacity-40 disabled:cursor-not-allowed" <?= $qty >= $maxQty ? 'disabled' : '' ?> aria-label="Artır">+</button>
                                </div>
                                <p class="text-xs text-secondary mt-1"><?= number_format($price, 2, ',', '.') ?> ₺</p>
                            </div>
                            <div class="cart-drawer-line-total flex-shrink-0 text-sm font-medium text-primary text-right"><?= number_format($lineTotal, 2, ',', '.') ?> ₺</div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div id="cart-drawer-footer" class="mt-6 pt-4 border-t border-gray-100">
                    <p class="flex justify-between text-sm text-secondary mb-4">
                        <span>Ara toplam</span>
                        <span id="cart-drawer-subtotal" class="font-medium text-primary"><?= number_format($cartDrawerSubtotal, 2, ',', '.') ?> ₺</span>
                    </p>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/sepet" class="block w-full py-3 text-center text-xs font-bold uppercase tracking-widest bg-black text-white hover:bg-gray-800 transition">Sepete git</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
(function(){
    var baseUrl = <?= json_encode($baseUrl) ?>;
    var updateUrl = (baseUrl || '') + '/sepet/guncelle';

    function formatMoney(n) {
        return (typeof n === 'number' ? n.toFixed(2) : n).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' ₺';
    }

    function updateRow(row, data) {
        row.querySelector('.cart-drawer-qty').textContent = data.quantity;
        row.querySelector('.cart-drawer-line-total').textContent = formatMoney(data.line_total);
        row.dataset.quantity = data.quantity;
        var minus = row.querySelector('.cart-drawer-minus');
        var plus = row.querySelector('.cart-drawer-plus');
        if (minus) minus.disabled = data.quantity <= 1;
        if (plus) plus.disabled = data.quantity >= parseInt(row.dataset.maxQty || 999, 10);
    }

    function setEmptyState() {
        var list = document.getElementById('cart-drawer-list');
        var footer = document.getElementById('cart-drawer-footer');
        var empty = document.getElementById('cart-drawer-empty');
        if (list) list.classList.add('hidden');
        if (footer) footer.classList.add('hidden');
        if (empty) empty.classList.remove('hidden');
    }

    document.addEventListener('click', function(e) {
        var minus = e.target.closest('.cart-drawer-minus');
        var plus = e.target.closest('.cart-drawer-plus');
        if (!minus && !plus) return;
        var row = (minus || plus).closest('.cart-drawer-row');
        if (!row) return;
        var key = row.dataset.cartKey;
        var qty = parseInt(row.dataset.quantity || 0, 10);
        if (minus) qty = Math.max(0, qty - 1);
        else qty = qty + 1;
        var fd = new FormData();
        fd.append('cart_key', key);
        fd.append('quantity', String(qty));
        var req = new XMLHttpRequest();
        req.open('POST', updateUrl);
        req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        req.setRequestHeader('Accept', 'application/json');
        req.onload = function() {
            try {
                var data = JSON.parse(req.responseText);
                if (!data.success) return;
                if (data.removed) {
                    row.remove();
                    var list = document.getElementById('cart-drawer-list');
                    if (list && list.querySelectorAll('.cart-drawer-row').length === 0) setEmptyState();
                } else {
                    updateRow(row, data);
                }
                var subtotalEl = document.getElementById('cart-drawer-subtotal');
                if (subtotalEl) subtotalEl.textContent = formatMoney(data.subtotal);
                if (typeof window.dispatchEvent === 'function') {
                    window.dispatchEvent(new CustomEvent('cart-count-updated', { detail: { count: data.cart_count } }));
                }
            } catch (err) {}
        };
        req.send(fd);
    });
})();
</script>
