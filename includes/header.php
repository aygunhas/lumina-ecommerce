<?php
$baseUrl = $baseUrl ?? '';
$cartCount = !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

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
<div x-data="{ mobileMenuOpen: false, searchOpen: false }">
<div class="fixed w-full top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-black/5 transition-all duration-300">
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
                <a href="<?= $baseUrl ?>/hesabim" class="hidden md:block text-primary hover:opacity-70 transition" aria-label="Hesabım">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998-0.059A7.5 7.5 0 0 1 4.5 20.118Z" />
                    </svg>
                </a>
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

    <!-- Arama barı -->
    <div x-show="searchOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="hidden absolute left-0 right-0 top-full bg-white border-b border-black/5 shadow-sm" @click.outside="searchOpen = false">
        <form action="<?= $baseUrl ?>/arama" method="get" class="max-w-[1400px] mx-auto px-6 py-4 flex gap-3">
            <input type="search" name="q" placeholder="Ürün ara..." class="flex-1 border-b border-black/10 py-2 text-primary placeholder:text-secondary focus:outline-none focus:border-primary text-sm" autofocus>
            <button type="submit" class="text-[11px] font-medium tracking-luxury uppercase text-primary hover:text-gray-500 transition">Ara</button>
            <button type="button" @click="searchOpen = false" class="text-secondary hover:text-primary" aria-label="Kapat">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </form>
    </div>
</div>

<!-- Mobil menü (slide-over) -->
<div x-show="mobileMenuOpen" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-[-100%]" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-[-100%]" class="hidden fixed inset-0 z-[60] bg-white">
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
        </nav>
    </div>
</div>

<div class="h-20" aria-hidden="true"></div>
</div>
