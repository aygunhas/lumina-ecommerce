<?php
$activeTab = $_GET['tab'] ?? 'overview';
$baseUrl = $baseUrl ?? '';
$userName = $userName ?? 'Üye';
$userEmail = $userEmail ?? '';
$lastOrder = $lastOrder ?? null;
$defaultAddress = $defaultAddress ?? null;
$statusLabels = $statusLabels ?? [];
$orders = $orders ?? [];
$orderItemsCount = $orderItemsCount ?? [];
$order = $order ?? null;
$items = $items ?? [];
$shipments = $shipments ?? [];
$addresses = $addresses ?? [];
$user = $user ?? null;
$products = $products ?? [];
$productImages = $productImages ?? [];
$errors = $errors ?? [];
$old = $old ?? [];

$initials = 'Ü';
if (trim($userName) !== '') {
    $parts = preg_split('/\s+/u', trim($userName), 2);
    $initials = mb_strtoupper(mb_substr($parts[0], 0, 1));
    if (isset($parts[1]) && $parts[1] !== '') {
        $initials .= mb_strtoupper(mb_substr($parts[1], 0, 1));
    } elseif (mb_strlen($parts[0]) > 1) {
        $initials .= mb_strtoupper(mb_substr($parts[0], 1, 1));
    }
}

$lastOrderStatus = $lastOrder && isset($statusLabels[$lastOrder['status'] ?? '']) ? $statusLabels[$lastOrder['status']] : null;
$addressLine = $defaultAddress ? trim(($defaultAddress['address_line'] ?? '') . ', ' . ($defaultAddress['district'] ?? '') . ' ' . ($defaultAddress['city'] ?? '')) : '';

$hesabimBase = $baseUrl . '/hesabim';
$navTabs = [
    'overview' => 'Genel Bakış',
    'orders' => 'Siparişlerim',
    'addresses' => 'Adreslerim',
    'details' => 'Hesap Detayları',
    'favorites' => 'Favorilerim',
];

