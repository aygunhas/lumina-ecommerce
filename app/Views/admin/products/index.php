<?php
$baseUrl = $baseUrl ?? '';
$products = $products ?? [];
$categories = $categories ?? [];
$filterQ = $filterQ ?? '';
$filterCategoryId = $filterCategoryId ?? null;
$filterStock = $filterStock ?? '';
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalRecords = $totalRecords ?? 0;
?>
<div class="space-y-6" x-data="productList()" x-init="init()">
    <!-- Başlık ve Yeni Ekle Butonu -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-light tracking-tight text-stone-800">Ürünler</h1>
            <p class="mt-1 text-sm text-stone-500">Ürün yönetimi ve düzenleme</p>
        </div>
        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products/create" 
           class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-black">
            Yeni Ürün
        </a>
    </div>

    <!-- Filtreler -->
    <div class="rounded-xl border border-stone-200 bg-[#FAFAF9] p-4 shadow-sm">
        <form @submit.prevent="applyFilters()" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <label for="q" class="mb-1.5 block text-xs font-medium text-stone-700">Ara (ürün adı, SKU)</label>
                <input type="text" 
                       id="q" 
                       x-model="filters.q"
                       @input.debounce.500ms="applyFilters()"
                       placeholder="Ara..."
                       autocomplete="off"
                       class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-400">
            </div>
            <div>
                <label for="category_id" class="mb-1.5 block text-xs font-medium text-stone-700">Kategori</label>
                <select id="category_id" 
                        x-model="filters.category_id"
                        @change="applyFilters()"
                        autocomplete="off"
                        class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-400">
                    <option value="">— Tümü —</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (string)$filterCategoryId === (string)$c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="stock" class="mb-1.5 block text-xs font-medium text-stone-700">Stok Durumu</label>
                <select id="stock" 
                        x-model="filters.stock"
                        @change="applyFilters()"
                        autocomplete="off"
                        class="w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-400">
                    <option value="">— Tümü —</option>
                    <option value="in_stock" <?= $filterStock === 'in_stock' ? 'selected' : '' ?>>Stokta</option>
                    <option value="low_stock" <?= $filterStock === 'low_stock' ? 'selected' : '' ?>>Düşük stok</option>
                    <option value="out_of_stock" <?= $filterStock === 'out_of_stock' ? 'selected' : '' ?>>Stok yok</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="button"
                        @click="clearFilters()"
                        x-show="hasActiveFilters()"
                        class="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50">
                    Temizle
                </button>
            </div>
        </form>
    </div>

    <!-- Ürünler Tablosu -->
    <div class="overflow-hidden rounded-xl border border-stone-200 bg-[#FAFAF9] shadow-sm" id="products-container">
        <div x-show="loading" class="p-12 text-center">
            <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-stone-300 border-r-stone-900"></div>
            <p class="mt-4 text-sm text-stone-500">Yükleniyor...</p>
        </div>
        <div x-show="!loading" x-html="productsHtml"></div>
    </div>

    <!-- Sayfalama -->
    <div x-show="!loading && totalPages > 1" class="mt-4 overflow-hidden rounded-xl border border-stone-200 bg-[#FAFAF9] shadow-sm">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="text-sm text-stone-600">
                Toplam <span class="font-medium text-stone-800" x-text="totalRecords"></span> ürün
            </div>
            <div class="flex items-center gap-2">
                <button type="button" 
                        x-show="currentPage > 1"
                        @click="loadPage(currentPage - 1)"
                        class="rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50">
                    Önceki
                </button>
                <span class="text-sm text-stone-600">
                    Sayfa <span class="font-medium text-stone-800" x-text="currentPage"></span> / <span x-text="totalPages"></span>
                </span>
                <button type="button" 
                        x-show="currentPage < totalPages"
                        @click="loadPage(currentPage + 1)"
                        class="rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50">
                    Sonraki
                </button>
            </div>
        </div>
    </div>

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
                <h3 class="mb-2 text-lg font-semibold text-stone-800">Ürünü Sil</h3>
                <p class="mb-6 text-sm text-stone-600 whitespace-pre-line" x-text="deleteConfirmData.message || ('\'' + deleteConfirmData.name + '\' ürününü silmek istediğinize emin misiniz?')"></p>
                <div x-show="deleteConfirmData.message && deleteConfirmData.message.includes('UYARI')" class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3">
                    <div class="flex items-start gap-2">
                        <svg class="h-5 w-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-sm text-amber-800">Bu ürün kargoda durumundaki siparişlerde kullanılıyor. Silme işlemi devam edecek.</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" 
                            @click="deleteConfirmData.show = false"
                            class="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50">
                        Vazgeç
                    </button>
                    <button type="button" 
                            @click="if(deleteConfirmData.callback) deleteConfirmData.callback(); deleteConfirmData.show = false;"
                            class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-rose-700">
                        Evet, Sil
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function productList() {
    return {
        loading: false,
        productsHtml: '',
        filters: {
            q: '<?= htmlspecialchars($filterQ) ?>',
            category_id: '<?= $filterCategoryId ? (int)$filterCategoryId : '' ?>',
            stock: '<?= htmlspecialchars($filterStock) ?>'
        },
        currentPage: <?= $page ?>,
        totalPages: <?= $totalPages ?>,
        totalRecords: <?= $totalRecords ?>,

        init() {
            // Global delete handler'ı kaydet
            window.deleteProductHandler = (id, name) => {
                this.deleteProduct(id, name);
            };
            
            // İlk yüklemede mevcut içeriği göster
            this.productsHtml = `<?php 
                ob_start();
                if (empty($products)): ?>
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <p class="mt-4 text-sm font-medium text-stone-500">Henüz ürün yok</p>
                        <p class="mt-1 text-xs text-stone-400">"Yeni Ürün" butonu ile ürün ekleyebilirsiniz</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-stone-200">
                            <thead class="bg-stone-50/50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-stone-500">Ürün</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-stone-500">Kategori</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-stone-500">Fiyat</th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-medium uppercase tracking-wider text-stone-500">Stok</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-stone-500">Durum</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-stone-500">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-200 bg-[#FAFAF9]">
                                <?php foreach ($products as $p): 
                                    $displayPrice = $p['sale_price'] !== null && (float) $p['sale_price'] > 0
                                        ? (float) $p['sale_price']
                                        : (float) $p['price'];
                                    $originalPrice = (float) $p['price'];
                                    $hasSale = $p['sale_price'] !== null && (float) $p['sale_price'] > 0 && (float) $p['sale_price'] < $originalPrice;
                                    $stock = (int) $p['stock'];
                                    $lowStockThreshold = (int) ($p['low_stock_threshold'] ?? 5);
                                    $isLowStock = $stock > 0 && $stock <= $lowStockThreshold;
                                    $isOutOfStock = $stock === 0;
                                ?>
                                    <tr class="hover:bg-stone-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <?php if (!empty($p['image'])): ?>
                                                    <div class="h-12 w-12 shrink-0 overflow-hidden rounded-md">
                                                        <img src="<?= htmlspecialchars($baseUrl . '/' . $p['image']) ?>" 
                                                             alt="<?= htmlspecialchars($p['name']) ?>" 
                                                             class="h-full w-full object-cover"
                                                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'flex h-full w-full items-center justify-center bg-stone-100 text-xs font-medium text-stone-400 rounded-md\'>Görsel Yok</div>'">
                                                    </div>
                                                <?php else: ?>
                                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-stone-100 text-xs font-medium text-stone-400">
                                                        Görsel Yok
                                                    </div>
                                                <?php endif; ?>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium text-stone-800"><?= htmlspecialchars($p['name']) ?></p>
                                                    <?php if (!empty($p['sku'])): ?>
                                                        <p class="mt-0.5 text-xs text-stone-400">SKU: <?= htmlspecialchars($p['sku']) ?></p>
                                                    <?php endif; ?>
                                                    <div class="mt-1 flex items-center gap-2">
                                                        <?php if ((int) $p['is_featured']): ?>
                                                            <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 border border-emerald-200">Öne Çıkan</span>
                                                        <?php endif; ?>
                                                        <?php if ((int) $p['is_new']): ?>
                                                            <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 border border-amber-200">Yeni</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm text-stone-600"><?= $p['category_name'] ? htmlspecialchars($p['category_name']) : '—' ?></p>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right">
                                            <div class="text-sm font-medium text-stone-800">
                                                <?php if ($hasSale): ?>
                                                    <span class="text-rose-600"><?= number_format($displayPrice, 2, ',', '.') ?> ₺</span>
                                                    <span class="ml-2 text-xs text-stone-400 line-through"><?= number_format($originalPrice, 2, ',', '.') ?> ₺</span>
                                                <?php else: ?>
                                                    <?= number_format($displayPrice, 2, ',', '.') ?> ₺
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <?php if ($isOutOfStock): ?>
                                                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                                <?php elseif ($isLowStock): ?>
                                                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                                <?php endif; ?>
                                                <span class="text-sm font-medium <?= $isOutOfStock ? 'text-rose-600' : ($isLowStock ? 'text-amber-600' : 'text-stone-700') ?>">
                                                    <?= $stock ?> Adet
                                                </span>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="inline-flex items-center gap-1.5">
                                                <span class="h-2 w-2 rounded-full <?= (int) $p['is_active'] ? 'bg-emerald-500' : 'bg-stone-300' ?>"></span>
                                                <span class="text-xs font-medium <?= (int) $p['is_active'] ? 'text-emerald-700' : 'text-stone-500' ?>">
                                                    <?= (int) $p['is_active'] ? 'Aktif' : 'Pasif' ?>
                                                </span>
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <div class="flex items-center justify-end gap-3">
                                                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products/edit?id=<?= (int) $p['id'] ?>" 
                                                   class="text-stone-400 transition-colors hover:text-stone-800"
                                                   title="Düzenle">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                        <button type="button"
                                                onclick="if(window.deleteProductHandler) window.deleteProductHandler(<?= (int) $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>')"
                                                class="text-stone-400 transition-colors hover:text-rose-600"
                                                title="Sil">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; 
                echo addslashes(ob_get_clean());
            ?>`;
        },

        async applyFilters() {
            this.currentPage = 1; // Filtre değiştiğinde ilk sayfaya dön
            await this.loadData();
        },

        async loadPage(page) {
            this.currentPage = page;
            await this.loadData();
        },

        async loadData() {
            this.loading = true;
            
            const params = new URLSearchParams({
                page: this.currentPage,
                ...(this.filters.q && { q: this.filters.q }),
                ...(this.filters.category_id && { category_id: this.filters.category_id }),
                ...(this.filters.stock && { stock: this.filters.stock })
            });

            try {
                const response = await fetch('<?= htmlspecialchars($baseUrl) ?>/admin/products?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Yükleme hatası');
                }

                const data = await response.json();
                
                if (data.success) {
                    this.productsHtml = data.html;
                    this.totalPages = data.totalPages;
                    this.totalRecords = data.totalRecords;
                    
                    // Global delete handler'ı yeniden kaydet (AJAX ile yüklenen içerik için)
                    window.deleteProductHandler = (id, name) => {
                        this.deleteProduct(id, name);
                    };
                    
                    // URL'i güncelle (sayfa yenilenmeden)
                    const newUrl = '<?= htmlspecialchars($baseUrl) ?>/admin/products?' + params.toString();
                    window.history.pushState({}, '', newUrl);
                    
                    // Sayfanın üstüne kaydır
                    document.getElementById('products-container').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } catch (error) {
                console.error('Hata:', error);
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type: 'error', message: 'Ürünler yüklenirken bir hata oluştu.' }
                }));
            } finally {
                this.loading = false;
            }
        },

        clearFilters() {
            this.filters = {
                q: '',
                category_id: '',
                stock: ''
            };
            this.applyFilters();
        },

        hasActiveFilters() {
            return this.filters.q !== '' || this.filters.category_id !== '' || this.filters.stock !== '';
        },

        async deleteProduct(id, name) {
            // Önce sipariş durumlarını kontrol et
            try {
                const checkResponse = await fetch('<?= htmlspecialchars($baseUrl) ?>/admin/products/delete?id=' + id, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (checkResponse.ok) {
                    const checkData = await checkResponse.json();
                    
                    // Aktif siparişlerde kullanılıyorsa silme engellenir
                    if (checkData.order_info && checkData.order_info.active_orders > 0) {
                        this.showToast('Bu ürün aktif siparişlerde (beklemede, onaylandı, hazırlanıyor) kullanıldığı için silinemez.', 'error');
                        return;
                    }
                    
                    // Kargoda durumunda siparişler varsa uyarı göster
                    let confirmMessage = `"${name}" ürününü silmek istediğinize emin misiniz?`;
                    if (checkData.order_info && checkData.order_info.shipped_orders > 0) {
                        confirmMessage = `"${name}" ürününü silmek istediğinize emin misiniz?\n\nUYARI: Bu ürün ${checkData.order_info.shipped_orders} adet kargoda durumundaki siparişte kullanılıyor. Silme işlemi devam edecek.`;
                    }
                    
                    this.showDeleteConfirm(name, () => {
                        this.performDelete(id);
                    }, confirmMessage);
                } else {
                    // Hata durumunda direkt silme denemesi yap
                    this.showDeleteConfirm(name, () => {
                        this.performDelete(id);
                    });
                }
            } catch (error) {
                console.error('Kontrol hatası:', error);
                // Hata durumunda direkt silme denemesi yap
                this.showDeleteConfirm(name, () => {
                    this.performDelete(id);
                });
            }
        },

        showDeleteConfirm(name, callback, message = null) {
            this.deleteConfirmData = {
                show: true,
                name: name,
                message: message || `"${name}" ürününü silmek istediğinize emin misiniz?`,
                callback: callback
            };
        },

        async performDelete(id) {
            try {
                const response = await fetch('<?= htmlspecialchars($baseUrl) ?>/admin/products/delete?id=' + id, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const contentType = response.headers.get('content-type');
                let data;
                
                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    // JSON değilse, hata mesajını oku
                    const text = await response.text();
                    this.showToast(text || 'Beklenmeyen yanıt formatı', 'error');
                    this.deleteConfirmData.show = false;
                    return;
                }

                if (!response.ok) {
                    // 400 veya diğer hata kodları için mesajı göster
                    this.showToast(data.message || 'Ürün silinirken bir hata oluştu.', 'error');
                    this.deleteConfirmData.show = false;
                    return;
                }
                
                if (data.success) {
                    this.showToast(data.message || 'Ürün başarıyla silindi.', 'success');
                    this.deleteConfirmData.show = false;
                    // Tabloyu yeniden yükle
                    await this.loadData();
                } else {
                    this.showToast(data.message || 'Ürün silinirken bir hata oluştu.', 'error');
                    this.deleteConfirmData.show = false;
                }
            } catch (error) {
                console.error('Silme hatası:', error);
                this.showToast('Ürün silinirken bir hata oluştu: ' + (error.message || 'Bilinmeyen hata'), 'error');
                this.deleteConfirmData.show = false;
            }
        },

        showToast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { message: message, type: type } 
            }));
        },

        deleteConfirmData: {
            show: false,
            name: '',
            message: '',
            callback: null
        }
    }
}
</script>
