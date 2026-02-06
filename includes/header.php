<?php
$baseUrl = $baseUrl ?? '';
$cartCount = !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$isLoggedIn = !empty($_SESSION['user_id']);

$menuItems = [];
if (class_exists(\App\Config\Database::class)) {
    try {
        $pdo = \App\Config\Database::getConnection();
        $topCategories = $pdo->query("SELECT id, name, slug FROM categories WHERE (parent_id IS NULL OR parent_id = 0) AND is_active = 1 ORDER BY sort_order ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $allCategories = $pdo->query("SELECT id, name, slug, parent_id FROM categories WHERE is_active = 1 ORDER BY parent_id ASC, sort_order ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($topCategories as $top) {
            $children = array_values(array_filter($allCategories, function ($c) use ($top) {
                return (int) ($c['parent_id'] ?? 0) === (int) $top['id'];
            }));
            $slug = strtolower($top['slug'] ?? '');
            $item = [
                'label' => mb_strtoupper($top['name']),
                'link' => 'kategori/' . $top['slug'],
                'color' => in_array($slug, ['sale', 'indirim'], true) ? 'text-[#991b1b]' : 'text-primary',
            ];
            if (in_array($slug, ['sale', 'indirim'], true)) {
                $item['icon'] = 'tag';
            }
            if (!empty($children)) {
                $item['submenu'] = array_map(function ($c) {
                    return ['label' => $c['name'], 'link' => 'kategori/' . $c['slug']];
                }, $children);
            }
            $menuItems[] = $item;
        }
    } catch (Throwable $e) {
        $menuItems = [];
    }
}
if (empty($menuItems)) {
    $menuItems = [['label' => 'KATEGORİLER', 'link' => '#', 'color' => 'text-primary']];
}
?>
<?php $headerBaseUrl = $baseUrl; ?>
<div class="header-wrap" data-base-url="<?= htmlspecialchars($headerBaseUrl) ?>"
     x-data="{
         mobileMenuOpen: false,
         searchOpen: false,
         get baseUrl() { return this.$el.dataset.baseUrl || ''; },
         searchQuery: '',
         suggestProducts: [],
         suggestCategories: [],
         suggestLoading: false,
         suggestDebounce: null,
         async fetchSuggest() {
             const q = this.searchQuery.trim();
             if (q.length < 1) { this.suggestProducts = []; this.suggestCategories = []; return; }
             this.suggestLoading = true;
             try {
                 const r = await fetch(this.baseUrl + '/arama/suggest?q=' + encodeURIComponent(q));
                 const d = await r.json();
                 this.suggestProducts = d.products || [];
                 this.suggestCategories = d.categories || [];
             } catch (e) {
                 this.suggestProducts = [];
                 this.suggestCategories = [];
             }
             this.suggestLoading = false;
         },
         onSearchInput() {
             clearTimeout(this.suggestDebounce);
             this.suggestDebounce = setTimeout(() => this.fetchSuggest(), 280);
         }
     }">
