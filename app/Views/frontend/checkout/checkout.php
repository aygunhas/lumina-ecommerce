<?php
$userId = $userId ?? 0;
$isLoggedIn = (int) $userId > 0;

if (!function_exists('getLuminaImage')) {
    $fn = defined('BASE_PATH') ? BASE_PATH . '/includes/functions.php' : __DIR__ . '/../../../includes/functions.php';
    if (is_file($fn)) require_once $fn;
}
$baseUrl = $baseUrl ?? '';
$old = $old ?? [];
$errors = $errors ?? [];
$items = $items ?? [];
$subtotal = $subtotal ?? 0;
$shippingCost = $shippingCost ?? 0;
$discountAmount = $discountAmount ?? 0;
$total = $total ?? 0;
$defaultEmail = $old['guest_email'] ?? ($userEmail ?? '');
$paymentSettings = $paymentSettings ?? [];
$appliedCoupon = $appliedCoupon ?? null;
$codEnabled = ($paymentSettings['cod_enabled'] ?? '1') === '1';
$bankEnabled = ($paymentSettings['bank_transfer_enabled'] ?? '1') === '1';
$userName = $userName ?? '';
$userEmail = $userEmail ?? '';
$userAddresses = $userAddresses ?? [];
$defaultAddressId = null;
if (!empty($userAddresses)) {
    $first = $userAddresses[0];
    $defaultAddressId = (int)($first['id'] ?? 0);
}
$canSubmitCheckout = !$isLoggedIn || !empty($userAddresses); // Üye ise en az bir adres gerekli
$displayShipping = $isLoggedIn ? 0 : $shippingCost; // Üye: ücretsiz kargo göster
$displayTotal = $subtotal + $displayShipping - $discountAmount;
$displayTotal = max(0, $displayTotal);
?>
<div class="lg:grid lg:grid-cols-12 min-h-screen font-sans">
    <!-- Sol Kolon: Formlar (x-data: adres seçimi + yeni adres modalı form dışında) -->
    <div class="lg:col-span-7 px-6 py-12 lg:pl-32 lg:pr-12 flex flex-col" x-data="{ showNewAddressModal: false, selectedId: <?= $defaultAddressId ?: 0 ?> }">
        <!-- Header: Logo + Breadcrumb -->
        <a href="<?= htmlspecialchars($baseUrl) ?>/" class="text-2xl font-display tracking-widest text-black mb-6 block">LUMINA</a>
        <nav class="text-xs text-gray-500 mb-8 flex flex-wrap gap-2" aria-label="Breadcrumb">
            <a href="<?= htmlspecialchars($baseUrl) ?>/sepet" class="hover:text-black transition">Sepet</a>
            <span aria-hidden="true">/</span>
            <span>Bilgiler</span>
            <span aria-hidden="true">/</span>
            <span class="text-black font-medium">Ödeme</span>
        </nav>

        <?php if (!empty($errors)): ?>
            <ul class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md text-sm text-red-700 list-disc list-inside">
                <?php foreach (is_array($errors) ? $errors : [$errors] as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/odeme" id="checkout-form">
            <!-- ADIM 1: İletişim & Üyelik -->
            <section class="mb-10">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-widest">İletişim & Üyelik</h2>
                    <?php if (!$isLoggedIn): ?>
                        <a href="<?= htmlspecialchars($baseUrl) ?>/giris" class="text-xs text-gray-600 hover:text-black transition">Zaten üye misiniz? Giriş Yap</a>
                    <?php endif; ?>
                </div>

                <?php if ($isLoggedIn): ?>
                    <!-- Üye: Kullanıcı kartı -->
                    <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-md bg-gray-50">
                        <div class="w-12 h-12 rounded-full bg-black text-white flex items-center justify-center text-sm font-medium flex-shrink-0">
                            <?= $userName ? mb_substr(trim($userName), 0, 1) . (mb_strlen(trim($userName)) > 1 ? mb_substr(trim($userName), -1, 1) : '') : 'Ü' ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($userName ?: 'Üye') ?></p>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($userEmail ?: 'uye@email.com') ?></p>
                        </div>
                        <a href="<?= htmlspecialchars($baseUrl) ?>/cikis" class="text-xs text-gray-600 hover:text-black transition whitespace-nowrap">Farklı hesapla çıkış yap</a>
                    </div>
                    <input type="hidden" name="guest_email" value="<?= htmlspecialchars($userEmail ?: '') ?>">
                <?php else: ?>
                    <!-- Misafir: E-posta + kampanya -->
                    <div class="space-y-4">
                        <div>
                            <label for="guest_email" class="block text-xs font-medium text-gray-600 mb-2">E-Posta Adresi</label>
                            <input type="email" id="guest_email" name="guest_email" value="<?= htmlspecialchars($defaultEmail) ?>" required placeholder="ornek@email.com" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm placeholder-gray-400 focus:ring-2 focus:ring-black focus:border-black transition">
                        </div>
                    </div>
                <?php endif; ?>
            </section>

            <!-- ADIM 2: Teslimat Adresi -->
            <section class="mb-10">
                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-widest mb-4">Teslimat Adresi</h2>

                <?php if ($isLoggedIn): ?>
                    <!-- Üye: Kayıtlı adresler (sayfa tasarımına uyumlu) + yeni adres butonu -->
                    <div class="space-y-4">
                        <?php if (!empty($userAddresses)): ?>
                            <p class="text-xs text-gray-500 mb-3">Teslimat için bir adres seçin veya yeni adres ekleyin.</p>
                            <div class="space-y-3">
                                <?php foreach ($userAddresses as $i => $addr): ?>
                                    <?php
                                    $aid = (int)($addr['id'] ?? 0);
                                    $title = $addr['title'] ?? '';
                                    $firstName = $addr['first_name'] ?? '';
                                    $lastName = $addr['last_name'] ?? '';
                                    $phone = $addr['phone'] ?? '';
                                    $district = $addr['district'] ?? '';
                                    $city = $addr['city'] ?? '';
                                    $line = $addr['address_line'] ?? '';
                                    $postalCode = $addr['postal_code'] ?? '';
                                    $isDefault = !empty($addr['is_default']);
                                    $checked = ($defaultAddressId && $aid === $defaultAddressId) || ($i === 0 && !$defaultAddressId);
                                    $fullLine = trim($line . ($line && ($district || $city) ? ', ' : '') . trim($district . ($district && $city ? ' / ' : '') . $city) . ($postalCode ? ' ' . $postalCode : ''));
                                    ?>
                                    <label class="cursor-pointer block">
                                        <input type="radio" name="address_id" value="<?= $aid ?>" <?= $checked ? 'checked' : '' ?> x-model.number="selectedId" class="sr-only peer">
                                        <div class="p-4 border border-gray-200 rounded-md transition flex items-start gap-3 bg-white hover:border-gray-300" :class="selectedId === <?= $aid ?> ? 'border-black ring-1 ring-black' : ''">
                                            <span class="flex-shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center mt-0.5" :class="selectedId === <?= $aid ?> ? 'border-black bg-black' : 'border-gray-300'">
                                                <template x-if="selectedId === <?= $aid ?>"><svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></template>
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-medium text-gray-900"><?= htmlspecialchars(trim($firstName . ' ' . $lastName)) ?: '—' ?></p>
                                                <?php if ($phone !== ''): ?><p class="text-sm text-gray-600 mt-0.5"><?= htmlspecialchars($phone) ?></p><?php endif; ?>
                                                <p class="text-sm text-gray-600 mt-0.5"><?= htmlspecialchars($fullLine ?: '—') ?></p>
                                                <div class="mt-2 flex items-center gap-2 flex-wrap">
                                                    <?php if ($isDefault): ?><span class="text-[10px] uppercase tracking-wider text-gray-500">Varsayılan</span><?php endif; ?>
                                                    <?php if ($title !== ''): ?><span class="text-[10px] text-gray-400"><?= htmlspecialchars($title) ?></span><?php endif; ?>
                                                    <button type="button" @click.stop="$refs.detail<?= $aid ?>.classList.toggle('hidden')" class="text-[10px] text-gray-500 hover:text-black underline">Tüm detay</button>
                                                </div>
                                                <div x-ref="detail<?= $aid ?>" class="hidden mt-2 pt-2 border-t border-gray-100 text-xs text-gray-500 space-y-0.5">
                                                    <?php if ($title !== ''): ?><p><span class="text-gray-400">Başlık:</span> <?= htmlspecialchars($title) ?></p><?php endif; ?>
                                                    <p><span class="text-gray-400">Ad Soyad:</span> <?= htmlspecialchars(trim($firstName . ' ' . $lastName)) ?></p>
                                                    <p><span class="text-gray-400">Telefon:</span> <?= htmlspecialchars($phone) ?></p>
                                                    <p><span class="text-gray-400">Adres:</span> <?= htmlspecialchars($line) ?></p>
                                                    <p><span class="text-gray-400">İlçe / İl:</span> <?= htmlspecialchars($district) ?> / <?= htmlspecialchars($city) ?></p>
                                                    <?php if ($postalCode !== ''): ?><p><span class="text-gray-400">Posta kodu:</span> <?= htmlspecialchars($postalCode) ?></p><?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" @click="showNewAddressModal = true" class="text-sm text-gray-600 hover:text-black transition underline">
                                + Yeni Adres Ekle
                            </button>
                        <?php else: ?>
                            <p class="text-sm text-gray-600 mb-3">Henüz kayıtlı adresiniz yok. Aşağıdaki butondan teslimat adresi ekleyebilirsiniz.</p>
                            <button type="button" @click="showNewAddressModal = true" class="inline-flex items-center gap-2 px-4 py-3 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:border-black hover:text-black transition">
                                <span>+ Yeni Adres Ekle</span>
                            </button>
                            <input type="hidden" name="address_id" value="">
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Misafir: Standart adres formu -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" id="shipping_first_name" name="shipping_first_name" value="<?= htmlspecialchars($old['shipping_first_name'] ?? '') ?>" required placeholder="Ad" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                            <input type="text" id="shipping_last_name" name="shipping_last_name" value="<?= htmlspecialchars($old['shipping_last_name'] ?? '') ?>" required placeholder="Soyad" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                        </div>
                        <input type="text" id="shipping_address_line" name="shipping_address_line" value="<?= htmlspecialchars($old['shipping_address_line'] ?? '') ?>" required placeholder="Adres" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" id="shipping_district" name="shipping_district" value="<?= htmlspecialchars($old['shipping_district'] ?? '') ?>" required placeholder="İlçe" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                            <input type="text" id="shipping_city" name="shipping_city" value="<?= htmlspecialchars($old['shipping_city'] ?? '') ?>" required placeholder="Şehir" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" id="shipping_phone" name="shipping_phone" value="<?= htmlspecialchars($old['shipping_phone'] ?? '') ?>" required placeholder="Telefon" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                            <input type="text" id="shipping_postal_code" name="shipping_postal_code" value="<?= htmlspecialchars($old['shipping_postal_code'] ?? '') ?>" placeholder="Posta kodu" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                        </div>
                        <textarea id="customer_notes" name="customer_notes" rows="2" placeholder="Sipariş notu (isteğe bağlı)" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition"><?= htmlspecialchars($old['customer_notes'] ?? '') ?></textarea>
                    </div>
                <?php endif; ?>
            </section>

            <!-- ADIM 3: Ödeme Yöntemleri -->
            <section class="mb-10" x-data="{ paymentTab: 'card', useNewCard: false }">
                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-widest mb-4">Ödeme Yöntemi</h2>
                <!-- Tabs: Kredi Kartı | Havale -->
                <div class="flex border-b border-gray-200 mb-6">
                    <button type="button" @click="paymentTab = 'card'" :class="paymentTab === 'card' ? 'border-black text-black font-medium' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-2 text-sm border-b-2 transition">Kredi Kartı</button>
                    <button type="button" @click="paymentTab = 'havale'" :class="paymentTab === 'havale' ? 'border-black text-black font-medium' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-2 text-sm border-b-2 transition">Havale</button>
                </div>

                <div x-show="paymentTab === 'card'" x-cloak x-transition>
                    <?php if ($isLoggedIn): ?>
                        <!-- Üye: Kayıtlı kartlar -->
                        <div class="space-y-3 mb-4">
                            <label class="flex items-center gap-3 p-4 border border-gray-200 rounded-md cursor-pointer hover:border-gray-300 transition">
                                <input type="radio" name="saved_card" value="4242" checked class="w-4 h-4 border-gray-300 text-black focus:ring-black" x-model="useNewCard" :value="false">
                                <span class="text-sm font-medium text-gray-900">Mastercard **** 4242</span>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-gray-200 rounded-md cursor-pointer hover:border-gray-300 transition">
                                <input type="radio" name="saved_card" value="5555" class="w-4 h-4 border-gray-300 text-black focus:ring-black" x-model="useNewCard" :value="false">
                                <span class="text-sm font-medium text-gray-900">Visa **** 5555</span>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-gray-200 rounded-md cursor-pointer" @click="useNewCard = true">
                                <input type="radio" name="saved_card" value="new" class="w-4 h-4 border-gray-300 text-black focus:ring-black" x-model="useNewCard">
                                <span class="text-sm font-medium text-gray-900">Başka bir kart kullan</span>
                            </label>
                        </div>
                    <?php endif; ?>

                    <div x-show="<?= $isLoggedIn ? 'useNewCard' : 'true' ?>" x-cloak x-transition class="space-y-4">
                        <div class="relative">
                            <label class="block text-xs font-medium text-gray-600 mb-2">Kart Numarası</label>
                            <input type="text" id="checkout-card-number" name="card_number" placeholder="4242 4242 4242 4242" maxlength="19" inputmode="numeric" autocomplete="cc-number" class="w-full border border-gray-300 rounded-md py-3 px-4 pl-12 text-sm focus:ring-2 focus:ring-black focus:border-black transition" x-ref="cardInput">
                            <div class="absolute left-3 top-9 flex gap-1 pointer-events-none">
                                <svg class="w-8 h-5 opacity-60" viewBox="0 0 24 24" fill="currentColor"><path d="M15.245 17.831h-2.733l1.64-10.062h2.734l-1.64 10.062zM6.323 7.671L4.247 17.831H1.6L3.2 7.671h3.123zm5.824 0L8.4 17.831H5.753l1.624-10.16h2.734l.036.229-.116.725-.756 4.724h2.735l.144-.894.93-5.784H12.147zm11.372 0l-2.504 10.16h-2.73l2.503-10.16h2.731z"/></svg>
                                <svg class="w-8 h-5 opacity-60" viewBox="0 0 24 24" fill="currentColor"><path d="M11.466 22c-4.726 0-8.127-1.343-10.028-3.893l3.038-2.658c1.46 1.174 3.34 1.785 5.056 1.785 2.1 0 3.304-.98 3.98-2.31l-.14-.666-.722-.004c-2.67 0-4.525-1.108-4.525-2.993 0-2.016 1.776-3.418 4.597-3.418 2.638 0 4.213 1.075 4.213 2.81 0 .93-.378 1.737-.99 2.244l.006.024h3.38l-.214 1.003c-.71 2.63-2.636 3.8-5.642 3.8zm.192-8.293c1.004 0 1.656.427 1.656 1.024 0 .533-.416.884-1.108.884h-.688l.238-1.908h.902z"/></svg>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-2">Kart Üzerindeki İsim</label>
                            <input type="text" name="card_name" placeholder="Ad Soyad" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Son Kullanma (AA/YY)</label>
                                <input type="text" name="card_exp" placeholder="AA/YY" maxlength="5" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">CVC</label>
                                <input type="text" name="card_cvc" placeholder="123" maxlength="4" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                            </div>
                        </div>
                        <!-- Taksit tablosu (simülasyon: kart girilince görünür) -->
                        <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-md" x-data="{ showInstallments: true }">
                            <p class="text-xs font-medium text-gray-600 mb-3">Taksit Seçenekleri</p>
                            <div class="grid grid-cols-3 gap-2 text-sm">
                                <div class="py-2 text-center border border-gray-200 rounded bg-white"><span class="font-medium">1</span> Taksit<br><span class="text-gray-600"><?= number_format($displayTotal, 2, ',', '.') ?> ₺</span></div>
                                <div class="py-2 text-center border border-gray-200 rounded bg-white"><span class="font-medium">2</span> Taksit<br><span class="text-gray-600"><?= number_format($displayTotal / 2, 2, ',', '.') ?> ₺</span></div>
                                <div class="py-2 text-center border border-gray-200 rounded bg-white"><span class="font-medium">3</span> Taksit<br><span class="text-gray-600"><?= number_format($displayTotal / 3, 2, ',', '.') ?> ₺</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="payment_method" :value="paymentTab === 'havale' ? 'bank_transfer' : 'stripe'">
                <div x-show="paymentTab === 'havale'" x-cloak x-transition>
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-md text-sm text-gray-700 space-y-2">
                        <?php if (!empty($paymentSettings['bank_name']) || !empty($paymentSettings['bank_iban'])): ?>
                            <?php if (!empty($paymentSettings['bank_name'])): ?><p><strong>Banka:</strong> <?= htmlspecialchars($paymentSettings['bank_name']) ?></p><?php endif; ?>
                            <?php if (!empty($paymentSettings['bank_account_name'])): ?><p><strong>Hesap adı:</strong> <?= htmlspecialchars($paymentSettings['bank_account_name']) ?></p><?php endif; ?>
                            <?php if (!empty($paymentSettings['bank_iban'])): ?><p><strong>IBAN:</strong> <code class="bg-white px-1 py-0.5 rounded"><?= htmlspecialchars($paymentSettings['bank_iban']) ?></code></p><?php endif; ?>
                        <?php else: ?>
                            <p><strong>Banka:</strong> Örnek Bankası A.Ş.</p>
                            <p><strong>Hesap adı:</strong> Lumina E-Ticaret Ltd. Şti.</p>
                            <p><strong>IBAN:</strong> <code class="bg-white px-1 py-0.5 rounded">TR00 0000 0000 0000 0000 0000 00</code></p>
                            <p><strong>Şube:</strong> Beşiktaş / İstanbul</p>
                        <?php endif; ?>
                        <p class="text-gray-500 mt-2">Siparişinizi tamamladıktan sonra havale/EFT ile ödeme yapabilirsiniz. Ödeme açıklamasına <strong>sipariş numaranızı</strong> yazmanızı rica ederiz. Ödeme onaylandıktan sonra siparişiniz kargoya verilecektir.</p>
                    </div>
                </div>
            </section>

            <!-- ADIM 4: Fatura Adresi -->
            <section class="mb-10" x-data="{ sameAsShipping: true }">
                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-widest mb-4">Fatura Adresi</h2>
                <label class="flex items-center gap-3 cursor-pointer mb-4">
                    <input type="checkbox" x-model="sameAsShipping" class="w-4 h-4 border-gray-300 rounded text-black focus:ring-black">
                    <span class="text-sm text-gray-700">Teslimat adresiyle aynı</span>
                </label>
                <div x-show="!sameAsShipping" x-cloak x-transition class="space-y-4 pt-4 border-t border-gray-200">
                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" name="billing_first_name" placeholder="Ad" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                        <input type="text" name="billing_last_name" placeholder="Soyad" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                    </div>
                    <input type="text" name="billing_address_line" placeholder="Adres" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" name="billing_district" placeholder="İlçe" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                        <input type="text" name="billing_city" placeholder="Şehir" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                    </div>
                </div>
            </section>

            <!-- Yasal zorunluluklar -->
            <section class="mb-10">
                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-widest mb-4">Yasal Onaylar</h2>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="accept_kvkk_mesafeli" value="1" required class="w-4 h-4 mt-0.5 border-gray-300 rounded text-black focus:ring-black">
                    <span class="text-sm text-gray-700"><a href="<?= htmlspecialchars($baseUrl) ?>/kvkk" target="_blank" rel="noopener" class="underline hover:text-black">KVKK Aydınlatma Metni</a> ve <a href="<?= htmlspecialchars($baseUrl) ?>/mesafeli-satis-sozlesmesi" target="_blank" rel="noopener" class="underline hover:text-black">Mesafeli Satış Sözleşmesi</a>'ni okudum ve kabul ediyorum.</span>
                </label>
                <?php if (!empty($errors['accept_kvkk_mesafeli'])): ?>
                    <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars($errors['accept_kvkk_mesafeli']) ?></p>
                <?php endif; ?>
            </section>

            <!-- Kampanya (KVKK altı, bilgilendirme kutusunun üstü) -->
            <section class="mb-10">
                <label class="flex items-start gap-3 cursor-pointer mb-3">
                    <input type="checkbox" name="newsletter" value="1" <?= !empty($old['newsletter']) ? 'checked' : '' ?> class="w-4 h-4 mt-0.5 border-gray-300 rounded text-black focus:ring-black">
                    <span class="text-sm text-gray-700">Kampanya, indirim ve yeniliklerden <a href="<?= htmlspecialchars($baseUrl) ?>/reklam-iletisim-izni" target="_blank" rel="noopener" class="underline hover:text-black">Reklam ve İletişim İzni</a> metnini okuyarak e-posta ve SMS ile haberdar olmak istiyorum.</span>
                </label>
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-md text-xs text-gray-600">
                    <p class="font-medium text-gray-700 mb-2">Reklam ve İletişim İzni</p>
                    <p>6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında; Lumina markasına ait kampanya, indirim ve yeniliklerin e-posta ve SMS ile iletilmesi için açık rızanız gerekmektedir. İşbu metni kabul etmeniz halinde kişisel iletişim bilgileriniz yalnızca reklam ve tanıtım amaçlı kullanılacaktır. İstediğiniz zaman abonelikten çıkabilirsiniz.</p>
                </div>
            </section>

            <p class="mt-6">
                <a href="<?= htmlspecialchars($baseUrl) ?>/sepet" class="text-sm text-gray-500 hover:text-black transition">← Sepete dön</a>
            </p>
        </form>

        <?php if ($isLoggedIn): ?>
        <!-- Modal: Yeni Adres Ekle (checkout formunun dışında; nested form KVKK hatasını önler) -->
        <div x-show="showNewAddressModal" x-cloak class="fixed inset-0 z-[60] bg-black/40 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto" x-transition.opacity aria-modal="true" role="dialog">
            <div @click.away="showNewAddressModal = false" class="bg-white w-full max-w-lg max-h-[calc(100vh-2rem)] shadow-2xl rounded-md my-auto flex flex-col" x-show="showNewAddressModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/hesabim/adresler/ekle" class="flex flex-col min-h-0 flex-1">
                    <input type="hidden" name="redirect" value="/odeme">
                    <div class="flex justify-between items-center p-6 border-b border-gray-200 flex-shrink-0">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-widest">Yeni Adres Ekle</h3>
                        <button type="button" @click="showNewAddressModal = false" class="text-gray-400 hover:text-black p-1" aria-label="Kapat">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-2">Adres Başlığı</label>
                            <input type="text" name="title" placeholder="Örn: Ev, İş" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Ad</label>
                                <input type="text" name="first_name" required class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Soyad</label>
                                <input type="text" name="last_name" required class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-2">Telefon</label>
                            <input type="tel" name="phone" required class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">İl</label>
                                <input type="text" name="city" required placeholder="İstanbul" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">İlçe</label>
                                <input type="text" name="district" required placeholder="İlçe" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-2">Adres</label>
                            <textarea name="address_line" rows="3" required class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-2">Posta Kodu</label>
                            <input type="text" name="postal_code" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_default" value="1" class="w-4 h-4 border-gray-300 rounded text-black focus:ring-black">
                            <span class="text-sm text-gray-700">Varsayılan adres olarak kaydet</span>
                        </label>
                    </div>
                    <div class="p-6 bg-gray-50 flex justify-end gap-4 rounded-b-md flex-shrink-0 border-t border-gray-200">
                        <button type="button" @click="showNewAddressModal = false" class="text-sm font-medium text-gray-600 hover:text-black px-4 py-3 transition">Vazgeç</button>
                        <button type="submit" class="bg-black text-white text-sm font-medium px-8 py-3 rounded-md hover:bg-gray-800 transition uppercase tracking-wider">Kaydet ve kullan</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sağ Kolon: Sipariş Özeti -->
    <aside class="lg:col-span-5 bg-gray-50 px-6 py-12 lg:pr-32 lg:pl-12 border-l border-gray-200 sticky top-0 h-screen hidden lg:flex lg:flex-col" aria-label="Sipariş özeti">
        <div class="max-w-sm w-full flex flex-col flex-1 min-h-0">
            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-widest mb-4">Sipariş Özeti</h2>
            <!-- Ürün listesi - scroll -->
            <ul class="space-y-4 overflow-y-auto flex-1 min-h-0 pr-2 mb-6">
                <?php foreach ($items as $row): ?>
                    <?php $qty = (int)($row['quantity'] ?? 1); ?>
                    <li class="flex gap-4 flex-shrink-0">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 rounded border border-gray-200 bg-gray-100 overflow-hidden flex items-center justify-center">
                                <?php
                                $imgSrc = !empty($row['image_path']) ? $baseUrl . '/' . $row['image_path'] : '';
                                if ($imgSrc): ?>
                                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="w-full h-full flex items-center justify-center text-[10px] text-gray-400">Görsel yok</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($row['name'] ?? '') ?></p>
                            <?php if (!empty($row['attributes_summary'])): ?>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($row['attributes_summary']) ?></p>
                            <?php endif; ?>
                            <p class="text-xs text-gray-500 mt-0.5"><?= $qty ?> adet</p>
                            <p class="text-sm text-gray-600 mt-0.5"><?= number_format((float)($row['total'] ?? 0), 2, ',', '.') ?> ₺</p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <!-- Kupon -->
            <div class="mb-4" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="text-sm text-gray-600 hover:text-black transition underline">İndirim kuponu gir</button>
                <div x-show="open" x-cloak x-transition class="mt-2 flex gap-2">
                    <input type="text" name="coupon_code" value="<?= htmlspecialchars($old['coupon_code'] ?? '') ?>" placeholder="Kupon kodu" class="flex-1 border border-gray-300 rounded-md py-2 px-3 text-sm focus:ring-2 focus:ring-black focus:border-black transition">
                    <button type="button" form="checkout-form" class="py-2 px-3 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-100 transition">Uygula</button>
                </div>
                <?php if (!empty($appliedCoupon)): ?>
                    <p class="mt-2 text-sm text-green-700">✓ <?= htmlspecialchars($appliedCoupon['code']) ?> uygulandı</p>
                <?php endif; ?>
            </div>
            <!-- Fiyatlar -->
            <div class="border-t border-gray-200 pt-4 space-y-2">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Ara Toplam</span>
                    <span><?= number_format($subtotal, 2, ',', '.') ?> ₺</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Kargo</span>
                    <span><?= $isLoggedIn ? 'Ücretsiz' : ($shippingCost > 0 ? number_format($shippingCost, 2, ',', '.') . ' ₺' : 'Ücretsiz') ?></span>
                </div>
                <?php if ($discountAmount > 0): ?>
                    <div class="flex justify-between text-sm text-green-600">
                        <span>İndirim</span>
                        <span>-<?= number_format($discountAmount, 2, ',', '.') ?> ₺</span>
                    </div>
                <?php endif; ?>
                <div class="flex justify-between text-lg font-semibold mt-4 pt-4 border-t border-gray-200">
                    <span>Toplam</span>
                    <span><?= number_format($displayTotal, 2, ',', '.') ?> ₺</span>
                </div>
            </div>
            <?php if ($isLoggedIn && empty($userAddresses)): ?>
                <p class="text-sm text-amber-700 mt-4">Ödeme için önce teslimat adresi eklemeniz gerekiyor.</p>
            <?php endif; ?>
            <button type="submit" form="checkout-form" class="w-full mt-6 bg-black text-white py-4 rounded-md font-bold text-sm uppercase tracking-widest hover:bg-gray-800 transition disabled:opacity-50 disabled:cursor-not-allowed" <?= $canSubmitCheckout ? '' : 'disabled' ?>>
                <?= number_format($displayTotal, 2, ',', '.') ?> ₺ ÖDE
            </button>
        </div>
    </aside>

    <!-- Mobil: Özet accordion + Öde butonu -->
    <div class="lg:hidden px-6 pb-12" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="w-full py-3 flex items-center justify-between text-sm font-medium text-gray-700 border-t border-gray-200">
            <span>Sipariş özeti</span>
            <span x-text="open ? '▴' : '▾'" class="text-gray-500"></span>
        </button>
        <div x-show="open" x-cloak x-transition class="py-4 space-y-2 text-sm text-gray-600">
            <div class="flex justify-between"><span>Ara Toplam</span><span><?= number_format($subtotal, 2, ',', '.') ?> ₺</span></div>
            <div class="flex justify-between"><span>Kargo</span><span><?= $isLoggedIn ? 'Ücretsiz' : ($shippingCost > 0 ? number_format($shippingCost, 2, ',', '.') . ' ₺' : 'Ücretsiz') ?></span></div>
            <?php if ($discountAmount > 0): ?>
                <div class="flex justify-between text-green-600"><span>İndirim</span><span>-<?= number_format($discountAmount, 2, ',', '.') ?> ₺</span></div>
            <?php endif; ?>
            <div class="flex justify-between text-base font-semibold text-gray-900 pt-2 border-t border-gray-200">
                <span>Toplam</span><span><?= number_format($displayTotal, 2, ',', '.') ?> ₺</span>
            </div>
        </div>
        <?php if ($isLoggedIn && empty($userAddresses)): ?>
            <p class="text-sm text-amber-700 mt-4">Ödeme için önce teslimat adresi eklemeniz gerekiyor.</p>
        <?php endif; ?>
        <button type="submit" form="checkout-form" class="w-full mt-4 bg-black text-white py-4 rounded-md font-bold text-sm uppercase tracking-widest hover:bg-gray-800 transition disabled:opacity-50 disabled:cursor-not-allowed" <?= $canSubmitCheckout ? '' : 'disabled' ?>>
            <?= number_format($displayTotal, 2, ',', '.') ?> ₺ ÖDE
        </button>
    </div>
</div>

<script>
(function() {
    var el = document.getElementById('checkout-card-number');
    if (!el) return;
    el.addEventListener('input', function() {
        var v = this.value.replace(/\D/g, '');
        if (v.length > 16) v = v.slice(0, 16);
        var parts = [];
        for (var i = 0; i < v.length; i += 4) parts.push(v.slice(i, i + 4));
        this.value = parts.join(' ');
        this.setSelectionRange(this.value.length, this.value.length);
    });
    el.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value.slice(-1) === ' ') {
            this.value = this.value.slice(0, -1);
            e.preventDefault();
        }
    });
})();
</script>

