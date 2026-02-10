<?php
$baseUrl = $baseUrl ?? '';
$product = $product ?? null;
$categories = $categories ?? [];
$brands = $brands ?? [];
$attributes = $attributes ?? [];
$attributeValuesByAttr = $attributeValuesByAttr ?? [];
$productImages = $productImages ?? [];
$colorImages = $colorImages ?? []; // Renk bazlı fotoğraflar
$productVariants = $productVariants ?? [];
$errors = $errors ?? [];
$old = $old ?? [];
$isEdit = !empty($product);
$formAction = $isEdit ? $baseUrl . '/admin/products/edit?id=' . (int)$product['id'] : $baseUrl . '/admin/products/create';

// Renk attribute ID'sini bul
$colorAttributeId = null;
foreach ($attributes as $attr) {
    if (($attr['type'] ?? '') === 'color') {
        $colorAttributeId = (int)$attr['id'];
        break;
    }
}

// Mevcut varyantlarda kullanılan renk ID'lerini bul (sadece düzenleme modunda)
$usedColorIds = [];
if ($isEdit && !empty($productVariants) && $colorAttributeId) {
    foreach ($productVariants as $variant) {
        foreach ($variant['attribute_value_ids'] ?? [] as $avId) {
            // Bu ID'nin renk attribute'una ait olup olmadığını kontrol et
            foreach ($attributeValuesByAttr[$colorAttributeId] ?? [] as $av) {
                if ($av['id'] == $avId) {
                    if (!in_array($avId, $usedColorIds)) {
                        $usedColorIds[] = $avId;
                    }
                    break;
                }
            }
        }
    }
}