function orderStatusClass($status) {
    if ($status === 'delivered') return 'text-green-600';
    if ($status === 'shipped') return 'text-green-600';
    if (in_array($status, ['processing', 'confirmed'], true)) return 'text-amber-600';
    return 'text-gray-500';
}
$trMonths = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
?>
<div class="container mx-auto px-6 py-12">
    <div class="flex flex-col gap-8 lg:grid lg:grid-cols-4 lg:gap-12">
        <!-- Sol Kolon: Navigasyon -->
        <aside class="lg:col-span-1">
            <div class="flex flex-col items-center lg:items-start">
                <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center text-xl font-display font-medium text-gray-600 flex-shrink-0">
                    <?= htmlspecialchars($initials) ?>
                </div>
                <p class="font-display font-medium mt-3 text-gray-900"><?= htmlspecialchars($userName) ?></p>
                <p class="text-xs text-gray-400"><?= htmlspecialchars($userEmail) ?></p>
            </div>

            <nav class="hidden lg:block space-y-1 mt-8">
                <?php foreach ($navTabs as $tab => $label): ?>
                    <?php
                    $isActive = ($activeTab === $tab) || ($activeTab === 'order-detail' && $tab === 'orders');
                    $href = $tab === 'overview' ? $hesabimBase : $hesabimBase . '?tab=' . $tab;
                    ?>
                    <a href="<?= htmlspecialchars($href) ?>" class="block px-4 py-3 text-sm rounded-md transition <?= $isActive ? 'bg-black text-white hover:bg-black' : 'text-gray-600 hover:bg-gray-50 hover:text-black' ?>">
                        <?= htmlspecialchars($label) ?>
                    </a>
                <?php endforeach; ?>
                <a href="<?= htmlspecialchars($baseUrl) ?>/cikis" class="block px-4 py-3 text-sm text-gray-500 hover:text-red-600 transition rounded-md mt-8 pt-8 border-t border-gray-100">
                    Güvenli Çıkış
                </a>
            </nav>
        </aside>

        <!-- Mobil: Yatay kaydırmalı menü -->
        <nav class="flex overflow-x-auto gap-4 pb-4 border-b border-gray-100 lg:hidden scrollbar-hide" aria-label="Hesap menüsü">
            <?php foreach ($navTabs as $tab => $label): ?>
                <?php
                $isActive = ($activeTab === $tab) || ($activeTab === 'order-detail' && $tab === 'orders');
                $href = $tab === 'overview' ? $hesabimBase : $hesabimBase . '?tab=' . $tab;
                ?>
                <a href="<?= htmlspecialchars($href) ?>" class="flex-shrink-0 px-4 py-2.5 text-sm rounded-md transition whitespace-nowrap <?= $isActive ? 'bg-black text-white hover:bg-black' : 'text-gray-600 hover:bg-gray-50 hover:text-black' ?>">
                    <?= htmlspecialchars($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Sağ Kolon: İçerik -->
        <div class="lg:col-span-3">
            <?php switch ($activeTab): case 'overview': ?>
                <h1 class="text-2xl font-display mb-2 text-gray-900">Merhaba, <?= htmlspecialchars(explode(' ', $userName)[0] ?? $userName) ?>.</h1>
                <p class="text-sm text-gray-500 mb-8">Hesap panonuzdan son siparişlerinizi görüntüleyebilir, kargo ve fatura adreslerinizi yönetebilirsiniz.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <a href="<?= $lastOrder ? $hesabimBase . '?tab=order-detail&id=' . (int)$lastOrder['id'] : $hesabimBase . '?tab=orders' ?>" class="border border-gray-200 p-6 rounded-sm hover:shadow-sm transition group cursor-pointer block">
                        <p class="text-xs tracking-widest text-gray-400 uppercase mb-4">Siparişler</p>
                        <?php if ($lastOrder && $lastOrderStatus): ?>
                            <p class="text-sm text-gray-700 leading-relaxed">#<?= htmlspecialchars($lastOrder['order_number'] ?? '') ?> nolu siparişiniz <?= htmlspecialchars($lastOrderStatus) ?>.</p>
                            <span class="text-xs font-bold mt-4 block group-hover:underline">Siparişi Takip Et →</span>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 leading-relaxed">Henüz siparişiniz bulunmuyor.</p>
                            <span class="text-xs font-bold mt-4 block group-hover:underline">Siparişlerim →</span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= htmlspecialchars($hesabimBase) ?>?tab=addresses" class="border border-gray-200 p-6 rounded-sm hover:shadow-sm transition group cursor-pointer block">
                        <p class="text-xs tracking-widest text-gray-400 uppercase mb-4">Teslimat Adresi</p>
                        <?php if ($addressLine !== ''): ?>
                            <p class="text-sm text-gray-600 leading-relaxed line-clamp-2"><?= htmlspecialchars($addressLine) ?></p>
                            <span class="text-xs font-bold mt-4 block group-hover:underline">Düzenle →</span>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 leading-relaxed">Kayıtlı adres bulunmuyor.</p>
                            <span class="text-xs font-bold mt-4 block group-hover:underline">Adres Ekle →</span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= htmlspecialchars($hesabimBase) ?>?tab=details" class="border border-gray-200 p-6 rounded-sm hover:shadow-sm transition group cursor-pointer block">
                        <p class="text-xs tracking-widest text-gray-400 uppercase mb-4">Hesap Bilgileri</p>
                        <p class="text-sm text-gray-600 leading-relaxed"><?= htmlspecialchars($userEmail) ?> — şifreniz ve iletişim tercihleriniz.</p>
                        <span class="text-xs font-bold mt-4 block group-hover:underline">Bilgileri Güncelle →</span>
                    </a>
                </div>
                <?php break; ?>

            <?php case 'orders': ?>
                <h2 class="text-xs tracking-widest text-gray-400 uppercase mb-6">Sipariş Geçmişi</h2>
                <?php if (empty($orders)): ?>
                    <p class="text-sm text-gray-500">Henüz siparişiniz yok. <a href="<?= htmlspecialchars($baseUrl) ?>/" class="underline hover:text-black">Alışverişe başlayın</a>.</p>
                <?php else: ?>
                    <div class="space-y-0">
                        <?php foreach ($orders as $o): ?>
                            <?php
                            $status = $o['status'] ?? '';
                            $statusLabel = $statusLabels[$status] ?? $status;
                            $itemCount = $orderItemsCount[(int)$o['id']] ?? 0;
                            ?>
                            <div class="border-b border-gray-100 py-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                                <div>
                                    <p class="font-medium text-gray-900">#<?= htmlspecialchars($o['order_number']) ?></p>
                                    <p class="text-xs text-gray-500 mt-0.5"><?php $t = strtotime($o['created_at']); echo (int)date('j', $t) . ' ' . $trMonths[(int)date('n', $t) - 1] . ' ' . date('Y', $t); ?></p>
                                    <p class="text-xs mt-1 <?= orderStatusClass($status) ?>">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full <?= $status === 'delivered' || $status === 'shipped' ? 'bg-green-500' : ($status === 'processing' || $status === 'confirmed' ? 'bg-amber-500' : 'bg-gray-400') ?> mr-1.5"></span>
                                        <?= htmlspecialchars($statusLabel) ?>
                                    </p>
                                </div>
                                <div class="text-sm text-gray-600"><?= $itemCount ?> Ürün</div>
                                <div class="font-medium text-gray-900"><?= number_format((float)$o['total'], 2, ',', '.') ?> ₺</div>
                                <a href="<?= htmlspecialchars($hesabimBase) ?>?tab=order-detail&id=<?= (int)$o['id'] ?>" class="border border-gray-200 px-4 py-2 text-xs uppercase tracking-wider hover:bg-black hover:text-white transition rounded-md inline-block flex-shrink-0">
                                    Detayları Gör
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php break; ?>

            <?php case 'order-detail':
                if (!$order) {
                    echo '<p class="text-sm text-gray-500">Sipariş bulunamadı. <a href="' . htmlspecialchars($hesabimBase) . '?tab=orders" class="underline">Siparişlere dön</a>.</p>';
                    break;
                }
                $o = $order;
                $statusToIdx = ['pending' => 0, 'confirmed' => 0, 'processing' => 1, 'shipped' => 2, 'delivered' => 3];
                $currentIdx = $statusToIdx[$o['status'] ?? 'pending'] ?? 0;
                $stepLabels = ['Sipariş Alındı', 'Hazırlanıyor', 'Kargoda', 'Teslim Edildi'];
                ?>
                <a href="<?= htmlspecialchars($hesabimBase) ?>?tab=orders" class="text-xs text-gray-500 hover:text-black mb-4 inline-block">← Siparişlere Dön</a>
                <h2 class="text-xl font-display mb-6">#<?= htmlspecialchars($o['order_number']) ?> Detayı</h2>

                <!-- Stepper: yalnızca aktif adım koyu, diğerleri boş daire + gri yazı, tüm genişlik -->
                <div class="flex items-start w-full mb-8">
                    <?php foreach ($stepLabels as $i => $label): ?>
                        <?php $isActive = $i === $currentIdx; $isLast = $i === count($stepLabels) - 1; ?>
                        <div class="flex flex-col items-center flex-shrink-0">
                            <span class="<?= $isActive ? 'bg-primary' : 'bg-white border-2 border-gray-300' ?> w-4 h-4 rounded-full flex-shrink-0" aria-hidden="true"></span>
                            <span class="mt-2 text-xs font-medium text-center whitespace-nowrap <?= $isActive ? 'text-primary font-semibold' : 'text-gray-400' ?>"><?= htmlspecialchars($label) ?></span>
                        </div>
                        <?php if (!$isLast): ?>
                            <div class="flex-1 min-w-[16px] border-t-2 mt-2 mx-1 self-start flex-shrink-0 border-gray-200" aria-hidden="true"></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <ul class="border border-gray-200 divide-y divide-gray-100 mb-8">
                    <?php foreach ($items as $item): ?>
                        <li class="flex gap-4 py-4 px-4">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['product_name']) ?></p>
                                <p class="text-xs text-gray-500"><?= (int)$item['quantity'] ?> adet × <?= number_format((float)$item['price'], 2, ',', '.') ?> ₺</p>
                            </div>
                            <p class="text-sm font-medium text-gray-900"><?= number_format((float)$item['total'], 2, ',', '.') ?> ₺</p>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <p class="text-xs tracking-widest text-gray-400 uppercase mb-3">Teslimat Adresi</p>
                        <p class="text-sm text-gray-700"><?= htmlspecialchars(trim(($o['shipping_first_name'] ?? '') . ' ' . ($o['shipping_last_name'] ?? ''))) ?></p>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($o['shipping_address_line'] ?? '') ?></p>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars(($o['shipping_district'] ?? '') . ' / ' . ($o['shipping_city'] ?? '')) ?><?= !empty($o['shipping_postal_code']) ? ' ' . htmlspecialchars($o['shipping_postal_code']) : '' ?></p>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($o['shipping_phone'] ?? '') ?></p>
                    </div>
                    <div>
                        <p class="text-xs tracking-widest text-gray-400 uppercase mb-3">Ödeme Özeti</p>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between text-gray-600"><span>Ara toplam</span><span><?= number_format((float)$o['subtotal'], 2, ',', '.') ?> ₺</span></div>
                            <div class="flex justify-between text-gray-600"><span>Kargo</span><span><?= number_format((float)$o['shipping_cost'], 2, ',', '.') ?> ₺</span></div>
                            <div class="flex justify-between font-medium text-gray-900 pt-2 border-t border-gray-100"><span>Toplam</span><span><?= number_format((float)$o['total'], 2, ',', '.') ?> ₺</span></div>
                        </div>
                    </div>
                </div>
                <?php break; ?>

            <?php case 'addresses':
                $addressOld = $old;
                $openAddressModal = ($activeTab === 'addresses' && !empty($errors));
                $addressesJson = json_encode(array_map(function($a) {
                    return [
                        'id' => (int)$a['id'],
                        'title' => $a['title'] ?? '',
                        'first_name' => $a['first_name'] ?? '',
                        'last_name' => $a['last_name'] ?? '',
                        'phone' => $a['phone'] ?? '',
                        'city' => $a['city'] ?? '',
                        'district' => $a['district'] ?? '',
                        'address_line' => $a['address_line'] ?? '',
                        'postal_code' => $a['postal_code'] ?? '',
                        'is_default' => (int)($a['is_default'] ?? 0),
                    ];
                }, $addresses));
                ?>
                <script>
                document.addEventListener('alpine:init', function() {
                    Alpine.data('addressAccount', function() {
                        return {
                            showAddressModal: <?= $openAddressModal ? 'true' : 'false' ?>,
                            showEditModal: false,
                            showDeleteConfirm: false,
                            editAddressId: null,
                            editForm: {},
                            deleteAddressId: null,
                            addressesData: <?= $addressesJson ?>,
                            baseUrl: <?= json_encode($baseUrl) ?>,
                            openEdit: function(id) {
                                var ad = this.addressesData.find(function(a) { return a.id === id; });
                                if (ad) {
                                    this.editForm = JSON.parse(JSON.stringify(ad));
                                    this.editAddressId = id;
                                    this.showEditModal = true;
                                }
                            },
                            closeEditModal: function() {
                                this.showEditModal = false;
                                this.editAddressId = null;
                                this.editForm = {};
                            },
                            openDelete: function(id) {
                                this.deleteAddressId = id;
                                this.showDeleteConfirm = true;
                            }
                        };
                    });
                });
                </script>
                <div x-data="addressAccount()">
                    <h2 class="text-xs tracking-widest text-gray-400 uppercase mb-6">Kayıtlı Adresler</h2>
                    <?php if (!empty($_GET['added'])): ?><p class="mb-4 text-sm text-green-600">Adres eklendi.</p><?php endif; ?>
                    <?php if (!empty($_GET['updated'])): ?><p class="mb-4 text-sm text-green-600">Adres güncellendi.</p><?php endif; ?>
                    <?php if (!empty($_GET['deleted'])): ?><p class="mb-4 text-sm text-green-600">Adres silindi.</p><?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <ul class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md text-sm text-red-700 list-none space-y-1">
                            <?php foreach (is_array($errors) ? $errors : [$errors] as $e): ?>
                                <li><?= htmlspecialchars(is_string($e) ? $e : implode(' ', (array)$e)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <button type="button" @click="showAddressModal = true" class="border-2 border-dashed border-gray-200 flex flex-col items-center justify-center h-48 cursor-pointer hover:border-black transition group rounded-sm w-full text-left bg-transparent">
                            <span class="text-3xl text-gray-300 group-hover:text-black transition">+</span>
                            <span class="text-sm text-gray-500 mt-2 group-hover:text-black transition">Yeni Adres Ekle</span>
                        </button>
                        <?php foreach ($addresses as $a): ?>
                            <div class="border border-gray-200 p-6 relative rounded-sm">
                                <?php if (!empty($a['title'])): ?>
                                    <span class="text-[10px] tracking-widest text-gray-400 uppercase"><?= htmlspecialchars($a['title']) ?></span>
                                    <?php if ((int)($a['is_default']) === 1): ?><span class="text-[10px] ml-2 text-gray-500">Varsayılan</span><?php endif; ?>
                                <?php endif; ?>
                                <p class="text-sm text-gray-700 mt-2"><?= htmlspecialchars(trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''))) ?></p>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($a['address_line'] ?? '') ?></p>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars(($a['district'] ?? '') . ' / ' . ($a['city'] ?? '')) ?><?= !empty($a['postal_code']) ? ' ' . htmlspecialchars($a['postal_code']) : '' ?></p>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($a['phone'] ?? '') ?></p>
                                <p class="mt-4 pt-4 border-t border-gray-100 text-xs">
                                    <button type="button" @click="openEdit(<?= (int)$a['id'] ?>)" class="underline text-gray-600 hover:text-black bg-none border-none cursor-pointer p-0">Düzenle</button>
                                    <span class="text-gray-300 mx-2">|</span>
                                    <button type="button" @click="openDelete(<?= (int)$a['id'] ?>)" class="underline text-gray-600 hover:text-red-600 bg-none border-none cursor-pointer p-0">Sil</button>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Modal: Yeni Adres Ekle -->
                    <div x-show="showAddressModal" x-cloak class="fixed inset-0 z-[60] bg-black/40 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto" x-transition.opacity>
                        <div @click.away="showAddressModal = false" class="bg-white w-full max-w-lg max-h-[calc(100vh-2rem)] shadow-2xl relative rounded-sm my-auto flex flex-col" x-show="showAddressModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4">
                            <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/hesabim/adresler/ekle" class="flex flex-col min-h-0 flex-1">
                                <div class="flex justify-between items-center p-6 border-b border-gray-100 flex-shrink-0">
                                    <h3 class="font-display tracking-wide text-lg">Yeni Adres Ekle</h3>
                                    <button type="button" @click="showAddressModal = false" class="text-gray-400 hover:text-black p-1" aria-label="Kapat">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                                <div class="p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
                                    <div>
                                        <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Adres Başlığı</label>
                                        <input type="text" name="title" placeholder="Örn: Ev, İş" value="<?= htmlspecialchars($addressOld['title'] ?? '') ?>" class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 transition">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Ad</label>
                                            <input type="text" name="first_name" required value="<?= htmlspecialchars($addressOld['first_name'] ?? '') ?>" class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 transition">
                                        </div>
                                        <div>
                                            <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Soyad</label>
                                            <input type="text" name="last_name" required value="<?= htmlspecialchars($addressOld['last_name'] ?? '') ?>" class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 transition">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Telefon</label>
                                        <input type="tel" name="phone" required value="<?= htmlspecialchars($addressOld['phone'] ?? '') ?>" class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 transition">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">İl</label>
                                            <input type="text" name="city" required value="<?= htmlspecialchars($addressOld['city'] ?? '') ?>" placeholder="İstanbul" class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 transition bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">İlçe</label>
                                            <input type="text" name="district" required value="<?= htmlspecialchars($addressOld['district'] ?? '') ?>" placeholder="İlçe" class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 transition bg-white">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Adres</label>
                                        <textarea name="address_line" rows="3" required class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 resize-none transition"><?= htmlspecialchars($addressOld['address_line'] ?? '') ?></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Posta Kodu</label>
                                        <input type="text" name="postal_code" value="<?= htmlspecialchars($addressOld['postal_code'] ?? '') ?>" class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 transition">
                                    </div>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="is_default" value="1" <?= !empty($addressOld['is_default']) ? 'checked' : '' ?> class="w-4 h-4 border-gray-300 rounded text-black focus:ring-black">
                                        <span class="text-sm text-gray-600">Varsayılan adres olarak kaydet</span>
                                    </label>
                                </div>
                                <div class="p-6 bg-gray-50 flex justify-end gap-4 rounded-b-sm flex-shrink-0">
                                    <button type="button" @click="showAddressModal = false" class="text-xs font-bold tracking-widest text-gray-500 hover:text-black px-4 py-3 uppercase">Vazgeç</button>
                                    <button type="submit" class="bg-black text-white text-xs font-bold tracking-widest px-8 py-3 uppercase hover:bg-gray-800 transition">Kaydet</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Modal: Adres Düzenle (editForm her zaman obje; null okuma hatası olmaz) -->
                    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-[60] bg-black/40 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto" x-transition.opacity>
                        <div @click.away="closeEditModal()" class="bg-white w-full max-w-lg max-h-[calc(100vh-2rem)] shadow-2xl relative rounded-sm my-auto flex flex-col" x-show="showEditModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4">
                            <template x-if="editAddressId !== null">
                                <form :action="baseUrl + '/hesabim/adresler/duzenle?id=' + editAddressId" method="post" class="flex flex-col min-h-0 flex-1">
                                    <div class="flex justify-between items-center p-6 border-b border-gray-100 flex-shrink-0">
                                        <h3 class="font-display tracking-wide text-lg">Adres Düzenle</h3>
                                        <button type="button" @click="closeEditModal()" class="text-gray-400 hover:text-black p-1" aria-label="Kapat">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <div class="p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
                                        <div>
                                            <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Adres Başlığı</label>
                                            <input type="text" name="title" x-model="editForm.title" placeholder="Örn: Ev, İş" class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 transition">
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Ad</label>
                                                <input type="text" name="first_name" x-model="editForm.first_name" required class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 transition">
                                            </div>
                                            <div>
                                                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Soyad</label>
                                                <input type="text" name="last_name" x-model="editForm.last_name" required class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 transition">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Telefon</label>
                                            <input type="tel" name="phone" x-model="editForm.phone" required class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 transition">
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">İl</label>
                                                <input type="text" name="city" x-model="editForm.city" required placeholder="İstanbul" class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 transition bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">İlçe</label>
                                                <input type="text" name="district" x-model="editForm.district" required placeholder="İlçe" class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 transition bg-white">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Adres</label>
                                            <textarea name="address_line" x-model="editForm.address_line" rows="3" required class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 resize-none transition"></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Posta Kodu</label>
                                            <input type="text" name="postal_code" x-model="editForm.postal_code" class="w-full border border-gray-200 focus:border-black focus:ring-black rounded-sm text-sm py-3 px-4 transition">
                                        </div>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="is_default" value="1" :checked="editForm.is_default === 1" class="w-4 h-4 border-gray-300 rounded text-black focus:ring-black">
                                            <span class="text-sm text-gray-600">Varsayılan adres olarak kaydet</span>
                                        </label>
                                    </div>
                                    <div class="p-6 bg-gray-50 flex justify-end gap-4 rounded-b-sm flex-shrink-0">
                                        <button type="button" @click="closeEditModal()" class="text-xs font-bold tracking-widest text-gray-500 hover:text-black px-4 py-3 uppercase">Vazgeç</button>
                                        <button type="submit" class="bg-black text-white text-xs font-bold tracking-widest px-8 py-3 uppercase hover:bg-gray-800 transition">Güncelle</button>
                                    </div>
                                </form>
                            </template>
                        </div>
                    </div>

                    <!-- Silme onayı (sepetteki alert tasarımı ile aynı) -->
                    <div x-show="showDeleteConfirm" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/30" aria-hidden="false" role="dialog" aria-labelledby="address-delete-confirm-title" x-transition.opacity>
                        <div class="bg-white rounded-sm shadow-xl max-w-sm w-full p-8 border border-gray-100" @click.away="showDeleteConfirm = false; deleteAddressId = null">
                            <p id="address-delete-confirm-title" class="font-display text-lg tracking-tight text-primary text-center mb-6">Bu adresi silmek istediğinize emin misiniz?</p>
                            <div class="flex gap-3">
                                <button type="button" @click="showDeleteConfirm = false; deleteAddressId = null" class="flex-1 py-3 text-xs font-bold uppercase tracking-widest border border-gray-200 text-primary hover:border-black transition">Vazgeç</button>
                                <form :action="baseUrl + '/hesabim/adresler/sil?id=' + deleteAddressId" method="post" class="flex-1" x-show="deleteAddressId">
                                    <button type="submit" class="w-full py-3 text-xs font-bold uppercase tracking-widest bg-black text-white hover:bg-gray-800 transition">Evet, sil</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php break; ?>

            <?php case 'details':
                $firstName = $old['first_name'] ?? $user['first_name'] ?? '';
                $lastName = $old['last_name'] ?? $user['last_name'] ?? '';
                $phone = $old['phone'] ?? $user['phone'] ?? '';
                ?>
                <script>
                document.addEventListener('alpine:init', function() {
                    Alpine.data('detailsForm', function() {
                        return {
                            newPassword: '',
                            showNewPassword: false,
                            rules: { length: false, upper: false, number: false, special: false },
                            checkStrength: function() {
                                var el = document.getElementById('new_password');
                                if (!el) return;
                                this.newPassword = el.value;
                                this.rules.length = this.newPassword.length >= 8;
                                this.rules.upper = /[A-Z]/.test(this.newPassword);
                                this.rules.number = /[0-9]/.test(this.newPassword);
                                this.rules.special = /[^A-Za-z0-9]/.test(this.newPassword);
                            },
                            newPasswordRulesPass: function() {
                                return this.rules.length && this.rules.upper && this.rules.number && this.rules.special;
                            }
                        };
                    });
                });
                </script>
                <h2 class="text-xs tracking-widest text-gray-400 uppercase mb-6">Kişisel Bilgiler</h2>
                <?php if (!empty($_GET['updated'])): ?><p class="mb-4 text-sm text-green-600">Bilgileriniz güncellendi.</p><?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <ul class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md text-sm text-red-700 list-none space-y-1">
                        <?php foreach (is_array($errors) ? $errors : [$errors] as $e): ?>
                            <li><?= htmlspecialchars(is_string($e) ? $e : implode(' ', (array)$e)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/hesabim/bilgilerim" class="max-w-lg space-y-4" x-data="detailsForm()">
                    <div>
                        <label for="first_name" class="block text-xs font-medium text-gray-600 mb-2">Ad</label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($firstName) ?>" required class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                    </div>
                    <div>
                        <label for="last_name" class="block text-xs font-medium text-gray-600 mb-2">Soyad</label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($lastName) ?>" required class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                    </div>
                    <div>
                        <label for="email" class="block text-xs font-medium text-gray-600 mb-2">E-posta</label>
                        <input type="email" id="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled class="w-full border border-gray-200 bg-gray-50 rounded-md py-3 px-4 text-sm text-gray-500">
                        <p class="text-xs text-gray-400 mt-1">E-posta değiştirilemez.</p>
                    </div>
                    <p class="text-xs tracking-widest text-gray-400 uppercase mt-8 mb-2">Şifre Değiştir</p>
                    <div>
                        <label for="current_password" class="block text-xs font-medium text-gray-600 mb-2">Mevcut Şifre</label>
                        <input type="password" id="current_password" name="current_password" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                    </div>
                    <div>
                        <label for="new_password" class="block text-xs font-medium text-gray-600 mb-2">Yeni Şifre</label>
                        <div class="relative">
                            <input :type="showNewPassword ? 'text' : 'password'" id="new_password" name="new_password" x-model="newPassword" @input="checkStrength()" placeholder="••••••••" minlength="8" class="w-full border border-gray-300 rounded-md py-3 px-4 pr-12 text-sm placeholder-gray-400 focus:ring-2 focus:ring-black focus:border-black transition" autocomplete="new-password">
                            <button type="button" @click="showNewPassword = !showNewPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" :aria-label="showNewPassword ? 'Şifreyi gizle' : 'Şifreyi göster'">
                                <svg x-show="!showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showNewPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        <ul class="text-[10px] mt-3 space-y-1" x-show="newPassword.length > 0">
                            <li class="flex items-center gap-2" :class="rules.length ? 'text-green-600' : 'text-gray-400'">
                                <span x-show="rules.length" class="text-green-600">✓</span>
                                <span x-show="!rules.length" x-cloak class="w-3 h-3 rounded-full bg-gray-300 inline-block flex-shrink-0"></span>
                                En az 8 karakter
                            </li>
                            <li class="flex items-center gap-2" :class="rules.upper ? 'text-green-600' : 'text-gray-400'">
                                <span x-show="rules.upper" class="text-green-600">✓</span>
                                <span x-show="!rules.upper" x-cloak class="w-3 h-3 rounded-full bg-gray-300 inline-block flex-shrink-0"></span>
                                En az bir büyük harf
                            </li>
                            <li class="flex items-center gap-2" :class="rules.number ? 'text-green-600' : 'text-gray-400'">
                                <span x-show="rules.number" class="text-green-600">✓</span>
                                <span x-show="!rules.number" x-cloak class="w-3 h-3 rounded-full bg-gray-300 inline-block flex-shrink-0"></span>
                                En az bir rakam
                            </li>
                            <li class="flex items-center gap-2" :class="rules.special ? 'text-green-600' : 'text-gray-400'">
                                <span x-show="rules.special" class="text-green-600">✓</span>
                                <span x-show="!rules.special" x-cloak class="w-3 h-3 rounded-full bg-gray-300 inline-block flex-shrink-0"></span>
                                En az bir sembol (!@#$% vb.)
                            </li>
                        </ul>
                    </div>
                    <button type="submit" class="w-full bg-black text-white py-4 mt-6 uppercase tracking-widest text-xs font-bold hover:bg-gray-800 transition rounded-md disabled:opacity-50 disabled:cursor-not-allowed" :disabled="newPassword.length > 0 && !newPasswordRulesPass()">
                        Değişiklikleri Kaydet
                    </button>
                </form>
                <?php break; ?>

            <?php case 'favorites': ?>
                <h2 class="text-xs tracking-widest text-gray-400 uppercase mb-6">Favori Listem</h2>
                <?php if (empty($products)): ?>
                    <p class="text-sm text-gray-500">Favori listeniz boş. <a href="<?= htmlspecialchars($baseUrl) ?>/" class="underline hover:text-black">Alışverişe başlayın</a>.</p>
                <?php else: ?>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                        <?php foreach ($products as $p): ?>
                            <?php
                            $price = (float)$p['price'];
                            $salePrice = $p['sale_price'] !== null ? (float)$p['sale_price'] : null;
                            $displayPrice = $salePrice !== null && $salePrice > 0 ? $salePrice : $price;
                            $imgPath = $productImages[$p['id']] ?? null;
                            ?>
                            <div class="group border border-gray-200 rounded-sm overflow-hidden hover:shadow-sm transition relative">
                                <a href="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($p['slug']) ?>" class="block aspect-[3/4] bg-gray-100 relative">
                                    <?php if ($imgPath): ?>
                                        <img src="<?= htmlspecialchars($baseUrl) ?>/<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <span class="absolute inset-0 flex items-center justify-center text-xs text-gray-400">Görsel yok</span>
                                    <?php endif; ?>
                                    <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/favori/sil" class="absolute top-2 right-2 z-10">
                                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($hesabimBase) ?>?tab=favorites">
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center bg-white/90 hover:bg-white border border-gray-200 rounded-full text-gray-500 hover:text-black transition" aria-label="Listeden çıkar">×</button>
                                    </form>
                                </a>
                                <div class="p-4">
                                    <a href="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($p['slug']) ?>" class="block">
                                        <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($p['name']) ?></p>
                                        <p class="text-sm font-medium text-gray-700 mt-0.5"><?= number_format($displayPrice, 2, ',', '.') ?> ₺</p>
                                    </a>
                                    <a href="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($p['slug']) ?>?add=1" class="mt-3 block w-full text-center py-2 border border-gray-200 text-xs uppercase tracking-wider hover:bg-black hover:text-white transition rounded-md">
                                        Sepete Ekle
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php break; ?>

            <?php default: ?>
                <p class="text-sm text-gray-500">Sayfa bulunamadı. <a href="<?= htmlspecialchars($hesabimBase) ?>" class="underline hover:text-black">Genel bakışa dön</a>.</p>
            <?php endswitch; ?>
        </div>
    </div>
</div>

<style>
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.scrollbar-hide::-webkit-scrollbar { display: none; }
</style>
