<?php
use App\Models\Product;
$baseUrl = $baseUrl ?? '';
$productImagePaths = $productImagePaths ?? [];
$price = (float) ($product['price'] ?? 0);
$salePrice = $product['sale_price'] !== null && $product['sale_price'] !== '' ? (float) $product['sale_price'] : null;
// İndirimli fiyat varsa ve normal fiyattan küçükse indirimli fiyatı göster
$displayPrice = ($salePrice !== null && $salePrice > 0 && $salePrice < $price) ? $salePrice : $price;
$displayPriceFormatted = number_format($displayPrice, 2, ',', '.');
$hasSale = ($salePrice !== null && $salePrice > 0 && $salePrice < $price);
$hasVariants = !empty($productVariants);
$productVariants = $productVariants ?? [];
$attributesForVariant = $attributesForVariant ?? [];
$attributeValuesByAttr = $attributeValuesByAttr ?? [];
$colorImages = $colorImages ?? [];
$sizeAttributeId = $sizeAttributeId ?? null;
$availableSizes = $availableSizes ?? [];
$sizeStockByColor = $sizeStockByColor ?? [];
$relatedProducts = $relatedProducts ?? [];
$relatedProductImages = $relatedProductImages ?? [];
$relatedProductHasVariants = $relatedProductHasVariants ?? [];
$relatedProductColorImages = $relatedProductColorImages ?? [];
$relatedProductColorVariants = $relatedProductColorVariants ?? [];

// Renk attribute'unu bul
$colorAttributeId = null;
$colorAttribute = null;
foreach ($attributesForVariant as $attr) {
    if (($attr['type'] ?? '') === 'color') {
        $colorAttributeId = (int)$attr['id'];
        $colorAttribute = $attr;
        break;
    }
}

// Renk bazlı görselleri baseUrl ile hazırla
// Görsel yolları zaten /uploads/products/... şeklinde başlıyor, sadece başına / ekleyelim
$colorImagesWithUrls = [];
foreach ($colorImages as $colorId => $images) {
    $colorImagesWithUrls[$colorId] = array_map(function($path) {
        return '/' . ltrim($path, '/');
    }, $images);
}

