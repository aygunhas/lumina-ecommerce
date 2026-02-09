<?php
$baseUrl = $baseUrl ?? '';
$product = $product ?? null;
$categories = $categories ?? [];
$brands = $brands ?? [];
$attributes = $attributes ?? [];
$attributeValuesByAttr = $attributeValuesByAttr ?? [];
$productImages = $productImages ?? [];
$productVariants = $productVariants ?? [];
$errors = $errors ?? [];
$old = $old ?? [];
$isEdit = !empty($product);
$formAction = $isEdit ? $baseUrl . '/admin/products/edit?id=' . (int)$product['id'] : $baseUrl . '/admin/products/create';
?>
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
        <!-- Görsel Input (Form'un en üstünde, sekmelerin dışında) -->
        <input id="product-images-input" 
               name="images[]" 
               type="file" 
               multiple 
               accept="image/jpeg,image/png,image/webp" 
               class="sr-only" 
               data-form-type="other"
               autocomplete="off"
               @change="handleImageUpload($event)">
        
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
                        @click="activeTab = 'media'"
                        :class="activeTab === 'media' ? 'border-stone-900 text-stone-900' : 'border-transparent text-stone-400 hover:border-stone-300 hover:text-stone-600'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                    Medya
                </button>
                <button type="button"
                        @click="activeTab = 'variants'"
                        :class="activeTab === 'variants' ? 'border-stone-900 text-stone-900' : 'border-transparent text-stone-400 hover:border-stone-300 hover:text-stone-600'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                    Varyantlar
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
            <div x-show="activeTab === 'general'" x-transition>
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

                    <!-- Kısa ve Detaylı Açıklama -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="short_description" class="mb-1 block text-xs font-medium text-stone-700">Kısa Açıklama</label>
                            <textarea id="short_description" 
                                      name="short_description" 
                                      rows="3"
                                      class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800"><?= htmlspecialchars($old['short_description'] ?? $product['short_description'] ?? '') ?></textarea>
                        </div>
                        <div>
                            <label for="description" class="mb-1 block text-xs font-medium text-stone-700">Detaylı Açıklama</label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="3"
                                      class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800"><?= htmlspecialchars($old['description'] ?? $product['description'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Fiyatlar -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label for="price" class="mb-1 block text-xs font-medium text-stone-700">Fiyat (₺) <span class="text-rose-600">*</span></label>
                            <input type="number" 
                                   id="price" 
                                   name="price" 
                                   step="0.01"
                                   min="0"
                                   required
                                   value="<?= htmlspecialchars($old['price'] ?? $product['price'] ?? '0') ?>"
                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                            <?php if (isset($errors['price'])): ?>
                                <p class="mt-0.5 text-xs text-rose-600"><?= htmlspecialchars($errors['price']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="sale_price" class="mb-1 block text-xs font-medium text-stone-700">İndirimli Fiyat (₺)</label>
                            <input type="number" 
                                   id="sale_price" 
                                   name="sale_price" 
                                   step="0.01"
                                   min="0"
                                   value="<?= htmlspecialchars($old['sale_price'] ?? $product['sale_price'] ?? '') ?>"
                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                        </div>
                        <div>
                            <label for="sku" class="mb-1 block text-xs font-medium text-stone-700">SKU</label>
                            <div class="flex gap-1.5">
                                <input type="text" 
                                       id="sku" 
                                       name="sku" 
                                       value="<?= htmlspecialchars($old['sku'] ?? $product['sku'] ?? '') ?>"
                                       x-model="sku"
                                       @input="slugManuallyChanged = true"
                                       class="flex-1 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                <button type="button"
                                        @click="generateSKU()"
                                        class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50 whitespace-nowrap">
                                    Otomatik
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="stock" class="mb-1 block text-xs font-medium text-stone-700">Stok</label>
                            <input type="number" 
                                   id="stock" 
                                   name="stock" 
                                   min="0"
                                   value="<?= htmlspecialchars($old['stock'] ?? $product['stock'] ?? '0') ?>"
                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                        </div>
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
            </div>

            <!-- MEDYA SEKME -->
            <div x-show="activeTab === 'media'" x-transition>
                <div class="space-y-4 rounded-xl border border-stone-200 bg-[#FAFAF9] p-4 shadow-sm">
                    <!-- Yeni Görsel Yükleme -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-stone-700">Ürün Görselleri</label>
                        <div class="mt-2 flex justify-center rounded-lg border-2 border-dashed border-stone-300 px-4 py-8 hover:border-stone-400 transition-colors cursor-pointer"
                             @dragover.prevent
                             @drop.prevent="handleDrop($event)"
                             @click="openFileDialog()">
                            <div class="text-center pointer-events-none">
                                <svg class="mx-auto h-12 w-12 text-stone-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div class="mt-4 flex text-sm leading-6 text-stone-600 justify-center">
                                    <span class="font-semibold text-stone-900">Görsel seç</span>
                                    <p class="pl-1">veya sürükle bırak</p>
                                </div>
                                <p class="text-xs leading-5 text-stone-600">PNG, JPG, WEBP (Maks. 2MB)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tüm Görseller (Mevcut + Yeni) -->
                    <div class="space-y-3">
                        <label class="mb-1 block text-xs font-medium text-stone-700">Tüm Görseller</label>
                        <div x-show="uploadedImages.length === 0" class="rounded-lg border border-stone-200 bg-stone-50 p-6 text-center">
                            <p class="text-xs text-stone-500">Henüz görsel eklenmedi. Yukarıdaki alana tıklayarak görsel seçebilirsiniz.</p>
                        </div>
                        <div x-show="uploadedImages.length > 0" class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                            <template x-for="(img, index) in uploadedImages" :key="index">
                                <div class="group relative overflow-hidden rounded-lg border border-stone-200 bg-white">
                                    <img :src="img.preview" class="h-32 w-full object-cover" :alt="'Görsel ' + (index + 1)">
                                    <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/60 opacity-0 transition-opacity group-hover:opacity-100">
                                        <template x-if="!img.is_existing">
                                            <div class="flex gap-2">
                                                <button type="button"
                                                        @click="setMainImageIndex(index)"
                                                        :class="mainImageIndex === index ? 'bg-emerald-500 text-white' : 'bg-white text-stone-900'"
                                                        class="rounded-lg px-3 py-1.5 text-xs font-medium hover:bg-stone-50">
                                                    <span x-text="mainImageIndex === index ? 'Ana Görsel' : 'Kapak Yap'"></span>
                                                </button>
                                                <button type="button"
                                                        @click="removeImage(index)"
                                                        class="rounded-lg bg-rose-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-rose-600">
                                                    Sil
                                                </button>
                                            </div>
                                        </template>
                                        <template x-if="img.is_existing">
                                            <div class="flex gap-2">
                                                <span class="rounded-full bg-emerald-500 px-2 py-1 text-xs font-medium text-white" x-show="img.is_main">Ana Görsel</span>
                                                <button type="button"
                                                        x-show="!img.is_main"
                                                        @click="setMainImage(img.id)"
                                                        class="rounded-lg bg-white px-3 py-1.5 text-xs font-medium text-stone-900 hover:bg-stone-50">
                                                    Kapak Yap
                                                </button>
                                                <button type="button"
                                                        @click="removeImage(index)"
                                                        class="rounded-lg bg-rose-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-rose-600">
                                                    Sil
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <input type="hidden" name="main_image_index" x-model="mainImageIndex">
                    </div>
                </div>
            </div>

            <!-- VARYANTLAR SEKME -->
            <div x-show="activeTab === 'variants'" x-transition>
                <div class="space-y-4 rounded-xl border border-stone-200 bg-[#FAFAF9] p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-stone-800">Ürün Varyantları</h3>
                            <p class="mt-1 text-sm text-stone-500">Beden, renk ve stok bilgilerini ekleyin</p>
                        </div>
                        <button type="button"
                                @click="addVariant()"
                                class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-black">
                            + Varyant Ekle
                        </button>
                    </div>

                    <!-- Mevcut Varyantlar -->
                    <?php if (!empty($productVariants)): ?>
                        <div class="space-y-4">
                            <?php foreach ($productVariants as $idx => $variant): ?>
                                <div class="rounded-lg border border-stone-200 bg-white p-4">
                                    <div class="mb-4 flex items-center justify-between">
                                        <h4 class="text-sm font-medium text-stone-800">Varyant #<?= $idx + 1 ?></h4>
                                        <button type="button"
                                                @click="deleteExistingVariant(<?= (int)$variant['id'] ?>)"
                                                class="text-sm text-rose-600 hover:text-rose-800">
                                            Sil
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-stone-700">SKU</label>
                                            <input type="text" 
                                                   name="existing_variants[<?= (int)$variant['id'] ?>][sku]"
                                                   value="<?= htmlspecialchars($variant['sku']) ?>"
                                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-stone-700">Stok</label>
                                            <input type="number" 
                                                   name="existing_variants[<?= (int)$variant['id'] ?>][stock]"
                                                   min="0"
                                                   value="<?= htmlspecialchars($variant['stock']) ?>"
                                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-stone-700">Fiyat (₺)</label>
                                            <input type="number" 
                                                   name="existing_variants[<?= (int)$variant['id'] ?>][price]"
                                                   step="0.01"
                                                   value="<?= htmlspecialchars($variant['price'] ?? '') ?>"
                                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-stone-700">İndirimli Fiyat (₺)</label>
                                            <input type="number" 
                                                   name="existing_variants[<?= (int)$variant['id'] ?>][sale_price]"
                                                   step="0.01"
                                                   value="<?= htmlspecialchars($variant['sale_price'] ?? '') ?>"
                                                   class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label class="mb-1 block text-xs font-medium text-stone-700">Özellikler (Beden/Renk)</label>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach ($attributes as $attr): ?>
                                                <div class="flex-1 min-w-[120px]">
                                                    <label class="mb-1 block text-xs text-stone-600"><?= htmlspecialchars($attr['name']) ?></label>
                                                    <select name="existing_variants[<?= (int)$variant['id'] ?>][attribute_value_ids][]"
                                                            class="w-full rounded-lg border border-stone-200 bg-white px-2 py-1.5 text-xs text-stone-800">
                                                        <option value="">— Seçiniz —</option>
                                                        <?php foreach ($attributeValuesByAttr[$attr['id']] ?? [] as $av): ?>
                                                            <option value="<?= (int)$av['id'] ?>" 
                                                                    <?= in_array($av['id'], $variant['attribute_value_ids'] ?? []) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($av['value']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

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
                                        <input type="text" 
                                               x-model="variant.sku"
                                               :name="'variants[' + index + '][sku]'"
                                               required
                                               class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800">
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
                                        <?php foreach ($attributes as $attr): ?>
                                            <div class="flex-1 min-w-[120px]">
                                                <label class="mb-1 block text-xs text-stone-600"><?= htmlspecialchars($attr['name']) ?></label>
                                                <select :name="'variants[' + index + '][attribute_value_ids][]'"
                                                        class="w-full rounded-lg border border-stone-200 bg-white px-2 py-1.5 text-xs text-stone-800">
                                                    <option value="">— Seçiniz —</option>
                                                    <?php foreach ($attributeValuesByAttr[$attr['id']] ?? [] as $av): ?>
                                                        <option value="<?= (int)$av['id'] ?>">
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
            <div x-show="activeTab === 'seo'" x-transition>
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
</div>

<script>
function productForm() {
    return {
        activeTab: 'general',
        slug: '<?= htmlspecialchars($old['slug'] ?? $product['slug'] ?? '') ?>',
        slugManuallyChanged: false,
        sku: '<?= htmlspecialchars($old['sku'] ?? $product['sku'] ?? '') ?>',
        uploadedImages: <?php
            if (!empty($productImages)) {
                $imagesData = array_map(function($img) use ($baseUrl) {
                    // Görsel yolunu düzelt
                    $previewPath = $img['path'];
                    if (!empty($previewPath)) {
                        // Eğer path zaten tam URL değilse baseUrl ekle
                        if (strpos($previewPath, 'http') !== 0) {
                            // Path'in başında / varsa kaldır, baseUrl zaten / içeriyor olabilir
                            $previewPath = ltrim($previewPath, '/');
                            $previewPath = rtrim($baseUrl, '/') . '/' . $previewPath;
                        }
                    }
                    return [
                        'id' => (int)$img['id'],
                        'preview' => $previewPath,
                        'is_main' => (int)($img['is_main'] ?? 0),
                        'is_existing' => true
                    ];
                }, $productImages);
                echo json_encode($imagesData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } else {
                echo '[]';
            }
        ?>,
        mainImageIndex: <?= !empty($productImages) ? (function($images) {
            $mainIndex = array_search(true, array_column($images, 'is_main'));
            return $mainIndex !== false ? $mainIndex : 0;
        })($productImages) : 0 ?>,
        variants: [],
        deleteVariants: [],
        deleteImages: [],

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

        openFileDialog() {
            const input = document.getElementById('product-images-input');
            if (input) {
                input.click();
            }
        },

        handleImageUpload(event) {
            const input = event.target;
            if (!input || !input.files) {
                return;
            }
            const files = Array.from(input.files);
            files.forEach(file => {
                if (file.type.startsWith('image/') && file.size <= 2 * 1024 * 1024) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.uploadedImages.push({
                            file: file,
                            preview: e.target.result,
                            is_existing: false
                        });
                    };
                    reader.onerror = () => {
                        console.error('Görsel okuma hatası');
                    };
                    reader.readAsDataURL(file);
                } else if (file.size > 2 * 1024 * 1024) {
                    alert('Dosya boyutu 2MB\'dan büyük olamaz: ' + file.name);
                }
            });
        },

        handleDrop(event) {
            event.preventDefault();
            const files = Array.from(event.dataTransfer.files);
            const imageFiles = files.filter(file => file.type.startsWith('image/'));
            if (imageFiles.length > 0) {
                const input = document.getElementById('product-images-input');
                if (input) {
                    const dataTransfer = new DataTransfer();
                    imageFiles.forEach(file => {
                        if (file.size <= 2 * 1024 * 1024) {
                            dataTransfer.items.add(file);
                        } else {
                            alert('Dosya boyutu 2MB\'dan büyük olamaz: ' + file.name);
                        }
                    });
                    input.files = dataTransfer.files;
                    const changeEvent = new Event('change', { bubbles: true });
                    input.dispatchEvent(changeEvent);
                }
            }
        },

        setMainImageIndex(index) {
            this.mainImageIndex = index;
        },

        removeImage(index) {
            const img = this.uploadedImages[index];
            // Eğer mevcut bir görsel ise, silme işlemi için işaretle
            if (img.is_existing && img.id) {
                if (!this.deleteImages) {
                    this.deleteImages = [];
                }
                this.deleteImages.push(img.id);
            }
            this.uploadedImages.splice(index, 1);
            if (this.mainImageIndex >= this.uploadedImages.length) {
                this.mainImageIndex = Math.max(0, this.uploadedImages.length - 1);
            }
        },

        setMainImage(imageId) {
            // Mevcut görseli ana görsel yap
            const imgIndex = this.uploadedImages.findIndex(img => img.id === imageId && img.is_existing);
            if (imgIndex !== -1) {
                // Tüm mevcut görsellerin is_main durumunu güncelle
                this.uploadedImages.forEach((img, idx) => {
                    if (img.is_existing) {
                        img.is_main = (idx === imgIndex);
                    }
                });
                this.mainImageIndex = imgIndex;
            }
        },

        addVariant() {
            this.variants.push({
                sku: '',
                stock: 0,
                price: '',
                sale_price: '',
                attribute_value_ids: []
            });
        },

        removeVariant(index) {
            this.variants.splice(index, 1);
        },

        deleteExistingVariant(variantId) {
            if (confirm('Bu varyantı silmek istediğinize emin misiniz?')) {
                this.deleteVariants.push(variantId);
                // Form submit edildiğinde bu ID'ler silinecek
                const form = document.querySelector('form');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_variants[]';
                input.value = variantId;
                form.appendChild(input);
                // DOM'dan kaldır
                event.target.closest('.rounded-lg').remove();
            }
        },

        submitForm(event) {
            event.preventDefault();
            
            const form = event.target;
            
            // SKU değerini kontrol et ve otomatik oluştur
            const skuInput = document.getElementById('sku');
            if (skuInput && (!skuInput.value || skuInput.value.trim() === '')) {
                this.generateSKU();
            }
            
            // Silinecek görselleri form'a ekle
            if (this.deleteImages && this.deleteImages.length > 0) {
                this.deleteImages.forEach(imageId => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_images[]';
                    input.value = imageId;
                    form.appendChild(input);
                });
            }
            
            // Ana görsel seçimi
            const mainImage = this.uploadedImages[this.mainImageIndex];
            if (mainImage) {
                if (mainImage.is_existing && mainImage.id) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'main_image_id';
                    input.value = mainImage.id;
                    form.appendChild(input);
                } else {
                    const newImages = this.uploadedImages.filter(img => !img.is_existing);
                    const newImageIndex = newImages.findIndex(img => img === mainImage);
                    if (newImageIndex !== -1) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'main_image_index';
                        input.value = newImageIndex;
                        form.appendChild(input);
                    }
                }
            }
            
            // Görsel input'unu kontrol et ve düzelt
            const imageInput = document.getElementById('product-images-input');
            if (imageInput) {
                if (!form.contains(imageInput)) {
                    form.appendChild(imageInput);
                }
                imageInput.disabled = false;
                if (imageInput.name !== 'images[]') {
                    imageInput.name = 'images[]';
                }
            }
            
            form.submit();
        }
    }
}
</script>
