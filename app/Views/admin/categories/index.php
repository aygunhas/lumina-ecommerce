<?php
$baseUrl = $baseUrl ?? '';
$categories = $categories ?? [];
$parents = $parents ?? [];
$errors = $_SESSION['category_errors'] ?? [];
$old = $_SESSION['category_old'] ?? [];
unset($_SESSION['category_errors'], $_SESSION['category_old']);
?>
<div class="space-y-6" x-data="categoryManager()">
    <!-- Başlık ve Yeni Ekle Butonu -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-light tracking-tight text-stone-800">Kategoriler</h1>
            <p class="mt-1 text-sm text-stone-500">Kategori yönetimi ve düzenleme</p>
        </div>
        <button @click="openForm()" 
                class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-black">
            Yeni Ekle
        </button>
    </div>

    <!-- Kategoriler Tablosu -->
    <div class="overflow-hidden rounded-xl border border-stone-200 bg-[#FAFAF9] shadow-sm">
        <?php if (empty($categories)): ?>
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="mt-4 text-sm font-medium text-stone-500">Henüz kategori yok</p>
                <p class="mt-1 text-xs text-stone-400">"Yeni Ekle" butonu ile kategori ekleyebilirsiniz</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-200">
                    <thead class="bg-stone-50/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-stone-500">Görsel</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-stone-500">İsim</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-stone-500">Slug</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-stone-500">Durum</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-stone-500">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200 bg-[#FAFAF9]">
                        <?php foreach ($categories as $c): ?>
                            <tr class="hover:bg-stone-50/50 transition-colors">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <?php if (!empty($c['image'])): ?>
                                        <div class="h-12 w-12 overflow-hidden rounded-md">
                                            <img src="<?= htmlspecialchars($baseUrl . $c['image']) ?>" 
                                                 alt="<?= htmlspecialchars($c['name']) ?>" 
                                                 class="h-full w-full object-cover"
                                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'flex h-full w-full items-center justify-center bg-stone-100 text-xs font-medium text-stone-400 rounded-md\'>' + '<?= mb_substr(htmlspecialchars($c['name']), 0, 2) ?>'.toUpperCase() + '</div>'">
                                        </div>
                                    <?php else: ?>
                                        <div class="flex h-12 w-12 items-center justify-center rounded-md bg-stone-100 text-xs font-medium text-stone-400">
                                            <?= mb_strtoupper(mb_substr($c['name'], 0, 2)) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-stone-800"><?= htmlspecialchars($c['name']) ?></p>
                                        <?php if (!empty($c['parent_name'])): ?>
                                            <span class="text-xs text-stone-400">(<?= htmlspecialchars($c['parent_name']) ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs text-stone-400"><?= htmlspecialchars($c['slug']) ?></p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="h-2 w-2 rounded-full <?= (int) $c['is_active'] ? 'bg-emerald-500' : 'bg-stone-300' ?>"></span>
                                        <span class="text-xs font-medium <?= (int) $c['is_active'] ? 'text-emerald-700' : 'text-stone-500' ?>">
                                            <?= (int) $c['is_active'] ? 'Aktif' : 'Pasif' ?>
                                        </span>
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <div class="flex items-center justify-end gap-3">
                                        <button @click="editCategory(<?= (int) $c['id'] ?>)" 
                                                class="text-stone-400 transition-colors hover:text-stone-800"
                                                title="Düzenle">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button @click="deleteCategory(<?= (int) $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['name'])) ?>')" 
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
        <?php endif; ?>
    </div>

    <!-- Slide-Over Form Modal -->
    <div x-show="formOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-hidden"
         @click.away="closeForm()"
         @keydown.escape.window="closeForm()"
         style="display: none;">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="closeForm()"></div>
        
        <!-- Slide Panel -->
        <div class="absolute right-0 top-0 h-full w-full max-w-2xl bg-[#FAFAF9] shadow-xl"
             @click.stop
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full">
            <div class="flex h-full flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-stone-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-stone-800" x-text="formMode === 'create' ? 'Yeni Kategori' : 'Kategori Düzenle'"></h2>
                    <button @click="closeForm()" class="rounded-md p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-800">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Form Content -->
                <form @submit.prevent="submitForm()"
                      enctype="multipart/form-data"
                      class="flex-1 overflow-y-auto px-6 py-6">
                    <?php if (!empty($errors)): ?>
                        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-100 px-4 py-3 text-sm text-rose-800">
                            <ul class="list-disc list-inside space-y-1">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Görsel Yükleme -->
                    <div class="mb-6">
                        <label class="mb-2 block text-sm font-medium text-stone-700">
                            Kategori Görseli
                            <span class="ml-2 text-xs font-normal text-stone-500">(Önerilen: 800x600px veya 1200x900px)</span>
                        </label>
                        <div class="relative">
                            <input type="file" 
                                   name="image" 
                                   id="category_image"
                                   accept="image/*"
                                   @change="previewImage($event)"
                                   class="hidden">
                            <label for="category_image" 
                                   class="flex h-48 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-stone-300 bg-stone-50 transition-colors hover:border-stone-400 hover:bg-stone-100">
                                <template x-if="!imagePreview">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <p class="mt-2 text-sm text-stone-600">Görsel seçmek için tıklayın</p>
                                        <p class="mt-1 text-xs text-stone-400">veya sürükle bırak</p>
                                    </div>
                                </template>
                                <template x-if="imagePreview">
                                    <img :src="imagePreview" alt="Önizleme" class="h-full w-full object-cover rounded-lg">
                                </template>
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-stone-500">
                            <strong>Not:</strong> Ana sayfada gösterilecek kategori containerları için bu görsel kullanılır. 
                            İlk 5 kategori (sıraya göre) ana sayfada gösterilir.
                        </p>
                    </div>

                    <!-- İsim ve Slug -->
                    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="name" class="mb-2 block text-sm font-medium text-stone-700">
                                Kategori Adı <span class="text-rose-600">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   x-model="formData.name"
                                   @input="autoSlug()"
                                   required
                                   class="w-full rounded-lg border border-stone-200 bg-white px-4 py-2.5 text-sm text-stone-800 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-400">
                        </div>
                        <div>
                            <label for="slug" class="mb-2 block text-sm font-medium text-stone-700">Slug</label>
                            <input type="text" 
                                   id="slug" 
                                   name="slug" 
                                   x-model="formData.slug"
                                   @input="onSlugInput()"
                                   class="w-full rounded-lg border border-stone-200 bg-white px-4 py-2.5 text-sm text-stone-800 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-400">
                        </div>
                    </div>

                    <!-- Üst Kategori -->
                    <div class="mb-6">
                        <label for="parent_id" class="mb-2 block text-sm font-medium text-stone-700">Üst Kategori</label>
                        <select id="parent_id" 
                                name="parent_id" 
                                x-model="formData.parent_id"
                                class="w-full rounded-lg border border-stone-200 bg-white px-4 py-2.5 text-sm text-stone-800 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-400">
                            <option value="">— Yok (ana kategori) —</option>
                            <?php foreach ($parents as $p): ?>
                                <option value="<?= (int) $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Açıklama -->
                    <div class="mb-6">
                        <label for="description" class="mb-2 block text-sm font-medium text-stone-700">Açıklama</label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  x-model="formData.description"
                                  class="w-full rounded-lg border border-stone-200 bg-white px-4 py-2.5 text-sm text-stone-800 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-400"></textarea>
                    </div>

                    <!-- Ana Sayfa Hero Metni -->
                    <div class="mb-6">
                        <label for="home_hero_text" class="mb-2 block text-sm font-medium text-stone-700">
                            Ana Sayfa Hero Metni
                            <span class="ml-2 text-xs font-normal text-stone-500">(Ana sayfa kategori containerında gösterilecek metin)</span>
                        </label>
                        <input type="text" 
                               id="home_hero_text" 
                               name="home_hero_text" 
                               x-model="formData.home_hero_text"
                               placeholder="Örn: Koleksiyonu Keşfet"
                               maxlength="255"
                               class="w-full rounded-lg border border-stone-200 bg-white px-4 py-2.5 text-sm text-stone-800 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-400">
                        <p class="mt-2 text-xs text-stone-500">
                            Bu metin ana sayfadaki kategori containerında kategori adının altında gösterilir. 
                            Boş bırakılırsa gösterilmez. Maksimum 5 kategori ana sayfada gösterilir (sıraya göre).
                        </p>
                    </div>

                    <!-- Sıra ve Durum -->
                    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="sort_order" class="mb-2 block text-sm font-medium text-stone-700">Sıra</label>
                            <input type="number" 
                                   id="sort_order" 
                                   name="sort_order" 
                                   x-model="formData.sort_order"
                                   min="0"
                                   class="w-full rounded-lg border border-stone-200 bg-white px-4 py-2.5 text-sm text-stone-800 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-400">
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1"
                                       x-model="formData.is_active"
                                       class="h-4 w-4 rounded border-stone-300 text-stone-900 focus:ring-stone-400">
                                <span class="text-sm font-medium text-stone-700">Aktif</span>
                            </label>
                        </div>
                    </div>

                    <!-- SEO Ayarları (Accordion) -->
                    <div class="mb-6" x-data="{ seoOpen: false }">
                        <button type="button" 
                                @click="seoOpen = !seoOpen"
                                class="flex w-full items-center justify-between rounded-lg border border-stone-200 bg-white px-4 py-3 text-left text-sm font-medium text-stone-700 hover:bg-stone-50">
                            <span>SEO Ayarları</span>
                            <svg class="h-5 w-5 text-stone-400 transition-transform" 
                                 :class="{ 'rotate-180': seoOpen }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="seoOpen" 
                             x-collapse
                             class="mt-4 space-y-4 rounded-lg border border-stone-200 bg-white p-4">
                            <div>
                                <label for="meta_title" class="mb-2 block text-sm font-medium text-stone-700">Meta Başlık</label>
                                <input type="text" 
                                       id="meta_title" 
                                       name="meta_title" 
                                       x-model="formData.meta_title"
                                       class="w-full rounded-lg border border-stone-200 bg-white px-4 py-2.5 text-sm text-stone-800 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-400">
                            </div>
                            <div>
                                <label for="meta_description" class="mb-2 block text-sm font-medium text-stone-700">Meta Açıklama</label>
                                <textarea id="meta_description" 
                                          name="meta_description" 
                                          rows="3"
                                          x-model="formData.meta_description"
                                          class="w-full rounded-lg border border-stone-200 bg-white px-4 py-2.5 text-sm text-stone-800 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-400"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Butonları -->
                    <div class="flex items-center justify-end gap-3 border-t border-stone-200 pt-6">
                        <button type="button" 
                                @click="closeForm()"
                                class="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50">
                            Vazgeç
                        </button>
                        <button type="submit" 
                                class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-black">
                            Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Silme Onay Modalı -->
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
                <h3 class="text-lg font-semibold text-stone-800 mb-2">Kategoriyi Sil</h3>
                <p class="text-sm text-stone-600 mb-6">
                    "<span x-text="deleteConfirmData.name"></span>" kategorisini silmek istediğinize emin misiniz?
                </p>
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
function categoryManager() {
    return {
        formOpen: false,
        formMode: 'create',
        formId: null,
        imagePreview: null,
        formData: {
            name: '',
            slug: '',
            parent_id: '',
            description: '',
            sort_order: 0,
            is_active: true,
            home_hero_text: '',
            meta_title: '',
            meta_description: '',
        },
        originalSlug: '',
        slugManuallyChanged: false,
        openForm() {
            this.formOpen = true;
            this.formMode = 'create';
            this.formId = null;
            this.resetForm();
        },
        async editCategory(id) {
            try {
                const response = await fetch('<?= htmlspecialchars($baseUrl) ?>/admin/categories/edit?id=' + id);
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('JSON bekleniyordu ama HTML döndü');
                }
                const data = await response.json();
                if (data.category) {
                    this.formMode = 'edit';
                    this.formId = id;
                    this.formData = {
                        name: data.category.name || '',
                        slug: data.category.slug || '',
                        parent_id: data.category.parent_id || '',
                        description: data.category.description || '',
                        sort_order: data.category.sort_order || 0,
                        is_active: data.category.is_active == 1,
                        home_hero_text: data.category.home_hero_text || '',
                        meta_title: data.category.meta_title || '',
                        meta_description: data.category.meta_description || '',
                    };
                    this.originalSlug = data.category.slug || '';
                    this.slugManuallyChanged = false;
                    if (data.category.image) {
                        this.imagePreview = '<?= htmlspecialchars($baseUrl) ?>' + data.category.image;
                    } else {
                        this.imagePreview = null;
                    }
                    this.formOpen = true;
                }
            } catch (error) {
                console.error('Kategori yüklenirken hata:', error);
                const errorText = await error.response?.text() || '';
                if (errorText.includes('<!DOCTYPE')) {
                    this.showToast('Kategori yüklenirken bir hata oluştu. Sayfayı yenileyin.', 'error');
                } else {
                    this.showToast('Kategori yüklenirken bir hata oluştu.', 'error');
                }
            }
        },
        async deleteCategory(id, name) {
            this.showDeleteConfirm(name, () => {
                this.performDelete(id);
            });
        },
        showDeleteConfirm(name, callback) {
            this.deleteConfirmData = {
                show: true,
                name: name,
                callback: callback
            };
        },
        async performDelete(id) {
            try {
                // Önce kontrol et
                const checkResponse = await fetch('<?= htmlspecialchars($baseUrl) ?>/admin/categories/delete?id=' + id);
                if (!checkResponse.ok) {
                    throw new Error('HTTP ' + checkResponse.status);
                }
                const checkData = await checkResponse.json();
                if (checkData.has_children) {
                    this.showToast('Bu kategorinin alt kategorileri var. Önce alt kategorileri silin.', 'error');
                    this.deleteConfirmData.show = false;
                    return;
                }
                // POST ile sil
                const deleteResponse = await fetch('<?= htmlspecialchars($baseUrl) ?>/admin/categories/delete?id=' + id, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (deleteResponse.ok) {
                    const contentType = deleteResponse.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        const data = await deleteResponse.json();
                        this.showToast(data.message || 'Kategori başarıyla silindi.', 'success');
                    } else {
                        this.showToast('Kategori başarıyla silindi.', 'success');
                    }
                    this.deleteConfirmData.show = false;
                    // Sayfayı yenile
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    throw new Error('Silme başarısız');
                }
            } catch (error) {
                console.error('Silme hatası:', error);
                this.showToast('Kategori silinirken bir hata oluştu.', 'error');
                this.deleteConfirmData.show = false;
            }
        },
        async submitForm() {
            const form = event.target;
            const formData = new FormData(form);
            const url = this.formMode === 'create' 
                ? '<?= htmlspecialchars($baseUrl) ?>/admin/categories/create'
                : '<?= htmlspecialchars($baseUrl) ?>/admin/categories/edit?id=' + this.formId;
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (data.success) {
                        this.showToast(data.message || 'İşlem başarılı.', 'success');
                        this.closeForm();
                        // Sayfayı yenile
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        // Hata mesajları
                        if (data.errors) {
                            const errorMessages = Object.values(data.errors).flat();
                            this.showToast(errorMessages.join(', '), 'error');
                        } else {
                            this.showToast(data.message || 'Bir hata oluştu.', 'error');
                        }
                    }
                } else {
                    // HTML response (normal form submit)
                    if (response.ok) {
                        this.showToast(
                            this.formMode === 'create' ? 'Kategori başarıyla eklendi.' : 'Kategori başarıyla güncellendi.',
                            'success'
                        );
                        this.closeForm();
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        const text = await response.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(text, 'text/html');
                        const errorDiv = doc.querySelector('.border-rose-200');
                        if (errorDiv) {
                            this.showToast(errorDiv.textContent.trim(), 'error');
                        } else {
                            this.showToast('Bir hata oluştu.', 'error');
                        }
                    }
                }
            } catch (error) {
                console.error('Form gönderme hatası:', error);
                this.showToast('Form gönderilirken bir hata oluştu.', 'error');
            }
        },
        deleteConfirmData: {
            show: false,
            name: '',
            callback: null
        },
        showToast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { message: message, type: type } 
            }));
        },
        closeForm() {
            this.formOpen = false;
            setTimeout(() => {
                this.resetForm();
            }, 300);
        },
        resetForm() {
            this.formData = {
                name: '',
                slug: '',
                parent_id: '',
                description: '',
                sort_order: 0,
                is_active: true,
                home_hero_text: '',
                meta_title: '',
                meta_description: '',
            };
            this.originalSlug = '';
            this.slugManuallyChanged = false;
            this.imagePreview = null;
            const fileInput = document.getElementById('category_image');
            if (fileInput) fileInput.value = '';
        },
        previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        autoSlug() {
            if (this.formMode === 'create') {
                if (!this.formData.slug || !this.slugManuallyChanged) {
                    this.formData.slug = this.slugify(this.formData.name);
                }
            } else {
                // Edit modunda: slug manuel değiştirilmemişse kategori adından güncelle
                if (!this.slugManuallyChanged) {
                    this.formData.slug = this.slugify(this.formData.name);
                }
            }
        },
        onSlugInput() {
            this.slugManuallyChanged = true;
        },
        slugify(text) {
            const map = {
                'ı': 'i', 'ğ': 'g', 'ü': 'u', 'ş': 's', 'ö': 'o', 'ç': 'c',
                'İ': 'i', 'Ğ': 'g', 'Ü': 'u', 'Ş': 's', 'Ö': 'o', 'Ç': 'c'
            };
            return text
                .toLowerCase()
                .split('')
                .map(char => map[char] || char)
                .join('')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    }
}
</script>