<div class="w-full bg-white/95 backdrop-blur-sm border-b border-black/5 transition-all duration-300">
    <div class="max-w-[1400px] mx-auto px-6 h-20">
        <div class="grid grid-cols-[1fr_auto_1fr] items-center h-full">
            <!-- SOL: Navigasyon (desktop) + Hamburger (mobil) -->
            <div class="flex items-center">
                <nav class="hidden md:flex gap-8">
                    <?php foreach ($menuItems as $item): ?>
                        <?php $hasSub = !empty($item['submenu']); ?>
                        <?php $linkClass = 'text-[11px] font-medium tracking-luxury uppercase transition ' . ($item['color'] ?? 'text-secondary hover:text-primary'); ?>
                        <?php if ($hasSub): ?>
                            <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                                <a href="<?= $baseUrl ?>/<?= htmlspecialchars($item['link']) ?>" class="<?= $linkClass ?> flex items-center gap-1">
                                    <?= htmlspecialchars($item['label']) ?>
                                </a>
                                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="hidden absolute top-full left-0 w-48 bg-white border border-gray-100 shadow-lg py-4 z-50" @click.outside="open = false">
                                    <?php foreach ($item['submenu'] as $sub): ?>
                                        <a href="<?= $baseUrl ?>/<?= htmlspecialchars($sub['link']) ?>" class="block px-6 py-2 text-[10px] text-gray-500 hover:text-black tracking-widest uppercase transition"><?= htmlspecialchars($sub['label']) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="<?= $baseUrl ?>/<?= htmlspecialchars($item['link']) ?>" class="<?= $linkClass ?> flex items-center gap-1">
                                <?php if (!empty($item['icon']) && $item['icon'] === 'tag'): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                                <?php endif; ?>
                                <?= htmlspecialchars($item['label']) ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
                <button type="button" @click="mobileMenuOpen = true" class="md:hidden p-2 text-primary" aria-label="Menüyü aç">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>

            <!-- ORTA: Logo -->
            <div class="flex justify-center">
                <a href="<?= $baseUrl ?>/" class="text-2xl md:text-3xl font-display font-bold tracking-tighter text-primary block">LUMINA</a>
            </div>

            <!-- SAĞ: İkonlar -->
            <div class="flex items-center justify-end gap-6">
                <button type="button" @click="searchOpen = true" class="text-primary hover:opacity-70 transition" aria-label="Ara">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>
                <?php if ($isLoggedIn): ?>
                    <div class="hidden md:block relative" x-data="{ accountOpen: false }" @click.outside="accountOpen = false">
                        <button type="button" @click="accountOpen = !accountOpen" class="text-primary hover:opacity-70 transition p-1 -m-1" aria-label="Hesabım" :aria-expanded="accountOpen">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998-0.059A7.5 7.5 0 0 1 4.5 20.118Z" />
                            </svg>
                        </button>
                        <div x-show="accountOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="absolute right-0 top-full mt-2 w-44 bg-white border border-gray-100 shadow-lg py-2 z-50">
                            <a href="<?= $baseUrl ?>/hesabim" class="block px-5 py-2.5 text-[11px] text-gray-600 hover:text-primary tracking-widest uppercase transition">Hesabım</a>
                            <a href="<?= $baseUrl ?>/cikis" class="block px-5 py-2.5 text-[11px] text-gray-600 hover:text-primary tracking-widest uppercase transition border-t border-gray-100">Çıkış yap</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>/giris" class="hidden md:block text-primary hover:opacity-70 transition" aria-label="Giriş yap">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998-0.059A7.5 7.5 0 0 1 4.5 20.118Z" />
                        </svg>
                    </a>
                <?php endif; ?>
                <div x-data="{ cartCount: <?= (int) ($cartCount ?? 0) ?>, badgePulse: false }" @cart-updated.window="cartCount++; badgePulse = true; setTimeout(() => badgePulse = false, 400)" @cart-count-updated.window="cartCount = $event.detail.count; badgePulse = true; setTimeout(() => badgePulse = false, 400)">
                    <a href="<?= $baseUrl ?>/sepet" @click.prevent="$dispatch('cart-open')" class="relative text-primary hover:opacity-70 transition block cursor-pointer" aria-label="Sepet (çekmeceyi aç)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12c.07-.404.323-.747.745-.933H3.75a.75.75 0 0 0-.75.75v.75c0 .414.336.75.75.75h14.5a.75.75 0 0 0 .75-.75V3a.75.75 0 0 0-.75-.75h-.745a1.125 1.125 0 0 1-.745.933Z" />
                        </svg>
                        <span x-show="cartCount > 0"
                              x-cloak
                              class="absolute -top-1 -right-2 bg-black text-white text-[9px] min-w-4 h-4 px-1 flex items-center justify-center rounded-full font-medium transition-transform duration-150 origin-center"
                              :class="{ 'scale-125': badgePulse }">
                            <span x-text="cartCount > 99 ? '99+' : cartCount"></span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Arama barı + canlı öneri dropdown (autocomplete kapalı, ürün/kategori önerisi) -->
    <div x-show="searchOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute left-0 right-0 top-full bg-white border-b border-black/5 shadow-sm z-50" @click.outside="searchOpen = false; searchQuery = ''; suggestProducts = []; suggestCategories = []">
        <div class="max-w-[1400px] mx-auto px-6 py-4">
            <form action="<?= $baseUrl ?>/arama" method="get" class="flex gap-3 relative flex-wrap">
                <input type="search" name="q" x-model="searchQuery" @input="onSearchInput()" autocomplete="off" placeholder="Ürün veya kategori ara..." class="flex-1 border-b border-black/10 py-2 text-primary placeholder:text-secondary focus:outline-none focus:border-primary text-sm" aria-label="Arama" aria-autocomplete="list" aria-controls="search-suggest-list" :aria-expanded="searchQuery.length > 0 && (suggestProducts.length > 0 || suggestCategories.length > 0)">
                <button type="submit" class="text-[11px] font-medium tracking-luxury uppercase text-primary hover:text-gray-500 transition">Ara</button>
                <button type="button" @click="searchOpen = false" class="text-secondary hover:text-primary" aria-label="Kapat">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
                <!-- Dropdown: ürün ve kategori önerileri -->
                <div id="search-suggest-list" x-show="searchQuery.trim().length >= 1 && (suggestProducts.length > 0 || suggestCategories.length > 0 || suggestLoading)" x-cloak class="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 shadow-lg max-h-[70vh] overflow-y-auto z-50 rounded-b-sm"
                     x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <template x-if="suggestLoading">
                        <div class="px-4 py-6 text-center text-sm text-gray-500">Yükleniyor...</div>
                    </template>
                    <template x-if="!suggestLoading && (suggestCategories.length > 0 || suggestProducts.length > 0)">
                        <div class="py-2">
                            <template x-if="suggestCategories.length > 0">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-[10px] uppercase tracking-widest text-gray-400 mb-2">Kategoriler</p>
                                    <ul class="space-y-0.5">
                                        <template x-for="c in suggestCategories" :key="c.id">
                                            <li>
                                                <a :href="baseUrl + '/kategori/' + c.slug" class="block py-2 px-2 text-sm text-primary hover:bg-gray-50 rounded-sm transition" x-text="c.name" @click="searchOpen = false"></a>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </template>
                            <template x-if="suggestProducts.length > 0">
                                <div class="px-4 py-2">
                                    <p class="text-[10px] uppercase tracking-widest text-gray-400 mb-2">Ürünler</p>
                                    <ul class="space-y-0.5">
                                        <template x-for="p in suggestProducts" :key="p.id">
                                            <li>
                                                <a :href="baseUrl + '/urun/' + p.slug" class="flex items-center gap-3 py-2 px-2 text-sm text-primary hover:bg-gray-50 rounded-sm transition group">
                                                    <span class="flex-shrink-0 w-10 h-10 bg-gray-100 rounded-sm overflow-hidden" x-show="p.image">
                                                        <img :src="baseUrl + '/' + p.image" :alt="p.name" class="w-full h-full object-cover">
                                                    </span>
                                                    <span class="flex-1 min-w-0 truncate" x-text="p.name"></span>
                                                    <span class="text-xs text-gray-500 flex-shrink-0" x-text="(p.sale_price && parseFloat(p.sale_price) > 0 ? p.sale_price : p.price) + ' ₺'"></span>
                                                </a>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!suggestLoading && searchQuery.trim().length >= 1 && suggestProducts.length === 0 && suggestCategories.length === 0">
                        <div class="px-4 py-6 text-center text-sm text-gray-500">Sonuç bulunamadı. Tam arama için &quot;Ara&quot; butonuna basın.</div>
                    </template>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mobil menü (slide-over): hidden kullanılmaz, x-show görünürlüğü kontrol eder -->
