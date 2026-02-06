<?php
$baseUrl = $baseUrl ?? '';
$productImagePaths = $productImagePaths ?? [];
$price = (float) ($product['price'] ?? 0);
$salePrice = $product['sale_price'] !== null ? (float) $product['sale_price'] : null;
$displayPrice = $salePrice !== null && $salePrice > 0 ? $salePrice : $price;
$displayPriceFormatted = number_format($displayPrice, 2, ',', '.');
$hasVariants = !empty($productVariants);
$productVariants = $productVariants ?? [];
$attributesForVariant = $attributesForVariant ?? [];
$attributeValuesByAttr = $attributeValuesByAttr ?? [];

// Galeri: veritabanındaki görseller + test için en az 4 görsel (eksikse farklı demo görsellerle doldur)
$productImageUrls = [];
foreach ($productImagePaths as $path) {
    $productImageUrls[] = $baseUrl . '/' . $path;
}
if (count($productImageUrls) < 4) {
    if (!function_exists('getLuminaImage')) {
        require_once (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3)) . '/includes/functions.php';
    }
    // Kırık/tekrar görünmesin diye sırayla product 0,1,2 ve hero 0 kullan (hepsi çalışan URL’ler)
    $demoIndices = [['product', 0], ['product', 1], ['product', 2], ['hero', 0]];
    foreach (array_slice($demoIndices, 0, 4 - count($productImageUrls)) as $pair) {
        $productImageUrls[] = getLuminaImage($pair[0], $pair[1]);
    }
}
$galleryJson = json_encode($productImageUrls);
?>
<div class="max-w-[1400px] mx-auto px-4 md:px-8 lg:px-10 pt-12 md:pt-16 pb-6 md:pb-8">
<nav class="mb-6 text-sm text-secondary">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" class="hover:text-primary transition">Anasayfa</a>
    <?php if (!empty($product['category_slug'])): ?>
        <span class="text-gray-400"> / </span>
        <a href="<?= htmlspecialchars($baseUrl) ?>/kategori/<?= htmlspecialchars($product['category_slug']) ?>" class="hover:text-primary transition"><?= htmlspecialchars($product['category_name']) ?></a>
    <?php endif; ?>
    <span class="text-gray-400"> / </span>
    <span class="text-primary"><?= htmlspecialchars($product['name']) ?></span>
</nav>

