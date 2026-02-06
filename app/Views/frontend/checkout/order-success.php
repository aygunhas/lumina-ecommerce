<?php
$baseUrl = $baseUrl ?? '';
$orderNumber = $orderNumber ?? '';
$customerName = trim((string) ($customerName ?? '')) !== '' ? trim((string) $customerName) : 'Müşteri';
$guestEmail = trim((string) ($guestEmail ?? '')) !== '' ? trim((string) $guestEmail) : '';
$isGuest = (bool) ($isGuest ?? true);
$orderDisplayNumber = $orderNumber !== '' && $orderNumber !== null ? '#' . $orderNumber : '#TR-8842';
$emailDisplay = $guestEmail !== '' ? $guestEmail : 'e-posta adresinize';
?>
<div class="max-w-3xl mx-auto px-6 py-16 md:py-24 text-center" x-data="{ iconVisible: false }" x-init="$nextTick(() => { iconVisible = true })">
    <!-- 1. Başarı Alanı (Hero) -->
    <div>
        <div
            x-show="iconVisible"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 scale-50"
            x-transition:enter-end="opacity-100 scale-100"
            class="w-24 h-24 text-green-600 mx-auto mb-8"
        >
            <svg class="w-full h-full" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="font-display text-3xl md:text-4xl tracking-widest uppercase mb-4 text-primary">Siparişiniz alındı</h1>
        <p class="text-gray-500 text-sm mb-2">Teşekkürler, <?= htmlspecialchars($customerName) ?>. Siparişiniz (<?= htmlspecialchars($orderDisplayNumber) ?>) başarıyla oluşturuldu.</p>
        <p class="text-gray-400 text-xs">Sipariş onayı ve detaylar <?= htmlspecialchars($emailDisplay) ?> adresine gönderildi.</p>
    </div>

    <!-- 2. Sipariş Durumu (Stepper) -->
    <div class="mt-12 mb-12">
        <div class="flex items-start max-w-xl mx-auto">
            <?php
            $steps = [
                ['label' => 'Sipariş Onaylandı', 'active' => true],
                ['label' => 'Hazırlanıyor', 'active' => false],
                ['label' => 'Kargoya Verildi', 'active' => false],
                ['label' => 'Teslim Edildi', 'active' => false],
            ];
            $last = count($steps) - 1;
            ?>
            <?php foreach ($steps as $i => $step): ?>
                <div class="flex flex-col items-center flex-shrink-0">
                    <span class="<?= $step['active'] ? 'bg-primary' : 'bg-white border-2 border-gray-200' ?> w-4 h-4 rounded-full"></span>
                    <span class="mt-2 text-xs font-medium whitespace-nowrap <?= $step['active'] ? 'text-primary' : 'text-gray-400' ?>"><?= htmlspecialchars($step['label']) ?></span>
                </div>
                <?php if ($i < $last): ?>
                    <div class="flex-1 min-w-[20px] border-t border-gray-200 mt-2 mx-1"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 3. Misafir Dönüşüm Kartı (sadece giriş yapmamışsa) -->
    <?php if ($isGuest): ?>
    <div class="bg-gray-50 border border-gray-100 p-8 rounded-sm text-left max-w-xl mx-auto mb-12">
        <h2 class="font-bold text-sm uppercase tracking-wide mb-2 text-primary">Siparişini takip etmek ister misin?</h2>
        <p class="text-xs text-gray-500 mb-6">Hesap oluşturarak sipariş durumunu anlık takip edebilir ve sonraki alışverişlerini hızlandırabilirsin.</p>
        <form action="<?= htmlspecialchars($baseUrl) ?>/kayit" method="post" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="hidden" name="email" value="<?= htmlspecialchars($guestEmail) ?>">
            <input type="password" name="password" placeholder="Şifre belirleyin" class="md:col-span-2 w-full px-4 py-3 border border-gray-200 rounded-sm text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary" required minlength="6">
            <button type="submit" class="md:col-span-1 w-full bg-black text-white px-6 py-3 text-xs font-bold tracking-widest uppercase hover:bg-gray-800 transition rounded-sm">HESAP OLUŞTUR</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- 4. Aksiyon Butonları -->
    <div class="mt-12 flex flex-wrap justify-center gap-6 items-center">
        <?php if (!$isGuest): ?>
            <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim/siparisler" class="text-xs underline hover:text-gray-500 transition text-primary">Sipariş detayını görüntüle</a>
        <?php endif; ?>
        <a href="<?= htmlspecialchars($baseUrl) ?>/" class="bg-black text-white px-8 py-4 text-xs font-bold tracking-widest uppercase hover:bg-gray-800 transition rounded-sm inline-block">ALIŞVERİŞE DEVAM ET</a>
    </div>
</div>