<div x-show="mobileMenuOpen" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-[-100%]" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-[-100%]" class="fixed inset-0 z-[60] bg-white">
    <div class="flex flex-col h-full pt-8 pb-12 px-8">
        <div class="flex items-center justify-between">
            <a href="<?= $baseUrl ?>/" class="text-3xl font-display font-bold tracking-tighter text-primary" @click="mobileMenuOpen = false">LUMINA</a>
            <button type="button" @click="mobileMenuOpen = false" class="p-2 text-primary hover:opacity-70" aria-label="Menüyü kapat">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <nav class="flex-1 flex flex-col justify-center gap-8">
            <?php foreach ($menuItems as $item): ?>
                <?php $hasSub = !empty($item['submenu']); ?>
                <?php $mobileLinkClass = 'text-3xl font-display font-medium tracking-tight transition ' . ($item['color'] ?? 'text-secondary hover:text-primary'); ?>
                <?php if ($hasSub): ?>
                    <div x-data="{ expanded: false }">
                        <div class="flex items-center justify-between gap-4">
                            <a href="<?= $baseUrl ?>/<?= htmlspecialchars($item['link']) ?>" class="<?= $mobileLinkClass ?>" @click="mobileMenuOpen = false"><?= htmlspecialchars($item['label']) ?></a>
                            <button type="button" @click="expanded = !expanded" class="p-2 text-primary hover:opacity-70 flex-shrink-0" aria-label="Alt menüyü aç veya kapat">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': expanded }">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </div>
                        <div x-show="expanded" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex flex-col gap-3 mt-3">
                            <?php foreach ($item['submenu'] as $sub): ?>
                                <a href="<?= $baseUrl ?>/<?= htmlspecialchars($sub['link']) ?>" class="text-lg text-gray-500 hover:text-primary pl-6 tracking-tight transition" @click="mobileMenuOpen = false"><?= htmlspecialchars($sub['label']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>/<?= htmlspecialchars($item['link']) ?>" class="<?= $mobileLinkClass ?> flex items-center gap-2" @click="mobileMenuOpen = false">
                        <?php if (!empty($item['icon']) && $item['icon'] === 'tag'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                        <?php endif; ?>
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
            <div class="pt-8 mt-8 border-t border-gray-200">
                <?php if ($isLoggedIn): ?>
                    <a href="<?= $baseUrl ?>/hesabim" class="block text-lg text-gray-500 hover:text-primary tracking-tight transition mb-3" @click="mobileMenuOpen = false">Hesabım</a>
                    <a href="<?= $baseUrl ?>/cikis" class="block text-lg text-gray-500 hover:text-primary tracking-tight transition" @click="mobileMenuOpen = false">Çıkış yap</a>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>/giris" class="block text-lg text-gray-500 hover:text-primary tracking-tight transition" @click="mobileMenuOpen = false">Giriş yap</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</div>

<div class="h-20" aria-hidden="true"></div>
</div>