// Galeri: ana ürün görselleri (attribute_value_id NULL olanlar)
$productImageUrls = [];
foreach ($productImagePaths as $path) {
    $productImageUrls[] = '/' . ltrim($path, '/');
}
$hasImages = !empty($productImageUrls);
$galleryJson = json_encode($productImageUrls, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($galleryJson === false) {
    $galleryJson = '[]';
}

// Renk bazlı görseller JSON (JavaScript için)
$colorImagesJson = json_encode($colorImagesWithUrls, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($colorImagesJson === false) {
    $colorImagesJson = '{}';
}

// Renkler JSON (JavaScript için)
$availableColorsJson = json_encode($colorAttributeId && isset($attributeValuesByAttr[$colorAttributeId]) ? $attributeValuesByAttr[$colorAttributeId] : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($availableColorsJson === false) {
    $availableColorsJson = '[]';
}

// Renk-beden kombinasyonlarına göre stok durumlarını hazırla
$sizeStockByColor = $sizeStockByColor ?? [];
$sizeStockByColorJson = json_encode($sizeStockByColor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($sizeStockByColorJson === false) {
    $sizeStockByColorJson = '{}';
}

// JSON'ları güvenli bir şekilde JavaScript değişkenlerine atayalım
$galleryJsonEscaped = htmlspecialchars($galleryJson, ENT_QUOTES, 'UTF-8');
$colorImagesJsonEscaped = htmlspecialchars($colorImagesJson, ENT_QUOTES, 'UTF-8');
$availableColorsJsonEscaped = htmlspecialchars($availableColorsJson, ENT_QUOTES, 'UTF-8');
?>
<script>
window.productGalleryData = <?= $galleryJson ?>;
window.productColorImagesData = <?= $colorImagesJson ?>;
window.productAvailableColorsData = <?= $availableColorsJson ?>;
window.productSizeStockByColorData = <?= $sizeStockByColorJson ?>;
window.productVariantsData = <?php
$variantsJson = json_encode(array_map(function($v){ 
    return [
        'id' => (int)$v['id'], 
        'attribute_value_ids' => array_map('intval', $v['attribute_value_ids'] ?? []), 
        'stock' => (int)$v['stock'],
        'price' => (float)$v['price'],
        'sale_price' => $v['sale_price'] !== null ? (float)$v['sale_price'] : null
    ]; 
}, $productVariants), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
echo $variantsJson !== false ? $variantsJson : '[]';
?>;
</script>
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
    <div class="md:col-span-6 w-full min-w-0" 
         x-data="{
            selectedIndex: 0,
            baseImages: window.productGalleryData || [],
            colorImages: window.productColorImagesData || {},
            selectedColorId: null,
            get images() {
                // Eğer renk seçildiyse ve o renk için görseller varsa, renk bazlı görselleri göster
                if (this.selectedColorId !== null && this.colorImages[this.selectedColorId] && this.colorImages[this.selectedColorId].length > 0) {
                    return this.colorImages[this.selectedColorId];
                }
                // Yoksa ana ürün görsellerini göster
                return this.baseImages;
            },
            next() { 
                if (this.images.length > 0) {
                    this.selectedIndex = (this.selectedIndex + 1) % this.images.length;
                }
            },
            prev() { 
                if (this.images.length > 0) {
                    this.selectedIndex = (this.selectedIndex - 1 + this.images.length) % this.images.length;
                }
            }
         }"
         @color-selected.window="selectedColorId = $event.detail.colorId; selectedIndex = 0;">
        <div class="relative w-full bg-gray-100 rounded-lg overflow-hidden aspect-[4/5] max-h-[560px] md:max-h-[600px]">
            <?php if ($hasImages || !empty($colorImages)): ?>
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
            <?php else: ?>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto text-gray-400 mb-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h18.75A2.25 2.25 0 0 0 21 18.75V8.25A2.25 2.25 0 0 0 18.75 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21Z" />
                        </svg>
                        <p class="text-sm text-gray-500">Görsel yok</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sağ: Ürün bilgileri – yarı yarıya (6/12), sticky masaüstü -->
    <div class="md:col-span-6 px-4 py-6 md:px-0 md:sticky md:top-24 md:h-fit">
        <div x-data="{
            selectedColorId: null,
            selectedColorName: null,
            selectedSize: null,
            selectedSizeId: null,
            sizeStockByColor: window.productSizeStockByColorData || {},
            get sizeStock() {
                // Seçili renge göre beden stok durumlarını döndür
                if (!this.selectedColorId || !this.sizeStockByColor[this.selectedColorId]) {
                    return {};
                }
                return this.sizeStockByColor[this.selectedColorId] || {};
            },
            buttonLabel: 'Sepete Ekle',
            addedFeedback: false,
            hasVariants: <?= $hasVariants ? 'true' : 'false' ?>,
            colorAttributeId: <?= $colorAttributeId ? $colorAttributeId : 'null' ?>,
            sizeAttributeId: <?= $sizeAttributeId ? $sizeAttributeId : 'null' ?>,
            availableColors: window.productAvailableColorsData || [],
            variants: window.productVariantsData || [],
            selectedVariantId: null,
            // Ana ürün fiyatları (PHP'den gelen)
            basePrice: <?= $price ?>,
            baseSalePrice: <?= $salePrice !== null ? $salePrice : 'null' ?>,
            // Dinamik fiyatlar (varyant seçildiğinde güncellenecek)
            get currentPrice() {
                if (!this.hasVariants || !this.selectedVariantId) {
                    return this.basePrice;
                }
                // Seçili varyantı bul
                var variant = this.variants.find(function(v) { return v.id === this.selectedVariantId; }.bind(this));
                if (variant && variant.price !== null && variant.price !== undefined) {
                    return variant.price;
                }
                return this.basePrice;
            },
            get currentSalePrice() {
                if (!this.hasVariants || !this.selectedVariantId) {
                    return this.baseSalePrice;
                }
                // Seçili varyantı bul
                var variant = this.variants.find(function(v) { return v.id === this.selectedVariantId; }.bind(this));
                if (variant && variant.sale_price !== null && variant.sale_price !== undefined && variant.sale_price > 0 && variant.sale_price < variant.price) {
                    return variant.sale_price;
                }
                return null;
            },
            get displayPrice() {
                var salePrice = this.currentSalePrice;
                if (salePrice !== null && salePrice > 0 && salePrice < this.currentPrice) {
                    return salePrice;
                }
                return this.currentPrice;
            },
            get hasSale() {
                var salePrice = this.currentSalePrice;
                return salePrice !== null && salePrice > 0 && salePrice < this.currentPrice;
            },
            get discountPercent() {
                if (!this.hasSale) return 0;
                return Math.round(((this.currentPrice - this.currentSalePrice) / this.currentPrice) * 100);
            },
            formatPrice(price) {
                return price.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            },
            init() {
                // İlk renk seçiliyse onu seç
                if (this.availableColors.length > 0) {
                    this.selectColor(this.availableColors[0].id, this.availableColors[0].value);
                }
                // Varyant değiştiğinde kontrol et
                this.$watch('selectedColorId', () => {
                    this.findVariant();
                    // Renk değiştiğinde seçili bedeni sıfırla (stok durumu değişebilir)
                    this.selectedSize = null;
                    this.selectedSizeId = null;
                });
                this.$watch('selectedSizeId', () => this.findVariant());
                // Varyant değiştiğinde fiyat otomatik güncellenecek (computed property sayesinde)
            },
            selectColor(colorId, colorName) {
                this.selectedColorId = colorId;
                this.selectedColorName = colorName;
                // Galeri görsellerini güncellemek için event gönder
                window.dispatchEvent(new CustomEvent('color-selected', { detail: { colorId: colorId } }));
                // Renk değiştiğinde seçili bedeni sıfırla
                this.selectedSize = null;
                this.selectedSizeId = null;
                this.findVariant();
            },
            selectSize(sizeValue, sizeId) {
                this.selectedSize = sizeValue;
                this.selectedSizeId = sizeId;
                this.findVariant();
            },
            findVariant() {
                if (!this.hasVariants || !this.selectedColorId || !this.selectedSizeId) {
                    this.selectedVariantId = null;
                    return;
                }
                var selectedIds = [this.selectedColorId, this.selectedSizeId].sort(function(a,b){ return a-b; });
                for (var i = 0; i < this.variants.length; i++) {
                    var vids = (this.variants[i].attribute_value_ids || []).slice().sort(function(a,b){ return a-b; });
                    if (vids.length === selectedIds.length && vids.every(function(v, j){ return v === selectedIds[j]; })) {
                        this.selectedVariantId = this.variants[i].id;
                        return;
                    }
                }
                this.selectedVariantId = null;
            },
            addToCart() {
                var form = document.getElementById(this.hasVariants ? 'add-cart-variant-form' : 'add-cart-simple-form');
                if (this.hasVariants) {
                    if (!this.selectedVariantId) {
                        this.$dispatch('notify', { message: 'Lütfen renk ve beden seçin.' });
                        return;
                    }
                    var variantEl = document.getElementById('product_variant_id');
                    if (variantEl) {
                        variantEl.value = this.selectedVariantId;
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
                <template x-if="hasSale && discountPercent > 0">
                    <span class="text-[10px] bg-red-900 text-white px-2 py-1 tracking-widest uppercase">%<span x-text="discountPercent"></span> İndirim</span>
                </template>
            </div>

            <!-- 1. Başlık, Favori ve Fiyat -->
            <div class="flex items-start justify-between gap-4 mb-2">
                <h1 class="font-display text-3xl md:text-4xl text-primary tracking-tight flex-1"><?= htmlspecialchars($product['name']) ?></h1>
                <?php if (!empty($userId)): ?>
                    <div x-data="{
                        inWishlist: <?= $isInWishlist ? 'true' : 'false' ?>,
                        loading: false,
                        toggleWishlist() {
                            if (this.loading) return;
                            
                            this.loading = true;
                            const url = this.inWishlist ? '<?= htmlspecialchars($baseUrl) ?>/favori/sil' : '<?= htmlspecialchars($baseUrl) ?>/favori/ekle';
                            const formData = new FormData();
                            formData.append('product_id', '<?= (int) $product['id'] ?>');
                            formData.append('redirect', '<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($product['slug']) ?>');
                            
                            const self = this;
                            fetch(url, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                self.loading = false;
                                if (data.success) {
                                    self.inWishlist = data.inWishlist;
                                    self.$dispatch('notify', { message: data.message || (data.inWishlist ? 'Ürün favorilere eklendi.' : 'Ürün favorilerden çıkarıldı.') });
                                    // Header'daki favori sayısını güncelle
                                    if (typeof data.count !== 'undefined') {
                                        self.$dispatch('wishlist-updated', { count: data.count });
                                    }
                                } else {
                                    self.$dispatch('notify', { message: data.message || 'Bir hata oluştu.' });
                                }
                            })
                            .catch(function() {
                                self.loading = false;
                                self.$dispatch('notify', { message: 'Bir hata oluştu.' });
                            });
                        }
                    }" class="flex-shrink-0">
                        <button type="button" 
                                @click="toggleWishlist()"
                                :disabled="loading"
                                class="p-2 transition"
                                :class="inWishlist ? 'text-red-600 hover:text-red-700' : 'text-secondary hover:text-primary'"
                                :aria-label="inWishlist ? 'Favorilerden çıkar' : 'Favorilere ekle'"
                                :title="inWishlist ? 'Favorilerden çıkar' : 'Favorilere ekle'">
                            <svg x-show="!loading && inWishlist" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" /></svg>
                            <svg x-show="!loading && !inWishlist" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                            <svg x-show="loading" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 animate-spin">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.25 9.348h4.992m4.992 0a3.001 3.001 0 0 0 3.75-3.75 3.001 3.001 0 0 0-3.75 3.75m-9 0h4.992m-4.992 0a3.001 3.001 0 0 1-3.75-3.75 3.001 3.001 0 0 1 3.75 3.75m-9 0v4.992m0 0a3.001 3.001 0 0 0 3.75 3.75h4.992m-4.992 0a3.001 3.001 0 0 1-3.75-3.75h4.992m0 0v-4.992m0 0a3.001 3.001 0 0 1 3.75-3.75h4.992m-4.992 0a3.001 3.001 0 0 0-3.75 3.75v4.992" />
                            </svg>
                        </button>
                    </div>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/giris?redirect=<?= urlencode($baseUrl . '/urun/' . $product['slug']) ?>" class="flex-shrink-0 p-2 text-secondary hover:text-primary transition" aria-label="Favorilere eklemek için giriş yapın" title="Favorilere ekle (giriş gerekli)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                    </a>
                <?php endif; ?>
            </div>
            <div class="mb-6">
                <p class="text-lg text-secondary font-light">
                    <template x-if="hasSale">
                        <span>
                            <span class="text-rose-600">₺<span x-text="formatPrice(displayPrice)"></span></span>
                            <span class="ml-2 text-sm text-gray-400 line-through">₺<span x-text="formatPrice(currentPrice)"></span></span>
                        </span>
                    </template>
                    <template x-if="!hasSale">
                        <span>₺<span x-text="formatPrice(displayPrice)"></span></span>
                    </template>
                </p>
            </div>

            <!-- 2. Renk Seçimi -->
            <?php if ($colorAttributeId && !empty($attributeValuesByAttr[$colorAttributeId])): ?>
                <p class="text-xs uppercase tracking-widest text-gray-500 mb-3">
                    Renk: <span x-text="selectedColorName || 'Seçin'"></span>
                </p>
                <div class="flex flex-wrap gap-2 mb-6">
                    <?php foreach ($attributeValuesByAttr[$colorAttributeId] as $colorValue): ?>
                        <?php
                        $colorId = (int)$colorValue['id'];
                        $colorName = htmlspecialchars($colorValue['value']);
                        $colorHex = $colorValue['color_hex'] ?? '#cccccc';
                        $hasColorImages = isset($colorImages[$colorId]) && !empty($colorImages[$colorId]);
                        ?>
                        <button type="button" 
                                @click="selectColor(<?= $colorId ?>, '<?= addslashes($colorValue['value']) ?>')"
                                class="w-8 h-8 rounded-full border border-black/10 flex-shrink-0 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition"
                                :class="{ 'ring-1 ring-black ring-offset-2': selectedColorId === <?= $colorId ?> }"
                                style="background-color: <?= htmlspecialchars($colorHex) ?>; filter: saturate(0.75) brightness(1.05) sepia(0.1);"
                                aria-label="<?= $colorName ?>"
                                title="<?= $colorName ?>">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- 3. Beden Seçimi -->
            <?php if (!$hasVariants && !empty($availableSizes)): ?>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs uppercase tracking-widest text-gray-500">Beden</span>
                    <a href="#" class="text-[10px] text-secondary underline hover:text-primary transition">Beden Tablosu</a>
                </div>
                <div class="grid grid-cols-5 gap-2 mb-6">
                    <?php 
                    // Sadece $availableSizes içindeki bedenleri göster (panelden eklenen bedenler)
                    // Stok durumuna göre aktif/pasif olacak (Alpine.js'te dinamik olarak hesaplanıyor)
                    foreach ($availableSizes as $sizeData): 
                        $size = $sizeData['value'];
                        $sizeId = $sizeData['id'];
                    ?>
                        <button type="button"
                            @click="if (!sizeStock['<?= $size ?>']) return; selectSize('<?= $size ?>', <?= $sizeId ?>)"
                            class="border py-3 text-xs text-center transition cursor-pointer"
                            :class="!sizeStock['<?= $size ?>'] ? 'text-gray-300 line-through cursor-not-allowed border-gray-100 bg-gray-50' : (selectedSize === '<?= $size ?>' ? 'bg-black text-white border-black' : 'border-gray-200 hover:border-black text-primary')"
                            :disabled="!sizeStock['<?= $size ?>']"
                        ><?= htmlspecialchars($size) ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- 3. Beden Seçimi (Varyantlı ürünler için) -->
            <?php if ($hasVariants && !empty($availableSizes)): ?>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs uppercase tracking-widest text-gray-500">Beden</span>
                    <a href="#" class="text-[10px] text-secondary underline hover:text-primary transition">Beden Tablosu</a>
                </div>
                <div class="grid grid-cols-5 gap-2 mb-6">
                    <?php 
                    // Sadece $availableSizes içindeki bedenleri göster (panelden eklenen bedenler)
                    // Stok durumuna göre aktif/pasif olacak (Alpine.js'te dinamik olarak hesaplanıyor)
                    foreach ($availableSizes as $sizeData): 
                        $size = $sizeData['value'];
                        $sizeId = $sizeData['id'];
                    ?>
                        <button type="button"
                            @click="if (!sizeStock['<?= $size ?>']) return; selectSize('<?= $size ?>', <?= $sizeId ?>)"
                            class="border py-3 text-xs text-center transition cursor-pointer"
                            :class="!sizeStock['<?= $size ?>'] ? 'text-gray-300 line-through cursor-not-allowed border-gray-100 bg-gray-50' : (selectedSize === '<?= $size ?>' ? 'bg-black text-white border-black' : 'border-gray-200 hover:border-black text-primary')"
                            :disabled="!sizeStock['<?= $size ?>']"
                        ><?= htmlspecialchars($size) ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- 4. Sepete Ekle -->
            <?php if ($hasVariants): ?>
                <!-- Varyantlı ürünler için select'ler (gizli, JavaScript ile kullanılacak) -->
                <div id="variant-selectors" class="hidden">
                    <?php foreach ($attributesForVariant as $attr): ?>
                        <?php $vals = $attributeValuesByAttr[$attr['id']] ?? []; if (empty($vals)) continue; ?>
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
                <?php if (!empty($product['material_care'])): ?>
                <div class="border-b border-gray-100">
                    <button type="button" @click="activeTab = activeTab === 1 ? null : 1" class="w-full py-4 flex items-center justify-between text-left text-xs uppercase tracking-widest text-secondary hover:text-primary transition">
                        <span>Materyal & Bakım</span>
                        <span class="flex-shrink-0 ml-2" x-text="activeTab === 1 ? '−' : '+'"></span>
                    </button>
                    <div x-show="activeTab === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="text-[11px] text-gray-500 leading-relaxed pb-4">
                        <?= nl2br(htmlspecialchars($product['material_care'])) ?>
                    </div>
                </div>
                <?php endif; ?>
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
    $relatedProductImages = $relatedProductImages ?? [];
    ?>
    <section class="pt-8 pb-20 border-t border-gray-100 max-w-[1400px] mx-auto px-4 md:px-8 lg:px-10">
        <h2 class="font-display text-2xl tracking-tight text-primary mb-8">Bunları da beğenebilirsiniz</h2>
        <ul class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-10 md:gap-x-8 md:gap-y-16">
            <?php foreach ($relatedProducts as $ri => $p): ?>
                <?php
                $rpPrice = (float) $p['price'];
                $rpSale = $p['sale_price'] !== null && $p['sale_price'] !== '' ? (float) $p['sale_price'] : null;
                $rpHasSale = ($rpSale !== null && $rpSale > 0 && $rpSale < $rpPrice);
                $rpDisplay = $rpHasSale ? $rpSale : $rpPrice;
                $rpDiscountPercent = $rpHasSale ? (int) round((($rpPrice - $rpSale) / $rpPrice) * 100) : 0;
                
                // Görselleri al
                $rpMainImgPath = $relatedProductImages[$p['id']] ?? null;
                $rpImg1 = null;
                $rpImg2 = null;
                if ($rpMainImgPath) {
                    $rpAllImages = Product::getImages($p['id']);
                    $rpImg1 = $baseUrl . '/' . ($rpAllImages[0] ?? $rpMainImgPath);
                    $rpImg2 = isset($rpAllImages[1]) ? ($baseUrl . '/' . $rpAllImages[1]) : null;
                }
                
                $rpHasVariants = isset($relatedProductHasVariants[$p['id']]) && $relatedProductHasVariants[$p['id']];
                
                // Renk varyantlarını ve görsellerini hazırla
                $rpColorVariants = $relatedProductColorVariants[$p['id']] ?? [];
                $rpColorImagesData = $relatedProductColorImages[$p['id']] ?? [];
                $rpColorImagesWithUrls = [];
                $rpFirstColorId = null;
                foreach ($rpColorVariants as $color) {
                    $colorId = (int)$color['id'];
                    if ($rpFirstColorId === null) {
                        $rpFirstColorId = $colorId;
                    }
                    if (isset($rpColorImagesData[$colorId]) && !empty($rpColorImagesData[$colorId])) {
                        $rpColorImagesWithUrls[$colorId] = array_map(function($path) use ($baseUrl) {
                            return $baseUrl . '/' . $path;
                        }, $rpColorImagesData[$colorId]);
                    }
                }
                
                // İlk renk için hover görseli (2. görsel varsa)
                $rpHoverImg = null;
                if ($rpFirstColorId && isset($rpColorImagesWithUrls[$rpFirstColorId]) && count($rpColorImagesWithUrls[$rpFirstColorId]) > 1) {
                    $rpHoverImg = $rpColorImagesWithUrls[$rpFirstColorId][1];
                } elseif ($rpImg2) {
                    $rpHoverImg = $rpImg2;
                }
                
                $rpCardData = [
                    'currentImg' => $rpImg1,
                    'hoverImg' => $rpHoverImg,
                    'selectedColorId' => $rpFirstColorId,
                    'colorImages' => $rpColorImagesWithUrls,
                    'hasColorImages' => !empty($rpColorImagesWithUrls),
                ];
                ?>
                <li>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($p['slug']) ?>" class="cursor-pointer block" aria-label="<?= htmlspecialchars($p['name']) ?>"
                        x-data="<?= htmlspecialchars(json_encode($rpCardData), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <div class="group relative overflow-hidden aspect-[3/4] bg-gray-100 mb-4"
                             :class="{ 'group-hover:scale-105': !hoverImg }"
                             style="transition: transform 0.5s ease;">
                            <?php if ($rpImg1): ?>
                                <img :src="currentImg" alt="<?= htmlspecialchars($p['name']) ?>" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 group-hover:opacity-0" />
                                <template x-if="hoverImg">
                                    <img :src="hoverImg" alt="<?= htmlspecialchars($p['name']) ?>" class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100" />
                                </template>
                            <?php else: ?>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-gray-400 mb-1">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h18.75A2.25 2.25 0 0 0 21 18.75V8.25A2.25 2.25 0 0 0 18.75 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21Z" />
                                        </svg>
                                        <p class="text-xs text-gray-500">Görsel yok</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ((int)($p['is_new'] ?? 0) === 1): ?>
                                <span class="absolute top-2 left-2 text-[10px] bg-white px-2 py-1 tracking-widest uppercase text-primary">NEW</span>
                            <?php endif; ?>
                            <?php if ($rpDiscountPercent > 0): ?>
                                <span class="absolute top-2 right-2 text-[10px] bg-red-900 text-white px-2 py-1 uppercase tracking-widest">%<?= $rpDiscountPercent ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm font-medium text-primary mt-3"><?= htmlspecialchars($p['name']) ?></p>
                        <p class="text-xs text-secondary mt-1">
                            <?php if ($rpHasSale): ?>
                                <span class="text-rose-600">₺<?= number_format($rpDisplay, 2, ',', '.') ?></span>
                                <span class="ml-1 text-gray-400 line-through text-[10px]">₺<?= number_format($rpPrice, 2, ',', '.') ?></span>
                            <?php else: ?>
                                ₺<?= number_format($rpDisplay, 2, ',', '.') ?>
                            <?php endif; ?>
                        </p>
                        <?php if ($rpHasVariants && !empty($rpColorVariants)): ?>
                            <div class="flex gap-1 mt-2" @click.prevent.stop>
                                <?php foreach ($rpColorVariants as $color): ?>
                                    <?php
                                    $colorId = (int)$color['id'];
                                    $colorName = htmlspecialchars($color['value']);
                                    $colorHex = $color['color_hex'] ?? '#cccccc';
                                    $hasColorImgs = isset($rpColorImagesWithUrls[$colorId]) && !empty($rpColorImagesWithUrls[$colorId]);
                                    // Renk bazlı görseller varsa onları kullan, yoksa ana ürün görsellerini kullan
                                    $colorFirstImg = $hasColorImgs ? $rpColorImagesWithUrls[$colorId][0] : ($rpImg1 ?? '');
                                    $colorHoverImg = null;
                                    if ($hasColorImgs && count($rpColorImagesWithUrls[$colorId]) > 1) {
                                        $colorHoverImg = $rpColorImagesWithUrls[$colorId][1];
                                    } elseif (!$hasColorImgs && $rpImg2) {
                                        $colorHoverImg = $rpImg2;
                                    }
                                    // Null kontrolü ile htmlspecialchars
                                    $colorFirstImgEscaped = $colorFirstImg ? htmlspecialchars($colorFirstImg, ENT_QUOTES) : '';
                                    $colorHoverImgEscaped = $colorHoverImg ? htmlspecialchars($colorHoverImg, ENT_QUOTES) : '';
                                    ?>
                                    <button type="button" 
                                            aria-label="<?= $colorName ?>"
                                            class="w-3.5 h-3.5 rounded-full flex-shrink-0 border border-black/10 cursor-pointer focus:outline-none focus:ring-1 focus:ring-black transition"
                                            :class="{ 'ring-1 ring-black': selectedColorId === <?= $colorId ?> }"
                                            style="background-color: <?= htmlspecialchars($colorHex) ?>; filter: saturate(0.75) brightness(1.05) sepia(0.1);"
                                            @click.prevent="selectedColorId = <?= $colorId ?>; currentImg = '<?= $colorFirstImgEscaped ?>'; hoverImg = <?= $colorHoverImgEscaped ? "'" . $colorHoverImgEscaped . "'" : 'null' ?>;">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>