<div class="grid grid-cols-1 md:grid-cols-12 gap-8 md:gap-10 items-start">
    <!-- Sol: Galeri – yarı yarıya (6/12), tam genişlik -->
    <div class="md:col-span-6 w-full min-w-0" x-data="{ selectedIndex: 0, images: <?= htmlspecialchars($galleryJson, ENT_QUOTES, 'UTF-8') ?>, next() { this.selectedIndex = (this.selectedIndex + 1) % this.images.length }, prev() { this.selectedIndex = (this.selectedIndex - 1 + this.images.length) % this.images.length } }">
        <div class="relative w-full bg-gray-100 rounded-lg overflow-hidden aspect-[4/5] max-h-[560px] md:max-h-[600px]">
            <template x-for="(img, index) in images" :key="index">
                <img x-show="index === selectedIndex" :src="img" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>" class="w-full h-full object-cover absolute inset-0" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            </template>
            <template x-if="images.length > 1">
                <div class="absolute inset-0 flex items-center justify-between pointer-events-none px-2 md:px-4">
                    <button type="button" @click="prev()" class="pointer-events-auto w-10 h-10 md:w-12 md:h-12 rounded-full bg-white/90 hover:bg-white shadow-md flex items-center justify-center text-primary transition" aria-label="Önceki görsel">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 md:w-6 md:h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    </button>
                    <button type="button" @click="next()" class="pointer-events-auto w-10 h-10 md:w-12 md:h-12 rounded-full bg-white/90 hover:bg-white shadow-md flex items-center justify-center text-primary transition" aria-label="Sonraki görsel">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 md:w-6 md:h-6"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Sağ: Ürün bilgileri – yarı yarıya (6/12), sticky masaüstü -->
    <div class="md:col-span-6 px-4 py-6 md:px-0 md:sticky md:top-24 md:h-fit">
        <div x-data="{
            selectedColor: 'Siyah',
            selectedSize: null,
            sizeStock: { XS: true, S: true, M: true, L: true, XL: false },
            buttonLabel: 'Sepete Ekle',
            addedFeedback: false,
            hasVariants: <?= $hasVariants ? 'true' : 'false' ?>,
            addToCart() {
                var form = document.getElementById(this.hasVariants ? 'add-cart-variant-form' : 'add-cart-simple-form');
                if (this.hasVariants) {
                    var variantEl = document.getElementById('product_variant_id');
                    if (!variantEl || !variantEl.value) {
                        this.$dispatch('notify', { message: 'Lütfen varyant seçin.' });
                        return;
                    }
                } else {
                    if (!this.selectedSize) {
                        this.$dispatch('notify', { message: 'Lütfen beden seçin.' });
                        return;
                    }
                }
                var self = this;
                fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            self.$dispatch('cart-updated');
                            self.$dispatch('cart-open');
                            self.$dispatch('notify', { message: 'Ürün sepete eklendi.' });
                        } else {
                            self.$dispatch('notify', { message: data.message || 'Hata oluştu.' });
                        }
                    })
                    .catch(function() { self.$dispatch('notify', { message: 'Bir hata oluştu.' }); });
            }
        }">
            <!-- Etiketler -->
            <div class="flex flex-wrap gap-2 mb-4">
                <?php if ((int)($product['is_featured'] ?? 0) === 1): ?><span class="text-[10px] bg-primary text-white px-2 py-1 tracking-widest uppercase">Öne çıkan</span><?php endif; ?>
                <?php if ((int)($product['is_new'] ?? 0) === 1): ?><span class="text-[10px] bg-white border border-primary text-primary px-2 py-1 tracking-widest uppercase">NEW</span><?php endif; ?>
                <?php if ($salePrice !== null && $salePrice > 0 && $price > 0): $discountPercent = (int) round((($price - $salePrice) / $price) * 100); if ($discountPercent > 0): ?>
                    <span class="text-[10px] bg-red-900 text-white px-2 py-1 tracking-widest uppercase">%<?= $discountPercent ?> İndirim</span>
                <?php endif; endif; ?>
            </div>

            <!-- 1. Başlık, Favori ve Fiyat -->
            <div class="flex items-start justify-between gap-4 mb-2">
                <h1 class="font-display text-3xl md:text-4xl text-primary tracking-tight flex-1"><?= htmlspecialchars($product['name']) ?></h1>
                <?php if (!empty($userId)): ?>
                    <?php if ($isInWishlist): ?>
                        <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/favori/sil" class="flex-shrink-0">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($product['slug']) ?>">
                            <button type="submit" class="p-2 text-red-600 hover:text-red-700 transition" aria-label="Favorilerden çıkar" title="Favorilerden çıkar">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" /></svg>
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/favori/ekle" class="flex-shrink-0">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($product['slug']) ?>">
                            <button type="submit" class="p-2 text-secondary hover:text-primary transition" aria-label="Favorilere ekle" title="Favorilere ekle">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                            </button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/giris?redirect=<?= urlencode($baseUrl . '/urun/' . $product['slug']) ?>" class="flex-shrink-0 p-2 text-secondary hover:text-primary transition" aria-label="Favorilere eklemek için giriş yapın" title="Favorilere ekle (giriş gerekli)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                    </a>
                <?php endif; ?>
            </div>
            <p class="text-lg text-secondary font-light mb-6">₺<?= $displayPriceFormatted ?></p>

            <!-- 2. Renk Seçimi -->
            <p class="text-xs uppercase tracking-widest text-gray-500 mb-3">Renk: <span x-text="selectedColor"></span></p>
            <div class="flex gap-2 mb-6">
                <button type="button" @click="selectedColor = 'Siyah'" class="w-8 h-8 rounded-full border border-black/10 flex-shrink-0 bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition" :class="{ 'ring-1 ring-black ring-offset-2': selectedColor === 'Siyah' }" aria-label="Siyah"></button>
                <button type="button" @click="selectedColor = 'Bej'" class="w-8 h-8 rounded-full border border-black/10 flex-shrink-0 bg-[#c4a77d] focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition" :class="{ 'ring-1 ring-black ring-offset-2': selectedColor === 'Bej' }" aria-label="Bej"></button>
                <button type="button" @click="selectedColor = 'Bordo'" class="w-8 h-8 rounded-full border border-black/10 flex-shrink-0 bg-[#722f37] focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition" :class="{ 'ring-1 ring-black ring-offset-2': selectedColor === 'Bordo' }" aria-label="Bordo"></button>
            </div>

            <!-- 3. Beden Seçimi -->
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase tracking-widest text-gray-500">Beden</span>
                <a href="#" class="text-[10px] text-secondary underline hover:text-primary transition">Beden Tablosu</a>
            </div>
            <div class="grid grid-cols-5 gap-2 mb-6">
                <?php foreach (['XS', 'S', 'M', 'L', 'XL'] as $size): ?>
                    <?php $outOfStock = ($size === 'XL'); ?>
                    <button type="button"
                        x-data="{ outOfStock: <?= $outOfStock ? 'true' : 'false' ?> }"
                        @click="if (!outOfStock) selectedSize = '<?= $size ?>'"
                        class="border py-3 text-xs text-center transition cursor-pointer"
                        :class="outOfStock ? 'text-gray-300 line-through cursor-not-allowed border-gray-100 bg-gray-50' : (selectedSize === '<?= $size ?>' ? 'bg-black text-white border-black' : 'border-gray-200 hover:border-black text-primary')"
                        :disabled="outOfStock"
                    ><?= $size ?></button>
                <?php endforeach; ?>
            </div>

            <!-- 4. Sepete Ekle -->
            <?php if ($hasVariants): ?>
                <div id="variant-selectors" class="mb-4">
                    <?php foreach ($attributesForVariant as $attr): ?>
                        <?php $vals = $attributeValuesByAttr[$attr['id']] ?? []; if (empty($vals)) continue; ?>
                        <p class="mb-1 text-xs font-medium text-secondary"><?= htmlspecialchars($attr['name']) ?></p>
                        <select data-attribute-id="<?= (int) $attr['id'] ?>" class="variant-attr-select border border-gray-200 rounded px-3 py-2 text-sm mb-3">
                            <option value="">— Seçin —</option>
                            <?php foreach ($vals as $av): ?>
                                <option value="<?= (int) $av['id'] ?>"><?= htmlspecialchars($av['value']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endforeach; ?>
                </div>
                <form id="add-cart-variant-form" method="post" action="<?= htmlspecialchars($baseUrl) ?>/sepet/ekle" class="hidden">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="product_variant_id" id="product_variant_id" value="">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($baseUrl) ?>/sepet">
                    <input type="hidden" name="quantity" value="1">
                </form>
            <?php endif; ?>
            <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/sepet/ekle" class="<?= $hasVariants ? 'hidden' : '' ?>" id="add-cart-simple-form">
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <input type="hidden" name="size" :value="selectedSize || ''">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($baseUrl) ?>/sepet">
                <input type="hidden" name="quantity" value="1">
            </form>
            <button type="button"
                id="add-cart-btn"
                @click.prevent="addToCart()"
                class="w-full bg-black text-white py-4 mt-8 uppercase tracking-widest text-xs font-bold hover:bg-gray-800 transition disabled:opacity-50 disabled:cursor-not-allowed">
                Sepete Ekle
            </button>
            <?php if ($hasVariants): ?>
                <p id="variant-please-select" class="text-[10px] text-gray-500 mt-2">Lütfen varyant seçin.</p>
                <script>
                (function(){
                    var variants = <?= json_encode(array_map(function($v){ return ['id' => (int)$v['id'], 'attribute_value_ids' => array_map('intval', $v['attribute_value_ids'] ?? []), 'stock' => (int)$v['stock']]; }, $productVariants)) ?>;
                    var selects = document.querySelectorAll('.variant-attr-select');
                    var variantInput = document.getElementById('product_variant_id');
                    var please = document.getElementById('variant-please-select');
                    function getSelectedIds(){ var ids = []; selects.forEach(function(s){ var v = s.value; if(v) ids.push(parseInt(v,10)); }); return ids.sort(function(a,b){ return a-b; }); }
                    function findVariant(){ var sel = getSelectedIds(); if(sel.length !== selects.length) return null; for(var i=0;i<variants.length;i++){ var vids = (variants[i].attribute_value_ids||[]).slice().sort(function(a,b){ return a-b; }); if(vids.length === sel.length && vids.every(function(v,j){ return v===sel[j]; })) return variants[i]; } return null; }
                    function update(){ var v = findVariant(); if(please) please.style.display = v ? 'none' : 'block'; if(v) variantInput.value = v.id; else variantInput.value = ''; }
                    selects.forEach(function(s){ s.addEventListener('change', update); });
                    update();
                })();
                </script>
            <?php endif; ?>
            <p class="text-[10px] text-gray-500 mt-3 text-center">Tahmini Teslimat: 2-4 İş Günü</p>

            <!-- 5. Accordion -->
            <div class="mt-10 border-t border-gray-100 pt-6" x-data="{ activeTab: null }">
                <div class="border-b border-gray-100">
                    <button type="button" @click="activeTab = activeTab === 0 ? null : 0" class="w-full py-4 flex items-center justify-between text-left text-xs uppercase tracking-widest text-secondary hover:text-primary transition">
                        <span>Ürün Açıklaması</span>
                        <span class="flex-shrink-0 ml-2" x-text="activeTab === 0 ? '−' : '+'"></span>
                    </button>
                    <div x-show="activeTab === 0" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="text-[11px] text-gray-500 leading-relaxed pb-4">
                        <?= nl2br(htmlspecialchars($product['description'] ?? 'Bu ürün özenle seçilmiş malzemelerle üretilmiştir. Zamansız tasarımı ile her sezon kullanılabilir.')) ?>
                    </div>
                </div>
                <div class="border-b border-gray-100">
                    <button type="button" @click="activeTab = activeTab === 1 ? null : 1" class="w-full py-4 flex items-center justify-between text-left text-xs uppercase tracking-widest text-secondary hover:text-primary transition">
                        <span>Materyal & Bakım</span>
                        <span class="flex-shrink-0 ml-2" x-text="activeTab === 1 ? '−' : '+'"></span>
                    </button>
                    <div x-show="activeTab === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="text-[11px] text-gray-500 leading-relaxed pb-4">
                        İpek karışımlı kumaş. Soğuk programda yıkayın, düşük ısıda ütüleyin. Ağartıcı kullanmayın.
                    </div>
                </div>
                <div class="border-b border-gray-100">
                    <button type="button" @click="activeTab = activeTab === 2 ? null : 2" class="w-full py-4 flex items-center justify-between text-left text-xs uppercase tracking-widest text-secondary hover:text-primary transition">
                        <span>Teslimat & İade</span>
                        <span class="flex-shrink-0 ml-2" x-text="activeTab === 2 ? '−' : '+'"></span>
                    </button>
                    <div x-show="activeTab === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="text-[11px] text-gray-500 leading-relaxed pb-4">
                        Siparişiniz 2-4 iş günü içinde kargoya verilir. 30 gün içinde koşulsuz iade hakkınız vardır.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php if (!empty($relatedProducts)): ?>
    <?php
    if (!function_exists('getLuminaImage')) {
        require_once (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3)) . '/includes/functions.php';
    }
    $relatedProductImages = $relatedProductImages ?? [];
    ?>
    <section class="pt-8 pb-20 border-t border-gray-100 max-w-[1400px] mx-auto px-4 md:px-8 lg:px-10">
        <h2 class="font-display text-2xl tracking-tight text-primary mb-8">Bunları da beğenebilirsiniz</h2>
        <ul class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-10 md:gap-x-8 md:gap-y-16">
            <?php foreach ($relatedProducts as $ri => $p): ?>
                <?php
                $rpPrice = (float) $p['price'];
                $rpSale = $p['sale_price'] !== null ? (float) $p['sale_price'] : null;
                $rpDisplay = $rpSale !== null && $rpSale > 0 ? $rpSale : $rpPrice;
                $rpImgPath = $relatedProductImages[$p['id']] ?? null;
                $rpImg1 = $rpImgPath ? ($baseUrl . '/' . $rpImgPath) : getLuminaImage('product', $ri % 6);
                $rpImg2 = getLuminaImage('hero', $ri % 4);
                ?>
                <li>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($p['slug']) ?>" class="group cursor-pointer block" aria-label="<?= htmlspecialchars($p['name']) ?>">
                        <div class="group relative overflow-hidden aspect-[3/4] bg-gray-100 mb-4">
                            <img src="<?= htmlspecialchars($rpImg1) ?>" alt="" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 group-hover:opacity-0" />
                            <img src="<?= htmlspecialchars($rpImg2) ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100" />
                            <span class="absolute top-2 left-2 text-[10px] bg-white px-2 py-1 tracking-widest uppercase text-primary">NEW</span>
                        </div>
                        <p class="text-sm font-medium text-primary mt-3"><?= htmlspecialchars($p['name']) ?></p>
                        <p class="text-xs text-secondary mt-1">₺<?= number_format($rpDisplay, 2, ',', '.') ?></p>
                        <div class="flex gap-1 mt-2">
                            <span class="w-3.5 h-3.5 rounded-full flex-shrink-0 border border-black/10 bg-[#0a0a0a]"></span>
                            <span class="w-3.5 h-3.5 rounded-full flex-shrink-0 border border-black/10 bg-[#c4a77d]"></span>
                            <span class="w-3.5 h-3.5 rounded-full flex-shrink-0 border border-black/10 bg-white"></span>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>