// Attribute'ları sırala: önce renk (type='color'), sonra diğerleri
$sortedAttributes = [];
$colorAttribute = null;
foreach ($attributes as $attr) {
    if (($attr['type'] ?? '') === 'color') {
        $colorAttribute = $attr;
    } else {
        $sortedAttributes[] = $attr;
    }
}
// Renk attribute'unu başa ekle
if ($colorAttribute) {
    array_unshift($sortedAttributes, $colorAttribute);
} else {
    // Renk attribute yoksa, mevcut sıralamayı kullan
    $sortedAttributes = $attributes;
}
?>
<style>
[x-cloak] { display: none !important; }
</style>
<div class="space-y-6" x-data="productForm()">
    <!-- Başlık -->
    <div>
        <h1 class="text-3xl font-light tracking-tight text-stone-800"><?= $isEdit ? 'Ürün Düzenle' : 'Yeni Ürün' ?></h1>
        <p class="mt-1 text-sm text-stone-500"><?= $isEdit ? 'Ürün bilgilerini güncelleyin' : 'Yeni ürün ekleyin' ?></p>
    </div>

    <!-- Hata Mesajları -->
    <?php if (!empty($errors)): ?>
        <div class="rounded-lg border border-rose-200 bg-rose-50 p-4">
            <ul class="list-disc list-inside space-y-1 text-sm text-rose-800">
                <?php foreach ($errors as $error): ?>
                    <li><?= is_array($error) ? implode(', ', $error) : htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars($formAction) ?>" enctype="multipart/form-data" @submit.prevent="submitForm($event)">
        <!-- Sekmeler -->
        <div class="border-b border-stone-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button type="button"
                        @click="activeTab = 'general'"
                        :class="activeTab === 'general' ? 'border-stone-900 text-stone-900' : 'border-transparent text-stone-400 hover:border-stone-300 hover:text-stone-600'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                    Genel
                </button>
                <button type="button"
                        @click="activeTab = 'seo'"
                        :class="activeTab === 'seo' ? 'border-stone-900 text-stone-900' : 'border-transparent text-stone-400 hover:border-stone-300 hover:text-stone-600'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                    SEO
                </button>
            </nav>
        </div>

        <!-- Sekme İçerikleri -->
        <div class="mt-6">
            <!-- GENEL SEKME -->
            <div x-show="activeTab === 'general'" x-cloak x-transition>
                <div class="space-y-4 rounded-xl border border-stone-200 bg-[#FAFAF9] p-4 shadow-sm">
                    <!-- Ürün Adı, Slug ve Kategori -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label for="name" class="mb-1 block text-xs font-medium text-stone-700">Ürün Adı <span class="text-rose-600">*</span></label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   required
                                   value="<?= htmlspecialchars($old['name'] ?? $product['name'] ?? '') ?>"
                                   @input="autoSlug()"
                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                            <?php if (isset($errors['name'])): ?>
                                <p class="mt-0.5 text-xs text-rose-600"><?= htmlspecialchars($errors['name']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="slug" class="mb-1 block text-xs font-medium text-stone-700">Slug</label>
                            <input type="text" 
                                   id="slug" 
                                   name="slug" 
                                   x-model="slug"
                                   @input="slugManuallyChanged = true"
                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                        </div>
                        <div>
                            <label for="category_id" class="mb-1 block text-xs font-medium text-stone-700">Kategori</label>
                            <select id="category_id" 
                                    name="category_id"
                                    class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                <option value="">— Seçiniz —</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= (int) $c['id'] ?>" <?= (string)($old['category_id'] ?? $product['category_id'] ?? '') === (string)$c['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Detaylı Açıklama -->
                    <div>
                        <label for="description" class="mb-1 block text-xs font-medium text-stone-700">Detaylı Açıklama</label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="5"
                                  class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800"><?= htmlspecialchars($old['description'] ?? $product['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Düşük Stok Eşiği ve Sıra -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="low_stock_threshold" class="mb-1 block text-xs font-medium text-stone-700">Düşük Stok Eşiği</label>
                            <input type="number" 
                                   id="low_stock_threshold" 
                                   name="low_stock_threshold" 
                                   min="0"
                                   value="<?= htmlspecialchars($old['low_stock_threshold'] ?? $product['low_stock_threshold'] ?? '5') ?>"
                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                        </div>
                        <div>
                            <label for="sort_order" class="mb-1 block text-xs font-medium text-stone-700">Sıra</label>
                            <input type="number" 
                                   id="sort_order" 
                                   name="sort_order" 
                                   value="<?= htmlspecialchars($old['sort_order'] ?? $product['sort_order'] ?? '0') ?>"
                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                        </div>
                    </div>

                    <!-- Materyal & Bakım -->
                    <div>
                        <label for="material_care" class="mb-1 block text-xs font-medium text-stone-700">Materyal & Bakım</label>
                        <textarea id="material_care" 
                                  name="material_care" 
                                  rows="4"
                                  placeholder="Örn: İpek karışımlı kumaş. Soğuk programda yıkayın, düşük ısıda ütüleyin. Ağartıcı kullanmayın."
                                  class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800"><?= htmlspecialchars($old['material_care'] ?? $product['material_care'] ?? '') ?></textarea>
                        <p class="mt-1 text-xs text-stone-500">Bu bilgiler ürün detay sayfasında gösterilecektir. Boş bırakılırsa gösterilmez.</p>
                    </div>

                    <!-- Durumlar -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-stone-700">Durumlar</label>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-1.5">
                                <input type="checkbox" 
                                       name="is_featured" 
                                       value="1"
                                       <?= (int)($old['is_featured'] ?? $product['is_featured'] ?? 0) ? 'checked' : '' ?>
                                       class="h-3.5 w-3.5 rounded border-stone-300 text-stone-900 focus:ring-stone-400">
                                <span class="text-xs text-stone-700">Öne Çıkan</span>
                            </label>
                            <label class="flex items-center gap-1.5">
                                <input type="checkbox" 
                                       name="is_new" 
                                       value="1"
                                       <?= (int)($old['is_new'] ?? $product['is_new'] ?? 0) ? 'checked' : '' ?>
                                       class="h-3.5 w-3.5 rounded border-stone-300 text-stone-900 focus:ring-stone-400">
                                <span class="text-xs text-stone-700">Yeni</span>
                            </label>
                            <label class="flex items-center gap-1.5">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1"
                                       <?= (int)($old['is_active'] ?? $product['is_active'] ?? 1) ? 'checked' : '' ?>
                                       class="h-3.5 w-3.5 rounded border-stone-300 text-stone-900 focus:ring-stone-400">
                                <span class="text-xs text-stone-700">Aktif</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Renk ve Varyant Kartı -->
                <div class="mt-6 rounded-xl border border-stone-200 bg-[#FAFAF9] p-4 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-stone-800">Renk ve Varyant</h3>
                            <p class="mt-1 text-sm text-stone-500">Beden, renk ve stok bilgilerini ekleyin</p>
                        </div>
                    </div>
                <div class="space-y-4 rounded-xl border border-stone-200 bg-[#FAFAF9] p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-stone-800">Ürün Varyantları</h3>
                            <p class="mt-1 text-sm text-stone-500">Beden, renk ve stok bilgilerini ekleyin</p>
                        </div>
                    </div>

                    <!-- Mod Seçimi -->
                    <div class="rounded-lg border border-stone-200 bg-white p-4">
                        <label class="mb-2 block text-sm font-medium text-stone-700">Varyant Ekleme Modu</label>
                        <div class="flex gap-4">
                            <label class="flex cursor-pointer items-center gap-2">
                                <input type="radio" 
                                       name="variant_mode" 
                                       value="quick"
                                       x-model="variantMode"
                                       class="h-4 w-4 border-stone-300 text-stone-900 focus:ring-stone-400">
                                <span class="text-sm text-stone-700">Hızlı Ekleme (Kombinasyon Matrisi)</span>
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Önerilen</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2">
                                <input type="radio" 
                                       name="variant_mode" 
                                       value="classic"
                                       x-model="variantMode"
                                       class="h-4 w-4 border-stone-300 text-stone-900 focus:ring-stone-400">
                                <span class="text-sm text-stone-700">Tek Tek Ekleme (Klasik)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Mevcut Varyantlar (Kompakt Tablo Görünümü) -->
                    <?php if (!empty($productVariants)): ?>
                        <div class="rounded-lg border border-stone-200 bg-white p-4" x-show="existingVariantsCount > 0" x-transition>
                            <div class="mb-4 flex items-center justify-between">
                                <h4 class="text-sm font-medium text-stone-800">Mevcut Varyantlar (<span x-text="existingVariantsCount"></span> adet)</h4>
                                <div class="flex gap-2">
                                    <button type="button"
                                            @click="deleteSelectedExistingVariants()"
                                            :disabled="selectedExistingVariants.length === 0"
                                            :class="selectedExistingVariants.length === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                                            class="rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-xs font-medium text-rose-700 transition-colors hover:bg-rose-50 disabled:bg-stone-100">
                                        Seçilenleri Sil (<span x-text="selectedExistingVariants.length"></span>)
                                    </button>
                                    <button type="button"
                                            @click="deleteAllExistingVariants()"
                                            :disabled="existingVariantsCount === 0"
                                            :class="existingVariantsCount === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                                            class="rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-xs font-medium text-rose-700 transition-colors hover:bg-rose-50 disabled:bg-stone-100">
                                        Tümünü Sil
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Mevcut Varyantlar İçin Renk Bazlı Fotoğraf Ekleme -->
                            <?php if (!empty($colorAttributeId)): ?>
                                <?php
                                // Mevcut varyantlardan renk ID'lerini topla
                                $existingColorIds = [];
                                foreach ($productVariants as $variant) {
                                    foreach ($variant['attribute_value_ids'] ?? [] as $avId) {
                                        foreach ($attributeValuesByAttr[$colorAttributeId] ?? [] as $av) {
                                            if ($av['id'] == $avId) {
                                                if (!in_array($avId, $existingColorIds)) {
                                                    $existingColorIds[] = $avId;
                                                }
                                                break;
                                            }
                                        }
                                    }
                                }
                                ?>
                                <?php if (!empty($existingColorIds)): ?>
                                    <div class="mb-4 rounded-lg border border-stone-200 bg-stone-50 p-4">
                                        <h5 class="mb-3 text-sm font-medium text-stone-800">Renk Bazlı Fotoğraflar (Mevcut Varyantlar)</h5>
                                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                                            <?php foreach ($existingColorIds as $colorId): ?>
                                                <?php
                                                $colorValue = '';
                                                foreach ($attributeValuesByAttr[$colorAttributeId] ?? [] as $av) {
                                                    if ($av['id'] == $colorId) {
                                                        $colorValue = $av['value'];
                                                        break;
                                                    }
                                                }
                                                ?>
                                                <div class="rounded-lg border border-stone-200 bg-white p-3">
                                                    <div class="mb-2 flex items-center justify-between">
                                                        <span class="text-xs font-medium text-stone-700"><?= htmlspecialchars($colorValue) ?></span>
                                                        <button type="button"
                                                                @click="showColorImageModal = <?= (int)$colorId ?>"
                                                                class="rounded border border-stone-300 bg-white px-2 py-1 text-xs text-stone-700 hover:bg-stone-50">
                                                            Fotoğraf Ekle
                                                        </button>
                                                    </div>
                                                    <div x-show="colorImages[<?= (int)$colorId ?>] && colorImages[<?= (int)$colorId ?>].length > 0" class="mt-2 flex gap-1 overflow-x-auto">
                                                        <template x-for="(img, imgIndex) in colorImages[<?= (int)$colorId ?>]" :key="imgIndex">
                                                            <div class="group relative h-16 w-16 shrink-0 overflow-hidden rounded border border-stone-200">
                                                                <img :src="img.preview" class="h-full w-full object-cover" :alt="img.name">
                                                                <button type="button"
                                                                        @click="removeColorImage(<?= (int)$colorId ?>, imgIndex)"
                                                                        class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 transition-opacity group-hover:opacity-100">
                                                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <p x-show="!colorImages[<?= (int)$colorId ?>] || colorImages[<?= (int)$colorId ?>].length === 0" 
                                                       class="mt-2 text-xs text-stone-400">Henüz fotoğraf yok</p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <!-- Seçim Kontrolü -->
                            <div class="mb-3 flex items-center gap-3 rounded-lg border border-stone-200 bg-stone-50 p-2">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           @change="toggleSelectAllExistingVariants($event.target.checked)"
                                           class="h-4 w-4 border-stone-300 text-stone-900 focus:ring-stone-400">
                                    <span class="text-xs text-stone-700">Tümünü Seç</span>
                                </label>
                                <span class="text-xs text-stone-500" x-show="selectedExistingVariants.length > 0">
                                    <span x-text="selectedExistingVariants.length"></span> varyant seçili
                                </span>
                            </div>

                            <!-- Kompakt Tablo Görünümü -->
                            <div class="overflow-x-auto rounded-lg border border-stone-200 bg-white">
                                <table class="min-w-full divide-y divide-stone-200">
                                    <thead class="bg-stone-50">
                                        <tr>
                                            <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-stone-700 w-10">
                                                <input type="checkbox" 
                                                       @change="toggleSelectAllExistingVariants($event.target.checked)"
                                                       class="h-4 w-4 border-stone-300 text-stone-900 focus:ring-stone-400">
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-stone-700">SKU</th>
                                            <?php foreach ($sortedAttributes as $attr): ?>
                                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-stone-700"><?= htmlspecialchars($attr['name']) ?></th>
                                            <?php endforeach; ?>
                                            <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider text-stone-700">Stok</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider text-stone-700">Fiyat</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider text-stone-700">İndirimli</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-stone-700">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-stone-200 bg-white">
                                        <?php foreach ($productVariants as $idx => $variant): ?>
                                            <tr x-show="!deletedExistingVariants.includes(<?= (int)$variant['id'] ?>)"
                                                x-transition
                                                class="hover:bg-stone-50 transition-colors"
                                                :class="selectedExistingVariants.includes(<?= (int)$variant['id'] ?>) ? 'bg-stone-100' : ''">
                                                <td class="px-3 py-2 text-center">
                                                    <input type="checkbox" 
                                                           :checked="selectedExistingVariants.includes(<?= (int)$variant['id'] ?>)"
                                                           @change="toggleExistingVariantSelection(<?= (int)$variant['id'] ?>)"
                                                           class="h-4 w-4 border-stone-300 text-stone-900 focus:ring-stone-400">
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-2 text-xs text-stone-800">
                                                    <span x-show="editingExistingVariantId !== <?= (int)$variant['id'] ?>"><?= htmlspecialchars($variant['sku']) ?></span>
                                                    <input type="text" 
                                                           x-show="editingExistingVariantId === <?= (int)$variant['id'] ?>"
                                                           name="existing_variants[<?= (int)$variant['id'] ?>][sku]"
                                                           value="<?= htmlspecialchars($variant['sku']) ?>"
                                                           class="w-full rounded-lg border border-stone-200 bg-white px-2 py-1 text-xs text-stone-800">
                                                </td>
                                                <?php foreach ($sortedAttributes as $attr): ?>
                                                    <td class="whitespace-nowrap px-3 py-2 text-xs text-stone-800">
                                                        <span x-show="editingExistingVariantId !== <?= (int)$variant['id'] ?>">
                                                            <?php
                                                            $attrValue = '';
                                                            foreach ($variant['attribute_value_ids'] ?? [] as $avId) {
                                                                foreach ($attributeValuesByAttr[$attr['id']] ?? [] as $av) {
                                                                    if ($av['id'] == $avId) {
                                                                        $attrValue = $av['value'];
                                                                        break 2;
                                                                    }
                                                                }
                                                            }
                                                            echo htmlspecialchars($attrValue ?: '—');
                                                            ?>
                                                        </span>
                                                        <select x-show="editingExistingVariantId === <?= (int)$variant['id'] ?>"
                                                                name="existing_variants[<?= (int)$variant['id'] ?>][attribute_value_ids][]"
                                                                class="w-full rounded-lg border border-stone-200 bg-white px-2 py-1 text-xs text-stone-800">
                                                            <option value="">— Seçiniz —</option>
                                                            <?php foreach ($attributeValuesByAttr[$attr['id']] ?? [] as $av): ?>
                                                                <option value="<?= (int)$av['id'] ?>" 
                                                                        <?= in_array($av['id'], $variant['attribute_value_ids'] ?? []) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($av['value']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                <?php endforeach; ?>
                                                <td class="whitespace-nowrap px-3 py-2 text-right text-xs text-stone-800">
                                                    <span x-show="editingExistingVariantId !== <?= (int)$variant['id'] ?>"><?= htmlspecialchars($variant['stock']) ?></span>
                                                    <input type="number" 
                                                           x-show="editingExistingVariantId === <?= (int)$variant['id'] ?>"
                                                           name="existing_variants[<?= (int)$variant['id'] ?>][stock]"
                                                           min="0"
                                                           value="<?= htmlspecialchars($variant['stock']) ?>"
                                                           class="w-full rounded-lg border border-stone-200 bg-white px-2 py-1 text-xs text-stone-800">
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-2 text-right text-xs text-stone-800">
                                                    <input type="number" 
                                                           name="existing_variants[<?= (int)$variant['id'] ?>][price]"
                                                           step="0.01"
                                                           value="<?= htmlspecialchars($variant['price'] !== null && $variant['price'] !== '' ? (string)$variant['price'] : '') ?>"
                                                           placeholder="—"
                                                           class="w-full rounded-lg border border-stone-200 bg-white px-2 py-1 text-xs text-stone-800">
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-2 text-right text-xs text-stone-800">
                                                    <input type="number" 
                                                           name="existing_variants[<?= (int)$variant['id'] ?>][sale_price]"
                                                           step="0.01"
                                                           value="<?= htmlspecialchars($variant['sale_price'] !== null && $variant['sale_price'] !== '' ? (string)$variant['sale_price'] : '') ?>"
                                                           placeholder="—"
                                                           class="w-full rounded-lg border border-stone-200 bg-white px-2 py-1 text-xs text-stone-800">
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    <div class="flex items-center justify-center gap-2">
                                                        <button type="button"
                                                                x-show="editingExistingVariantId !== <?= (int)$variant['id'] ?>"
                                                                @click="editingExistingVariantId = <?= (int)$variant['id'] ?>"
                                                                class="text-xs text-stone-600 hover:text-stone-800">
                                                            Düzenle
                                                        </button>
                                                        <button type="button"
                                                                x-show="editingExistingVariantId === <?= (int)$variant['id'] ?>"
                                                                @click="editingExistingVariantId = null; showToast('Varyant düzenleme modu kapatıldı. Değişiklikleri kaydetmek için formu gönderin.', 'info')"
                                                                class="text-xs text-emerald-600 hover:text-emerald-800">
                                                            Kapat
                                                        </button>
                                                        <button type="button"
                                                                @click="deleteExistingVariant(<?= (int)$variant['id'] ?>)"
                                                                class="text-xs text-rose-600 hover:text-rose-800">
                                                            Sil
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Hızlı Ekleme Modu -->
                    <div x-show="variantMode === 'quick'" x-transition class="space-y-4">
                        <!-- Varyant Özellik Seçim Alanı -->
                        <div class="rounded-lg border border-stone-200 bg-white p-4">
                            <h4 class="mb-4 text-sm font-medium text-stone-800">Varyant Özelliklerini Seçin</h4>
                            <div class="space-y-4">
                                <?php foreach ($sortedAttributes as $attr): ?>
                                    <div>
                                        <label class="mb-2 block text-xs font-medium text-stone-700">
                                            <?= htmlspecialchars($attr['name']) ?>
                                        </label>
                                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                                            <?php foreach ($attributeValuesByAttr[$attr['id']] ?? [] as $av): ?>
                                                <?php
                                                // Renk attribute'u için mevcut varyantlarda kullanılan renkleri kontrol et
                                                $isColorAttribute = ($attr['id'] == $colorAttributeId);
                                                ?>
                                                <label x-show="<?= $isColorAttribute ? '!usedColorIds.includes(' . (int)$av['id'] . ')' : 'true' ?>"
                                                       class="flex cursor-pointer items-center gap-2 rounded-lg border border-stone-200 bg-white p-2 hover:bg-stone-50 transition-colors"
                                                       :class="selectedAttributeValues[<?= (int)$attr['id'] ?>]?.includes(<?= (int)$av['id'] ?>) ? 'border-stone-900 bg-stone-50' : ''">
                                                    <input type="checkbox" 
                                                           value="<?= (int)$av['id'] ?>"
                                                           x-model="selectedAttributeValues[<?= (int)$attr['id'] ?>]"
                                                           @change="updateCombinationPreview()"
                                                           class="h-4 w-4 border-stone-300 text-stone-900 focus:ring-stone-400">
                                                    <span class="text-xs text-stone-700"><?= htmlspecialchars($av['value']) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if ($attr['id'] == $colorAttributeId && !empty($usedColorIds)): ?>
                                            <p class="mt-2 text-xs text-stone-500">
                                                <span class="font-medium">Not:</span> Mevcut varyantlarda kullanılan renkler (<?= implode(', ', array_map(function($id) use ($attributeValuesByAttr, $colorAttributeId) {
                                                    foreach ($attributeValuesByAttr[$colorAttributeId] ?? [] as $av) {
                                                        if ($av['id'] == $id) {
                                                            return $av['value'];
                                                        }
                                                    }
                                                    return '';
                                                }, $usedColorIds)) ?>) gizlenmiştir. Yeni renk eklemek için mevcut varyantları silin veya farklı bir renk seçin.
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Renk Bazlı Fotoğraf Ekleme (Renk seçildiyse) -->
                            <div x-show="colorAttributeId && selectedAttributeValues[colorAttributeId] && selectedAttributeValues[colorAttributeId].length > 0" 
                                 x-transition
                                 class="mt-4 rounded-lg border border-stone-200 bg-stone-50 p-4">
                                <h4 class="mb-3 text-sm font-medium text-stone-800">Renk Bazlı Fotoğraflar</h4>
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                                    <template x-for="colorId in selectedAttributeValues[colorAttributeId]" :key="colorId">
                                        <div class="rounded-lg border border-stone-200 bg-white p-3">
                                            <div class="mb-2 flex items-center justify-between">
                                                <span class="text-xs font-medium text-stone-700" x-text="attributeValueMap[colorAttributeId]?.[colorId] || 'Renk'"></span>
                                                <button type="button"
                                                        @click="showColorImageModal = colorId"
                                                        class="rounded border border-stone-300 bg-white px-2 py-1 text-xs text-stone-700 hover:bg-stone-50">
                                                    Fotoğraf Ekle
                                                </button>
                                            </div>
                                            <div x-show="colorImages[colorId] && colorImages[colorId].length > 0" class="mt-2 flex gap-1 overflow-x-auto">
                                                <template x-for="(img, imgIndex) in colorImages[colorId]" :key="imgIndex">
                                                    <div class="group relative h-16 w-16 shrink-0 overflow-hidden rounded border border-stone-200">
                                                        <img :src="img.preview" class="h-full w-full object-cover" :alt="img.name">
                                                        <button type="button"
                                                                @click="removeColorImage(colorId, imgIndex)"
                                                                class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 transition-opacity group-hover:opacity-100">
                                                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                            <p x-show="!colorImages[colorId] || colorImages[colorId].length === 0" 
                                               class="mt-2 text-xs text-stone-400">Henüz fotoğraf yok</p>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Kombinasyon Önizleme ve Oluştur Butonu -->
                            <div class="mt-4 flex items-center justify-between rounded-lg border border-stone-200 bg-stone-50 p-3">
                                <div>
                                    <p class="text-sm font-medium text-stone-800">
                                        <span x-text="combinationCount"></span> kombinasyon oluşturulacak
                                    </p>
                                    <p class="mt-1 text-xs text-stone-500" x-show="combinationCount > 0">
                                        Seçilen özelliklerin tüm kombinasyonları otomatik oluşturulacak
                                    </p>
                                    <p class="mt-1 text-xs text-amber-600" x-show="combinationCount === 0">
                                        Lütfen en az bir özellik seçin
                                    </p>
                                </div>
                                <button type="button"
                                        @click="generateCombinations()"
                                        :disabled="combinationCount === 0"
                                        :class="combinationCount === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                                        class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-black disabled:bg-stone-400">
                                    Tüm Kombinasyonları Oluştur
                                </button>
                            </div>
                        </div>

                        <!-- Kombinasyon Matrisi Tablosu -->
                        <div x-show="variantMatrix.length > 0" x-transition class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-medium text-stone-800">
                                    Oluşturulan Kombinasyonlar (<span x-text="variantMatrix.length"></span> adet)
                                </h4>
                                <div class="flex gap-2">
                                    <button type="button"
                                            @click="showBulkStockModal = true"
                                            class="rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-xs font-medium text-stone-700 transition-colors hover:bg-stone-50">
                                        Toplu Stok
                                    </button>
                                    <button type="button"
                                            @click="showBulkPriceModal = true"
                                            class="rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-xs font-medium text-stone-700 transition-colors hover:bg-stone-50">
                                        Toplu Fiyat
                                    </button>
                                    <button type="button"
                                            @click="showBulkSalePriceModal = true"
                                            class="rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-xs font-medium text-stone-700 transition-colors hover:bg-stone-50">
                                        Toplu İndirimli Fiyat
                                    </button>
                                    <button type="button"
                                            @click="generateAllSKUs()"
                                            class="rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-xs font-medium text-stone-700 transition-colors hover:bg-stone-50">
                                        Tüm SKU'ları Oluştur
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Seçim ve Toplu İşlemler -->
                            <div class="mb-3 flex items-center justify-between rounded-lg border border-stone-200 bg-stone-50 p-3">
                                <div class="flex items-center gap-3">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" 
                                               @change="toggleSelectAllMatrixVariants($event.target.checked)"
                                               class="h-4 w-4 border-stone-300 text-stone-900 focus:ring-stone-400">
                                        <span class="text-xs text-stone-700">Tümünü Seç</span>
                                    </label>
                                    <span class="text-xs text-stone-500" x-show="selectedMatrixVariants.length > 0">
                                        <span x-text="selectedMatrixVariants.length"></span> seçili
                                    </span>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button"
                                            @click="deleteSelectedMatrixVariants()"
                                            :disabled="selectedMatrixVariants.length === 0"
                                            :class="selectedMatrixVariants.length === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                                            class="rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-xs font-medium text-rose-700 transition-colors hover:bg-rose-50 disabled:bg-stone-100">
                                        Seçilenleri Sil
                                    </button>
                                    <button type="button"
                                            @click="deleteAllMatrixVariants()"
                                            :disabled="variantMatrix.length === 0"
                                            :class="variantMatrix.length === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                                            class="rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-xs font-medium text-rose-700 transition-colors hover:bg-rose-50 disabled:bg-stone-100">
                                        Tümünü Sil
                                    </button>
                                </div>
                            </div>

                            <!-- Responsive Tablo -->
                            <div class="overflow-x-auto rounded-lg border border-stone-200 bg-white">
                                <table class="min-w-full divide-y divide-stone-200">
                                    <thead class="bg-stone-50">
                                        <tr>
                                            <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-stone-700 w-12">
                                                <input type="checkbox" 
                                                       @change="toggleSelectAllMatrixVariants($event.target.checked)"
                                                       class="h-4 w-4 border-stone-300 text-stone-900 focus:ring-stone-400">
                                            </th>
                                            <?php foreach ($sortedAttributes as $attr): ?>
                                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-stone-700">
                                                    <?= htmlspecialchars($attr['name']) ?>
                                                </th>
                                            <?php endforeach; ?>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-stone-700">SKU</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-stone-700">Stok</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-stone-700">Fiyat (₺)</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-stone-700">İndirimli (₺)</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-stone-700">Fotoğraflar</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-stone-700">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-stone-200 bg-white">
                                        <template x-for="(variant, index) in variantMatrix" :key="index">
                                            <tr class="hover:bg-stone-50 transition-colors" :class="selectedMatrixVariants.includes(index) ? 'bg-stone-100' : ''">
                                                <td class="px-4 py-3 text-center">
                                                    <input type="checkbox" 
                                                           :checked="selectedMatrixVariants.includes(index)"
                                                           @change="toggleMatrixVariantSelection(index)"
                                                           class="h-4 w-4 border-stone-300 text-stone-900 focus:ring-stone-400">
                                                </td>
                                                <?php foreach ($sortedAttributes as $attr): ?>
                                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-stone-800" x-text="variant.attributes[<?= (int)$attr['id'] ?>] || '—'"></td>
                                                <?php endforeach; ?>
                                                <td class="px-4 py-3">
                                                    <div class="flex gap-1">
                                                        <input type="text" 
                                                               x-model="variant.sku"
                                                               @input="variant.skuManuallyChanged = true"
                                                               :name="'variants[' + index + '][sku]'"
                                                               required
                                                               class="w-full rounded-lg border border-stone-200 px-2 py-1 text-xs text-stone-800">
                                                        <button type="button"
                                                                @click="generateMatrixVariantSKU(index)"
                                                                class="rounded border border-stone-300 bg-white px-2 py-1 text-xs text-stone-700 hover:bg-stone-50"
                                                                title="Otomatik SKU">
                                                            Auto
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <input type="number" 
                                                           x-model="variant.stock"
                                                           :name="'variants[' + index + '][stock]'"
                                                           min="0"
                                                           class="w-full rounded-lg border border-stone-200 px-2 py-1 text-xs text-stone-800">
                                                </td>
                                                <td class="px-4 py-3">
                                                    <input type="number" 
                                                           x-model="variant.price"
                                                           :name="'variants[' + index + '][price]'"
                                                           step="0.01"
                                                           class="w-full rounded-lg border border-stone-200 px-2 py-1 text-xs text-stone-800">
                                                </td>
                                                <td class="px-4 py-3">
                                                    <input type="number" 
                                                           x-model="variant.sale_price"
                                                           :name="'variants[' + index + '][sale_price]'"
                                                           step="0.01"
                                                           class="w-full rounded-lg border border-stone-200 px-2 py-1 text-xs text-stone-800">
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs text-stone-600" 
                                                              x-text="getVariantColorImageCount(index)"></span>
                                                        <span class="text-xs text-stone-400">fotoğraf</span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <button type="button"
                                                            @click="removeMatrixVariant(index)"
                                                            class="text-xs text-rose-600 hover:text-rose-800">
                                                        Sil
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Klasik Mod (Tek Tek Ekleme) -->
                    <div x-show="variantMode === 'classic'" x-transition class="space-y-4">
                        <div class="flex items-center justify-end">
                            <button type="button"
                                    @click="addVariant()"
                                    class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-black">
                                + Varyant Ekle
                            </button>
                        </div>

                        <!-- Klasik modda mevcut varyantlar zaten üstte gösteriliyor, burada tekrar göstermeye gerek yok -->

                        <!-- Yeni Varyantlar -->
                        <div x-show="variants.length > 0" class="space-y-4">
                            <h4 class="text-sm font-medium text-stone-800">Yeni Varyantlar</h4>
                            <template x-for="(variant, index) in variants" :key="index">
                                <div class="rounded-lg border border-stone-200 bg-white p-4">
                                    <div class="mb-4 flex items-center justify-between">
                                        <h5 class="text-sm font-medium text-stone-800" x-text="'Yeni Varyant #' + (index + 1)"></h5>
                                        <button type="button"
                                                @click="removeVariant(index)"
                                                class="text-sm text-rose-600 hover:text-rose-800">
                                            Sil
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-stone-700">SKU <span class="text-rose-600">*</span></label>
                                            <div class="flex gap-1.5">
                                                <input type="text" 
                                                       x-model="variant.sku"
                                                       @input="variant.skuManuallyChanged = true"
                                                       :name="'variants[' + index + '][sku]'"
                                                       required
                                                       class="flex-1 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                                <button type="button"
                                                        @click="generateVariantSKU(index)"
                                                        class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-xs font-medium text-stone-700 transition-colors hover:bg-stone-50 whitespace-nowrap"
                                                        title="Otomatik SKU oluştur">
                                                    Otomatik
                                                </button>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-stone-700">Stok</label>
                                            <input type="number" 
                                                   x-model="variant.stock"
                                                   :name="'variants[' + index + '][stock]'"
                                                   min="0"
                                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-stone-700">Fiyat (₺)</label>
                                            <input type="number" 
                                                   x-model="variant.price"
                                                   :name="'variants[' + index + '][price]'"
                                                   step="0.01"
                                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-stone-700">İndirimli Fiyat (₺)</label>
                                            <input type="number" 
                                                   x-model="variant.sale_price"
                                                   :name="'variants[' + index + '][sale_price]'"
                                                   step="0.01"
                                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label class="mb-1 block text-xs font-medium text-stone-700">Özellikler (Beden/Renk)</label>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach ($sortedAttributes as $attr): ?>
                                                <div class="flex-1 min-w-[120px]">
                                                    <label class="mb-1 block text-xs text-stone-600"><?= htmlspecialchars($attr['name']) ?></label>
                                                    <select :name="'variants[' + index + '][attribute_value_ids][]'"
                                                            @change="updateVariantSKU(index)"
                                                            class="w-full rounded-lg border border-stone-200 bg-white px-2 py-1.5 text-xs text-stone-800 variant-attr-select"
                                                            :data-variant-index="index">
                                                        <option value="">— Seçiniz —</option>
                                                        <?php foreach ($attributeValuesByAttr[$attr['id']] ?? [] as $av): ?>
                                                            <option value="<?= (int)$av['id'] ?>" data-value="<?= htmlspecialchars($av['value']) ?>">
                                                                <?= htmlspecialchars($av['value']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Toplu İşlem Modalları -->
                    <!-- Toplu Fotoğraf Modal -->
                    <div x-show="showBulkImageModal" 
                         x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                         @click.self="showBulkImageModal = false">
                        <div class="w-full max-w-lg rounded-lg border border-stone-200 bg-white p-6 shadow-lg">
                            <h3 class="mb-4 text-lg font-medium text-stone-800">Seçili Varyantlara Fotoğraf Ekle</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-stone-700">Fotoğraflar</label>
                                    <input type="file" 
                                           id="bulk-variant-images-input"
                                           multiple
                                           accept="image/jpeg,image/png,image/webp"
                                           class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                    <p class="mt-1 text-xs text-stone-500">
                                        Seçilen <span x-text="selectedMatrixVariants.length"></span> varyanta aynı fotoğraflar eklenecek.
                                    </p>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button"
                                        @click="showBulkImageModal = false"
                                        class="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50">
                                    İptal
                                </button>
                                <button type="button"
                                        @click="applyBulkImages()"
                                        class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-black">
                                    Uygula
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Renk Bazlı Fotoğraf Modal -->
                    <div x-show="showColorImageModal !== null" 
                         x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                         @click.self="showColorImageModal = null">
                        <div class="w-full max-w-2xl rounded-lg border border-stone-200 bg-white p-6 shadow-lg max-h-[90vh] overflow-y-auto">
                            <h3 class="mb-4 text-lg font-medium text-stone-800">
                                Renk Fotoğrafları: 
                                <span x-text="showColorImageModal !== null ? (attributeValueMap[colorAttributeId]?.[showColorImageModal] || 'Renk') : ''"></span>
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-stone-700">Fotoğraf Ekle</label>
                                    <input type="file" 
                                           :id="'color-images-input-' + showColorImageModal"
                                           multiple
                                           accept="image/jpeg,image/png,image/webp"
                                           @change="handleColorImageUpload($event, showColorImageModal)"
                                           class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                </div>
                                
                                <!-- Yüklenen Fotoğraflar -->
                                <div x-show="colorImages[showColorImageModal] && colorImages[showColorImageModal].length > 0" class="grid grid-cols-3 gap-3">
                                    <template x-for="(img, imgIndex) in colorImages[showColorImageModal]" :key="imgIndex">
                                        <div class="group relative overflow-hidden rounded-lg border border-stone-200 bg-white">
                                            <img :src="img.preview" class="h-32 w-full object-cover" :alt="img.name">
                                            <div class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 transition-opacity group-hover:opacity-100">
                                                <button type="button"
                                                        @click="removeColorImage(showColorImageModal, imgIndex)"
                                                        class="rounded-lg bg-rose-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-rose-600">
                                                    Sil
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div x-show="!colorImages[showColorImageModal] || colorImages[showColorImageModal].length === 0" 
                                     class="rounded-lg border border-stone-200 bg-stone-50 p-6 text-center">
                                    <p class="text-xs text-stone-500">Henüz fotoğraf eklenmedi.</p>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button"
                                        @click="showColorImageModal = null"
                                        class="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50">
                                    Kapat
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Toplu Stok Modal -->
                    <div x-show="showBulkStockModal" 
                         x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                         @click.self="showBulkStockModal = false">
                        <div class="w-full max-w-md rounded-lg border border-stone-200 bg-white p-6 shadow-lg">
                            <h3 class="mb-4 text-lg font-medium text-stone-800">Toplu Stok Uygula</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-stone-700">Stok Miktarı</label>
                                    <input type="number" 
                                           x-model="bulkStockValue"
                                           min="0"
                                           @keydown.enter.prevent="applyBulkStock()"
                                           class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800"
                                           placeholder="Örn: 50"
                                           autofocus>
                                </div>
                                <p class="text-xs text-stone-500">
                                    Bu değer tüm <span x-text="variantMatrix.length"></span> kombinasyona uygulanacak.
                                </p>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button"
                                        @click="showBulkStockModal = false; bulkStockValue = ''"
                                        class="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50">
                                    İptal
                                </button>
                                <button type="button"
                                        @click="applyBulkStock()"
                                        class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-black">
                                    Uygula
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Toplu Fiyat Modal -->
                    <div x-show="showBulkPriceModal" 
                         x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                         @click.self="showBulkPriceModal = false">
                        <div class="w-full max-w-md rounded-lg border border-stone-200 bg-white p-6 shadow-lg">
                            <h3 class="mb-4 text-lg font-medium text-stone-800">Toplu Fiyat Uygula</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-stone-700">Fiyat (₺)</label>
                                    <input type="number" 
                                           x-model="bulkPriceValue"
                                           step="0.01"
                                           min="0"
                                           @keydown.enter.prevent="applyBulkPrice()"
                                           class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800"
                                           placeholder="Örn: 99.99"
                                           autofocus>
                                </div>
                                <p class="text-xs text-stone-500">
                                    Bu değer tüm <span x-text="variantMatrix.length"></span> kombinasyona uygulanacak.
                                </p>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button"
                                        @click="showBulkPriceModal = false; bulkPriceValue = ''"
                                        class="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50">
                                    İptal
                                </button>
                                <button type="button"
                                        @click="applyBulkPrice()"
                                        class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-black">
                                    Uygula
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Toplu İndirimli Fiyat Modal -->
                    <div x-show="showBulkSalePriceModal" 
                         x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                         @click.self="showBulkSalePriceModal = false">
                        <div class="w-full max-w-md rounded-lg border border-stone-200 bg-white p-6 shadow-lg">
                            <h3 class="mb-4 text-lg font-medium text-stone-800">Toplu İndirimli Fiyat Uygula</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-stone-700">İndirimli Fiyat (₺)</label>
                                    <input type="number" 
                                           x-model="bulkSalePriceValue"
                                           step="0.01"
                                           min="0"
                                           @keydown.enter.prevent="applyBulkSalePrice()"
                                           class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800"
                                           placeholder="Örn: 79.99"
                                           autofocus>
                                </div>
                                <p class="text-xs text-stone-500">
                                    Bu değer tüm <span x-text="variantMatrix.length"></span> kombinasyona uygulanacak.
                                </p>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button"
                                        @click="showBulkSalePriceModal = false; bulkSalePriceValue = ''"
                                        class="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50">
                                    İptal
                                </button>
                                <button type="button"
                                        @click="applyBulkSalePrice()"
                                        class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-black">
                                    Uygula
                                </button>
                            </div>
                        </div>
                    </div>

                        <!-- Yeni Varyantlar -->
                    <div x-show="variants.length > 0" class="space-y-4">
                        <h4 class="text-sm font-medium text-stone-800">Yeni Varyantlar</h4>
                        <template x-for="(variant, index) in variants" :key="index">
                            <div class="rounded-lg border border-stone-200 bg-white p-4">
                                <div class="mb-4 flex items-center justify-between">
                                    <h5 class="text-sm font-medium text-stone-800" x-text="'Yeni Varyant #' + (index + 1)"></h5>
                                    <button type="button"
                                            @click="removeVariant(index)"
                                            class="text-sm text-rose-600 hover:text-rose-800">
                                        Sil
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-stone-700">SKU <span class="text-rose-600">*</span></label>
                                        <div class="flex gap-1.5">
                                            <input type="text" 
                                                   x-model="variant.sku"
                                                   @input="variant.skuManuallyChanged = true"
                                                   :name="'variants[' + index + '][sku]'"
                                                   required
                                                   class="flex-1 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                            <button type="button"
                                                    @click="generateVariantSKU(index)"
                                                    class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-xs font-medium text-stone-700 transition-colors hover:bg-stone-50 whitespace-nowrap"
                                                    title="Otomatik SKU oluştur">
                                                Otomatik
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-stone-700">Stok</label>
                                        <input type="number" 
                                               x-model="variant.stock"
                                               :name="'variants[' + index + '][stock]'"
                                               min="0"
                                               class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-stone-700">Fiyat (₺)</label>
                                        <input type="number" 
                                               x-model="variant.price"
                                               :name="'variants[' + index + '][price]'"
                                               step="0.01"
                                               class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-stone-700">İndirimli Fiyat (₺)</label>
                                        <input type="number" 
                                               x-model="variant.sale_price"
                                               :name="'variants[' + index + '][sale_price]'"
                                               step="0.01"
                                               class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                    </div>
                                </div>
                                    <div class="mt-4">
                                    <label class="mb-1 block text-xs font-medium text-stone-700">Özellikler (Beden/Renk)</label>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($sortedAttributes as $attr): ?>
                                            <div class="flex-1 min-w-[120px]">
                                                <label class="mb-1 block text-xs text-stone-600"><?= htmlspecialchars($attr['name']) ?></label>
                                                <select :name="'variants[' + index + '][attribute_value_ids][]'"
                                                        @change="updateVariantSKU(index)"
                                                        class="w-full rounded-lg border border-stone-200 bg-white px-2 py-1.5 text-xs text-stone-800 variant-attr-select"
                                                        :data-variant-index="index">
                                                    <option value="">— Seçiniz —</option>
                                                    <?php foreach ($attributeValuesByAttr[$attr['id']] ?? [] as $av): ?>
                                                        <option value="<?= (int)$av['id'] ?>" data-value="<?= htmlspecialchars($av['value']) ?>">
                                                            <?= htmlspecialchars($av['value']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- SEO SEKME -->
            <div x-show="activeTab === 'seo'" x-cloak x-transition>
                <div class="space-y-4 rounded-xl border border-stone-200 bg-[#FAFAF9] p-4 shadow-sm">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="meta_title" class="mb-1 block text-xs font-medium text-stone-700">Meta Başlık</label>
                            <input type="text" 
                                   id="meta_title" 
                                   name="meta_title" 
                                   value="<?= htmlspecialchars($old['meta_title'] ?? $product['meta_title'] ?? '') ?>"
                                   maxlength="255"
                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                            <p class="mt-0.5 text-xs text-stone-500">Maks. 255 karakter</p>
                        </div>
                        <div>
                            <label for="meta_description" class="mb-1 block text-xs font-medium text-stone-700">Meta Açıklama</label>
                            <textarea id="meta_description" 
                                      name="meta_description" 
                                      rows="3"
                                      maxlength="500"
                                      class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800"><?= htmlspecialchars($old['meta_description'] ?? $product['meta_description'] ?? '') ?></textarea>
                            <p class="mt-0.5 text-xs text-stone-500">Maks. 500 karakter</p>
                        </div>
                    </div>
                    <div>
                        <label for="short_description" class="mb-1 block text-xs font-medium text-stone-700">Kısa Açıklama (SEO)</label>
                        <textarea id="short_description" 
                                  name="short_description" 
                                  rows="3"
                                  maxlength="500"
                                  placeholder="Ürünün kısa açıklaması (SEO için kullanılır)"
                                  class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800"><?= htmlspecialchars($old['short_description'] ?? $product['short_description'] ?? '') ?></textarea>
                        <p class="mt-0.5 text-xs text-stone-500">Maks. 500 karakter. Arama motorları için kısa açıklama.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Butonları -->
        <div class="mt-8 flex items-center justify-end gap-4 border-t border-stone-200 pt-6">
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products" 
               class="rounded-lg border border-stone-300 bg-white px-6 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50">
                Vazgeç
            </a>
            <button type="submit" 
                    class="rounded-lg bg-stone-900 px-6 py-2 text-sm font-medium text-white transition-colors hover:bg-black">
                Kaydet
            </button>
        </div>
    </form>

    <!-- Custom Delete Confirmation Modal -->
    <div x-show="deleteConfirmData.show" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4"
         @click.away="deleteConfirmData.show = false"
         style="display: none;">
        <div class="w-full max-w-md rounded-xl border border-stone-200 bg-[#FAFAF9] shadow-xl"
             @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div class="px-6 py-5">
                <h3 class="mb-2 text-lg font-semibold text-stone-800">Onay</h3>
                <p class="mb-6 text-sm text-stone-600 whitespace-pre-line" x-text="deleteConfirmData.message || 'Bu işlemi yapmak istediğinize emin misiniz?'"></p>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" 
                            @click="deleteConfirmData.show = false"
                            class="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50">
                        Vazgeç
                    </button>
                    <button type="button" 
                            @click="if(deleteConfirmData.callback) deleteConfirmData.callback(); deleteConfirmData.show = false;"
                            class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-rose-700">
                        Evet, Devam Et
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function productForm() {
    return {
        activeTab: 'general',
        slug: '<?= htmlspecialchars($old['slug'] ?? $product['slug'] ?? '') ?>',
        slugManuallyChanged: false,
        sku: '<?= htmlspecialchars($old['sku'] ?? $product['sku'] ?? '') ?>',
        variants: [],
        deleteVariants: [],
        
        // Yeni kombinasyon matrisi sistemi
        variantMode: 'quick', // 'quick' veya 'classic'
        selectedAttributeValues: <?php
            // Her attribute için boş array oluştur (sıralı attribute'ları kullan)
            $selectedAttrValues = [];
            foreach ($sortedAttributes as $attr) {
                $selectedAttrValues[$attr['id']] = [];
            }
            echo json_encode($selectedAttrValues, JSON_UNESCAPED_UNICODE);
        ?>,
        variantMatrix: [],
        showBulkStockModal: false,
        showBulkPriceModal: false,
        showBulkSalePriceModal: false,
        showBulkImageModal: false,
        showVariantImageModalIndex: null,
        bulkStockValue: '',
        bulkPriceValue: '',
        bulkSalePriceValue: '',
        editingExistingVariantId: null, // Düzenlenen mevcut varyant ID'si
        selectedMatrixVariants: [],
        selectedExistingVariants: [], // Mevcut varyantlar için seçim
        deletedExistingVariants: [], // Silinen mevcut varyant ID'leri (DOM'dan gizlemek için)
        deletedColorImages: [], // Silinen renk bazlı fotoğraf ID'leri
        colorImages: <?php
            // Mevcut renk bazlı fotoğrafları yükle
            $colorImagesData = [];
            if ($isEdit && !empty($colorImages)) {
                foreach ($colorImages as $colorId => $images) {
                    $colorImagesData[$colorId] = [];
                    foreach ($images as $img) {
                        $previewPath = $img['path'];
                        if (!empty($previewPath)) {
                            // Eğer path zaten tam URL değilse baseUrl ekle
                            if (strpos($previewPath, 'http') !== 0) {
                                $previewPath = ltrim($previewPath, '/');
                                $previewPath = rtrim($baseUrl, '/') . '/' . $previewPath;
                            }
                        }
                        $colorImagesData[$colorId][] = [
                            'id' => (int)$img['id'],
                            'preview' => $previewPath,
                            'name' => basename($img['path']),
                            'is_existing' => true
                        ];
                    }
                }
            }
            echo json_encode($colorImagesData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        ?>, // {colorId: [imageFiles]} - Renk bazlı fotoğraflar
        showColorImageModal: null, // Açık renk fotoğraf modal ID'si
        existingVariantsCount: <?= !empty($productVariants) ? count($productVariants) : 0 ?>,
        deleteConfirmData: {
            show: false,
            message: '',
            callback: null
        },
        
        // Renk attribute ID'sini bul
        colorAttributeId: <?php
            echo $colorAttributeId ? $colorAttributeId : 'null';
        ?>,
        
        // Mevcut varyantlarda kullanılan renk ID'leri (yeni varyant eklerken gizlenecek)
        usedColorIds: <?= json_encode($usedColorIds, JSON_UNESCAPED_UNICODE) ?>,
        
        // Her varyantın renk ID'sini saklamak için mapping (varyantId -> colorId)
        variantColorMap: <?php
            $variantColorMap = [];
            if ($isEdit && !empty($productVariants) && $colorAttributeId) {
                foreach ($productVariants as $variant) {
                    $variantId = (int)$variant['id'];
                    $colorId = null;
                    foreach ($variant['attribute_value_ids'] ?? [] as $avId) {
                        foreach ($attributeValuesByAttr[$colorAttributeId] ?? [] as $av) {
                            if ($av['id'] == $avId) {
                                $colorId = (int)$avId;
                                break 2;
                            }
                        }
                    }
                    if ($colorId !== null) {
                        $variantColorMap[$variantId] = $colorId;
                    }
                }
            }
            echo json_encode($variantColorMap, JSON_UNESCAPED_UNICODE);
        ?>,
        
        // Attribute değerlerini ID'den isme çevirmek için mapping (sıralı attribute'ları kullan)
        attributeValueMap: <?php
            $attrValueMap = [];
            foreach ($sortedAttributes as $attr) {
                $attrValueMap[$attr['id']] = [];
                foreach ($attributeValuesByAttr[$attr['id']] ?? [] as $av) {
                    $attrValueMap[$attr['id']][$av['id']] = $av['value'];
                }
            }
            echo json_encode($attrValueMap, JSON_UNESCAPED_UNICODE);
        ?>,

        init() {
            // URL hash kontrolü - eğer #seo varsa SEO sekmesine geç
            if (window.location.hash === '#seo') {
                this.activeTab = 'seo';
            } else if (window.location.hash === '#variants') {
                // Eski varyantlar sekmesi kaldırıldı, genel sekmesine yönlendir
                this.activeTab = 'general';
                // Hash'i temizle
                window.history.replaceState(null, '', window.location.pathname + window.location.search);
            }
        },

        autoSlug() {
            if (!this.slugManuallyChanged) {
                const name = document.getElementById('name').value;
                this.slug = this.slugify(name);
            }
        },

        slugify(text) {
            const map = {'ı': 'i', 'ğ': 'g', 'ü': 'u', 'ş': 's', 'ö': 'o', 'ç': 'c', 'İ': 'i', 'Ğ': 'g', 'Ü': 'u', 'Ş': 's', 'Ö': 'o', 'Ç': 'c'};
            text = text.toLowerCase().replace(/[ığüşöçİĞÜŞÖÇ]/g, m => map[m] || m);
            text = text.replace(/[^a-z0-9\s-]/g, '');
            text = text.replace(/[\s-]+/g, '-').trim();
            return text || 'urun';
        },

        generateSKU() {
            const name = document.getElementById('name').value;
            const categorySelect = document.getElementById('category_id');
            const categoryId = categorySelect ? categorySelect.value : '';
            const timestamp = Date.now().toString().slice(-6); // Son 6 rakam
            let sku = '';
            
            if (name) {
                // İsimden ilk harfleri al (maksimum 3 karakter)
                const nameParts = name.toUpperCase().split(' ').filter(p => p.length > 0);
                const initials = nameParts.slice(0, 3).map(p => p[0]).join('');
                sku = initials + timestamp;
            } else {
                sku = 'PRD' + timestamp;
            }
            
            // Kategori ID ekle (varsa)
            if (categoryId) {
                sku = 'CAT' + categoryId + '-' + sku;
            }
            
            this.sku = sku;
            const skuInput = document.getElementById('sku');
            if (skuInput) {
                skuInput.value = sku;
            }
        },


        addVariant() {
            this.variants.push({
                sku: '',
                stock: 0,
                price: '',
                sale_price: '',
                attribute_value_ids: {},
                skuManuallyChanged: false
            });
        },

        generateVariantSKU(variantIndex) {
            const variant = this.variants[variantIndex];
            if (!variant) return;
            
            // Ürün SKU'sunu al, yoksa ürün adından oluştur
            let productSku = this.sku || document.getElementById('sku')?.value || '';
            if (!productSku) {
                // Ürün adından SKU oluştur
                const productName = document.getElementById('name')?.value || '';
                if (productName) {
                    const nameParts = productName.toUpperCase().split(' ').filter(p => p.length > 0);
                    const initials = nameParts.slice(0, 3).map(p => p[0]).join('');
                    const timestamp = Date.now().toString().slice(-6);
                    productSku = initials + timestamp;
                } else {
                    productSku = 'PRD' + Date.now().toString().slice(-6);
                }
            }
            
            // Seçili attribute değerlerini al
            const selectedValues = [];
            // Yeni varyantlar için container'ı bul
            const newVariantsSection = document.querySelector('div[x-show="variants.length > 0"]');
            if (newVariantsSection) {
                const variantContainers = newVariantsSection.querySelectorAll('.rounded-lg.border.border-stone-200.bg-white.p-4');
                const variantContainer = variantContainers[variantIndex];
                if (variantContainer) {
                    const selects = variantContainer.querySelectorAll('select.variant-attr-select');
                    selects.forEach(select => {
                        const selectedOption = select.options[select.selectedIndex];
                        if (selectedOption && selectedOption.value) {
                            const value = selectedOption.getAttribute('data-value') || selectedOption.textContent.trim();
                            if (value && value !== '— Seçiniz —') {
                                // Değeri SKU için uygun formata çevir (boşlukları kaldır, büyük harfe çevir)
                                const skuPart = value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10);
                                if (skuPart) {
                                    selectedValues.push(skuPart);
                                }
                            }
                        }
                    });
                }
            }
            
            // SKU oluştur: ÜRÜN-SKU-ÖZELLİK1-ÖZELLİK2...
            let variantSku = productSku;
            if (selectedValues.length > 0) {
                variantSku = productSku + '-' + selectedValues.join('-');
            } else {
                // Özellik seçilmemişse varyant numarası ekle
                variantSku = productSku + '-V' + (variantIndex + 1);
            }
            
            variant.sku = variantSku;
            variant.skuManuallyChanged = false;
        },

        updateVariantSKU(variantIndex) {
            const variant = this.variants[variantIndex];
            if (!variant || variant.skuManuallyChanged) return;
            
            // Otomatik SKU güncellemesi (kullanıcı manuel değiştirmediyse)
            setTimeout(() => {
                this.generateVariantSKU(variantIndex);
            }, 100);
        },

        generateExistingVariantSKU(variantId) {
            // Mevcut varyant için SKU oluştur
            const variantContainer = document.querySelector(`input[name="existing_variants[${variantId}][sku]"]`)?.closest('.rounded-lg');
            if (!variantContainer) return;
            
            // Ürün SKU'sunu al, yoksa ürün adından oluştur
            let productSku = this.sku || document.getElementById('sku')?.value || '';
            if (!productSku) {
                // Ürün adından SKU oluştur
                const productName = document.getElementById('name')?.value || '';
                if (productName) {
                    const nameParts = productName.toUpperCase().split(' ').filter(p => p.length > 0);
                    const initials = nameParts.slice(0, 3).map(p => p[0]).join('');
                    const timestamp = Date.now().toString().slice(-6);
                    productSku = initials + timestamp;
                } else {
                    productSku = 'PRD' + Date.now().toString().slice(-6);
                }
            }
            
            const selectedValues = [];
            const selects = variantContainer.querySelectorAll('select[name*="[attribute_value_ids][]"]');
            selects.forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const value = selectedOption.getAttribute('data-value') || selectedOption.textContent.trim();
                    if (value && value !== '— Seçiniz —') {
                        const skuPart = value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10);
                        if (skuPart) {
                            selectedValues.push(skuPart);
                        }
                    }
                }
            });
            
            let variantSku = productSku;
            if (selectedValues.length > 0) {
                variantSku = productSku + '-' + selectedValues.join('-');
            } else {
                variantSku = productSku + '-V' + variantId;
            }
            
            const skuInput = variantContainer.querySelector(`input[name="existing_variants[${variantId}][sku]"]`);
            if (skuInput) {
                skuInput.value = variantSku;
            }
        },

        removeVariant(index) {
            this.variants.splice(index, 1);
        },

        deleteExistingVariant(variantId) {
            this.showDeleteConfirm('Bu varyantı silmek istediğinize emin misiniz?', () => {
                // Silme listesine ekle
                if (!this.deleteVariants.includes(variantId)) {
                    this.deleteVariants.push(variantId);
                    // Form submit edildiğinde bu ID'ler silinecek
                    const form = document.querySelector('form');
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_variants[]';
                    input.value = variantId;
                    form.appendChild(input);
                }
                
                // DOM'dan gizle (Alpine.js x-show ile)
                if (!this.deletedExistingVariants.includes(variantId)) {
                    this.deletedExistingVariants.push(variantId);
                }
                
                // Sayacı güncelle
                this.existingVariantsCount--;
                
                // Seçimden de kaldır
                const idx = this.selectedExistingVariants.indexOf(variantId);
                if (idx > -1) {
                    this.selectedExistingVariants.splice(idx, 1);
                }
                
                // Bu varyantın renk ID'sini usedColorIds'den çıkar
                const colorId = this.variantColorMap[variantId];
                if (colorId !== undefined && this.usedColorIds.includes(colorId)) {
                    const colorIdx = this.usedColorIds.indexOf(colorId);
                    if (colorIdx > -1) {
                        this.usedColorIds.splice(colorIdx, 1);
                    }
                }
            });
        },

        toggleExistingVariantSelection(variantId) {
            const idx = this.selectedExistingVariants.indexOf(variantId);
            if (idx > -1) {
                this.selectedExistingVariants.splice(idx, 1);
            } else {
                this.selectedExistingVariants.push(variantId);
            }
        },

        toggleSelectAllExistingVariants(checked) {
            if (checked) {
                // Tüm mevcut varyant ID'lerini al
                const variantIds = [];
                document.querySelectorAll('input[name^="existing_variants["]').forEach(input => {
                    const match = input.name.match(/existing_variants\[(\d+)\]/);
                    if (match && !variantIds.includes(parseInt(match[1]))) {
                        variantIds.push(parseInt(match[1]));
                    }
                });
                this.selectedExistingVariants = variantIds;
            } else {
                this.selectedExistingVariants = [];
            }
        },

        deleteSelectedExistingVariants() {
            if (this.selectedExistingVariants.length === 0) {
                return;
            }
            
            this.showDeleteConfirm(`${this.selectedExistingVariants.length} varyantı silmek istediğinize emin misiniz?`, () => {
                this.performDeleteSelectedExistingVariants();
            });
        },

        performDeleteSelectedExistingVariants() {
            
            // Seçili varyantları silme listesine ekle ve DOM'dan gizle
            this.selectedExistingVariants.forEach(variantId => {
                // Silme listesine ekle
                if (!this.deleteVariants.includes(variantId)) {
                    this.deleteVariants.push(variantId);
                    // Form'a hidden input ekle
                    const form = document.querySelector('form');
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_variants[]';
                    input.value = variantId;
                    form.appendChild(input);
                }
                
                // DOM'dan gizle (Alpine.js x-show ile)
                if (!this.deletedExistingVariants.includes(variantId)) {
                    this.deletedExistingVariants.push(variantId);
                }
                
                // Sayacı güncelle
                this.existingVariantsCount--;
                
                // Bu varyantın renk ID'sini usedColorIds'den çıkar
                const colorId = this.variantColorMap[variantId];
                if (colorId !== undefined && this.usedColorIds.includes(colorId)) {
                    const colorIdx = this.usedColorIds.indexOf(colorId);
                    if (colorIdx > -1) {
                        this.usedColorIds.splice(colorIdx, 1);
                    }
                }
            });
            
            this.selectedExistingVariants = [];
        },

        deleteAllExistingVariants() {
            if (this.existingVariantsCount === 0) {
                return;
            }
            
            this.showDeleteConfirm(`Tüm ${this.existingVariantsCount} mevcut varyantı silmek istediğinize emin misiniz?`, () => {
                this.performDeleteAllExistingVariants();
            });
        },

        performDeleteAllExistingVariants() {
            
            // Tüm mevcut varyant ID'lerini topla
            const allVariantIds = [];
            document.querySelectorAll('input[name^="existing_variants["]').forEach(input => {
                const match = input.name.match(/existing_variants\[(\d+)\]/);
                if (match && !allVariantIds.includes(parseInt(match[1]))) {
                    allVariantIds.push(parseInt(match[1]));
                }
            });
            
            // Tümünü silme listesine ekle ve DOM'dan gizle
            allVariantIds.forEach(variantId => {
                // Silme listesine ekle
                if (!this.deleteVariants.includes(variantId)) {
                    this.deleteVariants.push(variantId);
                    // Form'a hidden input ekle
                    const form = document.querySelector('form');
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_variants[]';
                    input.value = variantId;
                    form.appendChild(input);
                }
                
                // DOM'dan gizle (Alpine.js x-show ile)
                if (!this.deletedExistingVariants.includes(variantId)) {
                    this.deletedExistingVariants.push(variantId);
                }
                
                // Bu varyantın renk ID'sini usedColorIds'den çıkar
                const colorId = this.variantColorMap[variantId];
                if (colorId !== undefined && this.usedColorIds.includes(colorId)) {
                    const colorIdx = this.usedColorIds.indexOf(colorId);
                    if (colorIdx > -1) {
                        this.usedColorIds.splice(colorIdx, 1);
                    }
                }
            });
            
            // Sayacı ve seçimleri sıfırla
            this.existingVariantsCount = 0;
            this.selectedExistingVariants = [];
        },

        showDeleteConfirm(message, callback) {
            this.deleteConfirmData = {
                show: true,
                message: message,
                callback: callback
            };
        },

        // Kombinasyon matrisi fonksiyonları
        updateCombinationPreview() {
            // Bu fonksiyon checkbox değiştiğinde çağrılır
            // combinationCount computed property otomatik güncellenir
        },

        get combinationCount() {
            const selectedArrays = [];
            Object.keys(this.selectedAttributeValues).forEach(attrId => {
                if (this.selectedAttributeValues[attrId] && this.selectedAttributeValues[attrId].length > 0) {
                    selectedArrays.push(this.selectedAttributeValues[attrId]);
                }
            });
            
            if (selectedArrays.length === 0) {
                return 0;
            }
            
            return selectedArrays.reduce((acc, arr) => acc * arr.length, 1);
        },

        generateCombinations() {
            if (this.combinationCount === 0) {
                alert('Lütfen en az bir özellik seçin.');
                return;
            }
            
            // Seçili attribute değerlerini array'lere çevir
            const selectedArrays = [];
            const attrIds = [];
            Object.keys(this.selectedAttributeValues).forEach(attrId => {
                if (this.selectedAttributeValues[attrId] && this.selectedAttributeValues[attrId].length > 0) {
                    selectedArrays.push(this.selectedAttributeValues[attrId]);
                    attrIds.push(parseInt(attrId));
                }
            });
            
            if (selectedArrays.length === 0) {
                return;
            }
            
            // Cartesian Product hesapla
            const combinations = this.cartesianProduct(selectedArrays);
            
            // Kombinasyonları variantMatrix formatına çevir
            this.variantMatrix = combinations.map(combo => {
                const attributes = {};
                const attributeValueIds = [];
                
                combo.forEach((valueId, index) => {
                    const attrId = attrIds[index];
                    attributes[attrId] = this.attributeValueMap[attrId]?.[valueId] || '';
                    attributeValueIds.push(valueId);
                });
                
                return {
                    attributes: attributes,
                    attribute_value_ids: attributeValueIds,
                    sku: '',
                    stock: 0,
                    price: '',
                    sale_price: '',
                    skuManuallyChanged: false
                };
            });
        },

        cartesianProduct(arrays) {
            if (arrays.length === 0) return [];
            if (arrays.length === 1) return arrays[0].map(item => [item]);
            
            const [first, ...rest] = arrays;
            const restProduct = this.cartesianProduct(rest);
            
            const result = [];
            first.forEach(item => {
                restProduct.forEach(combo => {
                    result.push([item, ...combo]);
                });
            });
            
            return result;
        },

        generateMatrixVariantSKU(index) {
            const variant = this.variantMatrix[index];
            if (!variant) return;
            
            // Ürün SKU'sunu al, yoksa ürün adından oluştur
            let productSku = this.sku || document.getElementById('sku')?.value || '';
            if (!productSku) {
                // Ürün adından SKU oluştur
                const productName = document.getElementById('name')?.value || '';
                if (productName) {
                    const nameParts = productName.toUpperCase().split(' ').filter(p => p.length > 0);
                    const initials = nameParts.slice(0, 3).map(p => p[0]).join('');
                    const timestamp = Date.now().toString().slice(-6);
                    productSku = initials + timestamp;
                } else {
                    productSku = 'PRD' + Date.now().toString().slice(-6);
                }
            }
            
            // Attribute değerlerini SKU formatına çevir
            const selectedValues = [];
            Object.keys(variant.attributes).forEach(attrId => {
                const value = variant.attributes[attrId];
                if (value && value !== '—') {
                    const skuPart = value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10);
                    if (skuPart) {
                        selectedValues.push(skuPart);
                    }
                }
            });
            
            let variantSku = productSku;
            if (selectedValues.length > 0) {
                variantSku = productSku + '-' + selectedValues.join('-');
            } else {
                variantSku = productSku + '-V' + (index + 1);
            }
            
            variant.sku = variantSku;
            variant.skuManuallyChanged = false;
        },

        generateAllSKUs() {
            // Ürün SKU'su kontrolü kaldırıldı, otomatik oluşturulacak
            let generatedCount = 0;
            this.variantMatrix.forEach((variant, index) => {
                if (!variant.skuManuallyChanged || !variant.sku) {
                    this.generateMatrixVariantSKU(index);
                    generatedCount++;
                }
            });
            
            if (generatedCount > 0) {
                this.showToast(`${generatedCount} adet SKU başarıyla oluşturuldu.`, 'success');
            } else {
                this.showToast('Tüm SKU\'lar zaten oluşturulmuş.', 'info');
            }
        },

        showToast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { message: message, type: type } 
            }));
        },

        removeMatrixVariant(index) {
            this.showDeleteConfirm('Bu kombinasyonu silmek istediğinize emin misiniz?', () => {
                this.variantMatrix.splice(index, 1);
            });
        },

        applyBulkStock() {
            if (!this.bulkStockValue || this.bulkStockValue === '') {
                alert('Lütfen stok miktarını girin.');
                return;
            }
            
            const stockValue = parseInt(this.bulkStockValue);
            if (isNaN(stockValue) || stockValue < 0) {
                alert('Geçerli bir stok miktarı girin.');
                return;
            }
            
            this.variantMatrix.forEach(variant => {
                variant.stock = stockValue;
            });
            
            this.showBulkStockModal = false;
            this.bulkStockValue = '';
        },

        applyBulkPrice() {
            if (!this.bulkPriceValue || this.bulkPriceValue === '') {
                this.showToast('Lütfen fiyat girin.', 'warning');
                return;
            }
            
            const priceValue = parseFloat(this.bulkPriceValue);
            if (isNaN(priceValue) || priceValue < 0) {
                this.showToast('Geçerli bir fiyat girin.', 'warning');
                return;
            }
            
            this.variantMatrix.forEach(variant => {
                variant.price = priceValue;
            });
            
            this.showBulkPriceModal = false;
            this.bulkPriceValue = '';
            this.showToast(`${this.variantMatrix.length} varyanta fiyat uygulandı.`, 'success');
        },

        applyBulkSalePrice() {
            if (!this.bulkSalePriceValue || this.bulkSalePriceValue === '') {
                this.showToast('Lütfen indirimli fiyat girin.', 'warning');
                return;
            }
            
            const salePriceValue = parseFloat(this.bulkSalePriceValue);
            if (isNaN(salePriceValue) || salePriceValue < 0) {
                this.showToast('Geçerli bir indirimli fiyat girin.', 'warning');
                return;
            }
            
            this.variantMatrix.forEach(variant => {
                variant.sale_price = salePriceValue;
            });
            
            this.showBulkSalePriceModal = false;
            this.bulkSalePriceValue = '';
            this.showToast(`${this.variantMatrix.length} varyanta indirimli fiyat uygulandı.`, 'success');
        },

        // Varyant seçim fonksiyonları
        toggleMatrixVariantSelection(index) {
            const idx = this.selectedMatrixVariants.indexOf(index);
            if (idx > -1) {
                this.selectedMatrixVariants.splice(idx, 1);
            } else {
                this.selectedMatrixVariants.push(index);
            }
        },

        toggleSelectAllMatrixVariants(checked) {
            if (checked) {
                this.selectedMatrixVariants = this.variantMatrix.map((_, index) => index);
            } else {
                this.selectedMatrixVariants = [];
            }
        },

        deleteSelectedMatrixVariants() {
            if (this.selectedMatrixVariants.length === 0) {
                return;
            }
            
            this.showDeleteConfirm(`${this.selectedMatrixVariants.length} varyantı silmek istediğinize emin misiniz?`, () => {
                this.performDeleteSelectedMatrixVariants();
            });
        },

        performDeleteSelectedMatrixVariants() {
            // Seçili varyantları ters sırada sil (index kaymasını önlemek için)
            const sorted = [...this.selectedMatrixVariants].sort((a, b) => b - a);
            sorted.forEach(index => {
                this.variantMatrix.splice(index, 1);
            });
            
            this.selectedMatrixVariants = [];
        },

        deleteAllMatrixVariants() {
            if (this.variantMatrix.length === 0) {
                return;
            }
            
            this.showDeleteConfirm(`Tüm ${this.variantMatrix.length} varyantı silmek istediğinize emin misiniz?`, () => {
                this.variantMatrix = [];
                this.selectedMatrixVariants = [];
            });
        },

        // Renk bazlı fotoğraf fonksiyonları
        handleColorImageUpload(event, colorId) {
            const input = event.target;
            if (!input || !input.files) {
                return;
            }
            
            const files = Array.from(input.files);
            if (!this.colorImages[colorId]) {
                this.colorImages[colorId] = [];
            }
            
            files.forEach(file => {
                if (file.type.startsWith('image/') && file.size <= 2 * 1024 * 1024) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.colorImages[colorId].push({
                            file: file,
                            preview: e.target.result,
                            name: file.name
                        });
                    };
                    reader.readAsDataURL(file);
                } else if (file.size > 2 * 1024 * 1024) {
                    this.showToast('Dosya boyutu 2MB\'dan büyük olamaz: ' + file.name, 'error');
                }
            });
            
            // Input'un value'sunu temizle - aynı dosya tekrar seçilebilmesi için
            input.value = '';
        },

        removeColorImage(colorId, imageIndex) {
            if (this.colorImages[colorId] && this.colorImages[colorId][imageIndex]) {
                const img = this.colorImages[colorId][imageIndex];
                // Eğer mevcut bir görsel ise (veritabanından gelen), silme işlemi için işaretle
                if (img.is_existing && img.id) {
                    if (!this.deletedColorImages) {
                        this.deletedColorImages = [];
                    }
                    this.deletedColorImages.push(img.id);
                }
                this.colorImages[colorId].splice(imageIndex, 1);
            }
        },

        // Varyantın renk ID'sini bul
        getColorIdFromVariant(variant) {
            if (!this.colorAttributeId || !variant.attribute_value_ids) {
                return null;
            }
            // Varyantın attribute_value_ids içinde renk attribute'una ait ID'yi bul
            const colorValueId = variant.attribute_value_ids.find(avId => {
                // attributeValueMap'ten kontrol et
                return this.attributeValueMap[this.colorAttributeId] && 
                       this.attributeValueMap[this.colorAttributeId][avId] !== undefined;
            });
            return colorValueId || null;
        },

        // Varyantın renk bazlı fotoğraf sayısını getir
        getVariantColorImageCount(variantIndex) {
            const variant = this.variantMatrix[variantIndex];
            if (!variant) return 0;
            
            const colorId = this.getColorIdFromVariant(variant);
            if (!colorId || !this.colorImages[colorId]) {
                return 0;
            }
            
            return this.colorImages[colorId].length;
        },

        showToast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { message: message, type: type } 
            }));
        },

        applyBulkImages() {
            if (this.selectedMatrixVariants.length === 0) {
                this.showToast('Lütfen en az bir varyant seçin.', 'warning');
                return;
            }
            
            const input = document.getElementById('bulk-variant-images-input');
            if (!input || !input.files || input.files.length === 0) {
                this.showToast('Lütfen fotoğraf seçin.', 'warning');
                return;
            }
            
            // Renk bazlı fotoğraf sistemi kullanılıyor, bu fonksiyon artık kullanılmıyor
            // Ancak geriye dönük uyumluluk için bırakıyoruz
            this.showToast('Fotoğraf eklemek için renk bazlı fotoğraf ekleme bölümünü kullanın.', 'info');
            this.showBulkImageModal = false;
        },

        submitForm(event) {
            event.preventDefault();
            
            const form = event.target;
            
            // Aktif sekme bilgisini form'a ekle
            const activeTabInput = document.createElement('input');
            activeTabInput.type = 'hidden';
            activeTabInput.name = 'active_tab';
            activeTabInput.value = this.activeTab;
            form.appendChild(activeTabInput);
            
            // SKU değerini kontrol et ve otomatik oluştur
            const skuInput = document.getElementById('sku');
            if (skuInput && (!skuInput.value || skuInput.value.trim() === '')) {
                this.generateSKU();
            }
            
            // Kombinasyon matrisinden gelen varyantları variants dizisine ekle
            if (this.variantMode === 'quick' && this.variantMatrix.length > 0) {
                // SKU validasyonu
                const emptySKUs = [];
                this.variantMatrix.forEach((variant, index) => {
                    if (!variant.sku || variant.sku.trim() === '') {
                        emptySKUs.push(index + 1);
                    }
                });
                
                if (emptySKUs.length > 0) {
                    alert(`Lütfen tüm kombinasyonlar için SKU girin.\nEksik SKU'lar: ${emptySKUs.join(', ')}`);
                    event.preventDefault();
                    return;
                }
                
                // Variant'ları form'a ekle
                this.variantMatrix.forEach((matrixVariant, index) => {
                    const variantData = {
                        sku: matrixVariant.sku || '',
                        stock: matrixVariant.stock || 0,
                        price: matrixVariant.price || '',
                        sale_price: matrixVariant.sale_price || '',
                        attribute_value_ids: matrixVariant.attribute_value_ids || []
                    };
                    
                    // Hidden input olarak ekle
                    const variantPrefix = `variants[${this.variants.length + index}]`;
                    
                    // SKU
                    const skuInput = document.createElement('input');
                    skuInput.type = 'hidden';
                    skuInput.name = `${variantPrefix}[sku]`;
                    skuInput.value = variantData.sku;
                    form.appendChild(skuInput);
                    
                    // Stock
                    const stockInput = document.createElement('input');
                    stockInput.type = 'hidden';
                    stockInput.name = `${variantPrefix}[stock]`;
                    stockInput.value = variantData.stock;
                    form.appendChild(stockInput);
                    
                    // Price
                    if (variantData.price) {
                        const priceInput = document.createElement('input');
                        priceInput.type = 'hidden';
                        priceInput.name = `${variantPrefix}[price]`;
                        priceInput.value = variantData.price;
                        form.appendChild(priceInput);
                    }
                    
                    // Sale Price
                    if (variantData.sale_price) {
                        const salePriceInput = document.createElement('input');
                        salePriceInput.type = 'hidden';
                        salePriceInput.name = `${variantPrefix}[sale_price]`;
                        salePriceInput.value = variantData.sale_price;
                        form.appendChild(salePriceInput);
                    }
                    
                    // Attribute Value IDs
                    variantData.attribute_value_ids.forEach((avId, avIndex) => {
                        const attrInput = document.createElement('input');
                        attrInput.type = 'hidden';
                        attrInput.name = `${variantPrefix}[attribute_value_ids][${avIndex}]`;
                        attrInput.value = avId;
                        form.appendChild(attrInput);
                    });
                });
            }
            
            // Silinecek renk bazlı fotoğrafları ekle
            if (this.deletedColorImages && this.deletedColorImages.length > 0) {
                this.deletedColorImages.forEach(imageId => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_color_images[]';
                    input.value = imageId;
                    form.appendChild(input);
                });
            }
            
            // Renk bazlı görselleri kontrol et
            const hasColorImages = Object.keys(this.colorImages).some(key => 
                this.colorImages[key] && this.colorImages[key].length > 0 && 
                this.colorImages[key].some(img => !img.is_existing && img.file)
            );
            
            // Renk bazlı fotoğraflar varsa veya silinecek fotoğraflar varsa FormData kullan
            if (hasColorImages || (this.deletedColorImages && this.deletedColorImages.length > 0)) {
                // FormData kullanarak gönder
                const formData = new FormData(form);
                
                // Renk bazlı görselleri ekle (her renk için - sadece yeni eklenenler)
                Object.keys(this.colorImages).forEach(colorId => {
                    if (this.colorImages[colorId] && this.colorImages[colorId].length > 0) {
                        this.colorImages[colorId].forEach((img, imgIndex) => {
                            // Sadece yeni eklenen fotoğrafları gönder (is_existing olmayanlar)
                            if (!img.is_existing && img.file) {
                                formData.append(`color_images[${colorId}][${imgIndex}]`, img.file);
                            }
                        });
                    }
                });
                
                // Butonu devre dışı bırak (çift tıklamayı önle)
                const submitButton = form.querySelector('button[type="submit"]');
                const originalButtonText = submitButton ? submitButton.textContent : 'Kaydet';
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Kaydediliyor...';
                }
                
                // FormData ile submit (X-Requested-With header olarak gönderiliyor)
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                }).then(async response => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        // JSON response
                        const data = await response.json();
                        if (data.success) {
                            this.showToast(data.message || 'İşlem başarıyla tamamlandı.', 'success');
                            if (data.redirect) {
                                setTimeout(() => {
                                    window.location.href = data.redirect;
                                }, 1000);
                            } else {
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            }
                        } else {
                            if (submitButton) {
                                submitButton.disabled = false;
                                submitButton.textContent = originalButtonText;
                            }
                            this.showToast(data.message || 'Bir hata oluştu.', 'error');
                            if (data.errors) {
                                // Form hatalarını göster
                                console.error('Form hataları:', data.errors);
                            }
                        }
                    } else {
                        // HTML response (fallback)
                        if (response.ok || response.redirected) {
                            this.showToast('İşlem başarıyla tamamlandı.', 'success');
                            if (response.redirected) {
                                setTimeout(() => {
                                    window.location.href = response.url;
                                }, 500);
                            } else {
                                setTimeout(() => {
                                    window.location.reload();
                                }, 500);
                            }
                        } else {
                            if (submitButton) {
                                submitButton.disabled = false;
                                submitButton.textContent = originalButtonText;
                            }
                            this.showToast('Form gönderilirken bir hata oluştu.', 'error');
                        }
                    }
                }).catch(error => {
                    console.error('Form gönderim hatası:', error);
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = originalButtonText;
                    }
                    this.showToast('Form gönderilirken bir hata oluştu. Lütfen tekrar deneyin.', 'error');
                });
                
                return; // Fetch kullanıldığı için buradan çık
            }
            
            // Normal form submit (fotoğraf yoksa) - AJAX ile gönder
            const formData = new FormData(form);
            formData.append('X-Requested-With', 'XMLHttpRequest');
            
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton ? submitButton.textContent : 'Kaydet';
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Kaydediliyor...';
            }
            
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            }).then(async response => {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (data.success) {
                        this.showToast(data.message || 'İşlem başarıyla tamamlandı.', 'success');
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 500);
                        } else {
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        }
                    } else {
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = originalButtonText;
                        }
                        this.showToast(data.message || 'Bir hata oluştu.', 'error');
                    }
                } else {
                    // HTML response - normal submit gibi davran
                    if (response.ok) {
                        this.showToast('İşlem başarıyla tamamlandı.', 'success');
                        setTimeout(() => {
                            form.submit();
                        }, 500);
                    } else {
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = originalButtonText;
                        }
                        this.showToast('Form gönderilirken bir hata oluştu.', 'error');
                    }
                }
            }).catch(error => {
                console.error('Form gönderim hatası:', error);
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                }
                this.showToast('Form gönderilirken bir hata oluştu.', 'error');
            });
        }
    }
}
</script>
