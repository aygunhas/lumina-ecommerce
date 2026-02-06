<?php
$baseUrl = $baseUrl ?? '';
$order = $order ?? null;
$items = $items ?? [];
$shipments = $shipments ?? [];
$q = $q ?? '';
$notFound = $notFound ?? false;

$trMonths = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];

// Veritabanı status -> stepper index (0: Sipariş Alındı, 1: Hazırlanıyor, 2: Kargoda, 3: Teslim Edildi)
$statusToIdx = ['pending' => 0, 'confirmed' => 0, 'processing' => 1, 'shipped' => 2, 'delivered' => 3];
$currentIdx = 0;
if ($order) {
    $status = $order['status'] ?? 'pending';
    $currentIdx = $statusToIdx[$status] ?? 0;
}
?>
<div class="pt-20 pb-12 text-center">
    <h1 class="font-display text-3xl tracking-widest text-primary">Sipariş Takibi</h1>
    <p class="text-sm text-gray-500 mt-3 max-w-md mx-auto">Sipariş numaranızı veya siparişte kullandığınız e-posta adresini girin.</p>

    <!-- Sorgulama Formu (GET ile sunucuya gider) -->
    <div class="max-w-md mx-auto mt-10 px-6">
        <form action="<?= htmlspecialchars($baseUrl) ?>/siparis-takip" method="get" class="text-left space-y-4">
            <div>
                <label for="track_query" class="block text-xs font-medium text-gray-600 mb-2">Sipariş no veya e-posta</label>
                <input type="text" id="track_query" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Örn: LB-20250206-1234 veya ornek@email.com" class="w-full border border-gray-200 rounded-sm px-4 py-3 text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
            </div>
            <button type="submit" class="w-full bg-black text-white py-4 mt-6 text-xs font-bold tracking-widest uppercase hover:bg-gray-800 transition rounded-sm">
                Sorgula
            </button>
        </form>
    </div>

    <?php if ($notFound): ?>
        <p class="mt-8 text-sm text-red-600 max-w-md mx-auto">Sipariş bulunamadı. Lütfen sipariş numaranızı veya e-posta adresinizi kontrol edin.</p>
    <?php endif; ?>

    <!-- Sipariş sonucu (sadece sipariş bulunduysa) -->
    <?php if ($order): ?>
    <div class="max-w-3xl mx-auto px-6 mt-10">
        <div class="border border-gray-200 p-8 rounded-sm text-left">
            <?php
            $o = $order;
            $createdTs = strtotime($o['created_at']);
            $orderDateStr = $createdTs ? (date('j', $createdTs) . ' ' . $trMonths[date('n', $createdTs) - 1] . ' ' . date('Y', $createdTs)) : '';
            ?>
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-8">
                <h2 class="text-xl font-display text-primary">Sipariş #<?= htmlspecialchars($o['order_number']) ?></h2>
                <p class="text-sm text-gray-500">Sipariş Tarihi: <?= htmlspecialchars($orderDateStr) ?></p>
            </div>

            <!-- İlerleme Çubuğu (Stepper) - yalnızca aktif adım koyu, diğerleri boş daire + gri yazı, tüm genişlik -->
            <div class="flex items-start w-full mb-8">
                <?php
                $stepLabels = ['Sipariş Alındı', 'Hazırlanıyor', 'Kargoda', 'Teslim Edildi'];
                $lastIdx = count($stepLabels) - 1;
                foreach ($stepLabels as $i => $label):
                    $isActive = $i === $currentIdx;
                    $isLast = $i === $lastIdx;
                ?>
                    <div class="flex flex-col items-center flex-shrink-0">
                        <span class="<?= $isActive ? 'bg-primary' : 'bg-white border-2 border-gray-300' ?> w-4 h-4 rounded-full flex-shrink-0" aria-hidden="true"></span>
                        <span class="mt-2 text-xs font-medium text-center whitespace-nowrap <?= $isActive ? 'text-primary font-semibold' : 'text-gray-400' ?>"><?= htmlspecialchars($label) ?></span>
                    </div>
                    <?php if (!$isLast): ?>
                        <div class="flex-1 min-w-[16px] border-t-2 mt-2 mx-1 self-start flex-shrink-0 border-gray-200" aria-hidden="true"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php if (in_array($o['status'] ?? '', ['cancelled', 'refunded'], true)): ?>
                <p class="text-sm text-amber-700 mb-6">Bu sipariş <?= $o['status'] === 'cancelled' ? 'iptal edilmiş' : 'iade edilmiş' ?> olarak işaretlenmiştir.</p>
            <?php endif; ?>

            <!-- Ürün Listesi -->
            <p class="text-xs tracking-widest text-gray-400 uppercase mb-4">Ürün Özeti</p>
            <ul class="border border-gray-200 divide-y divide-gray-100 mb-8">
                <?php foreach ($items as $item): ?>
                <li class="flex gap-4 py-4 px-4">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['product_name']) ?></p>
                        <p class="text-xs text-gray-500"><?= (int) $item['quantity'] ?> adet × <?= number_format((float) $item['price'], 2, ',', '.') ?> ₺</p>
                    </div>
                    <p class="text-sm font-medium text-gray-900"><?= number_format((float) $item['total'], 2, ',', '.') ?> ₺</p>
                </li>
                <?php endforeach; ?>
            </ul>

            <!-- Kargo Bilgisi -->
            <?php if (!empty($shipments)): ?>
            <div class="bg-gray-50 border border-gray-100 p-4 rounded-sm mb-8">
                <p class="text-xs tracking-widest text-gray-400 uppercase mb-2">Kargo Bilgisi</p>
                <?php foreach ($shipments as $s): ?>
                <p class="text-sm text-gray-700">Kargo Firması: <?= htmlspecialchars($s['carrier'] ?? 'Kargo') ?></p>
                <?php if (!empty($s['tracking_number'])): ?>
                <p class="text-sm text-gray-700 mt-1">
                    Takip No:
                    <a href="https://www.yurticikargo.com/tr/online-servisler/gonderi-sorgula?code=<?= urlencode($s['tracking_number']) ?>" target="_blank" rel="noopener noreferrer" class="underline hover:text-gray-500 text-primary"><?= htmlspecialchars($s['tracking_number']) ?></a>
                </p>
                <?php else: ?>
                <p class="text-sm text-gray-700 mt-1">Takip No: —</p>
                <?php endif; ?>
                <?php if (!empty($s['shipped_at'])): ?>
                <p class="text-xs text-gray-500 mt-1">Kargoya veriliş: <?= date('d.m.Y', strtotime($s['shipped_at'])) ?></p>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="bg-gray-50 border border-gray-100 p-4 rounded-sm mb-8">
                <p class="text-xs tracking-widest text-gray-400 uppercase mb-2">Kargo Bilgisi</p>
                <p class="text-sm text-gray-500">Henüz kargo bilgisi eklenmemiş.</p>
            </div>
            <?php endif; ?>

            <!-- Yeni Sorgu -->
            <p class="text-center">
                <a href="<?= htmlspecialchars($baseUrl) ?>/siparis-takip" class="text-xs underline cursor-pointer text-primary hover:text-gray-500 transition">
                    Farklı bir sipariş sorgula
                </a>
            </p>
        </div>
    </div>
    <?php endif; ?>
</div>
