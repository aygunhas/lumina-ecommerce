<?php
$baseUrl = $baseUrl ?? '';
$cartCount = !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<div x-data="{ mobileMenuOpen: false, searchOpen: false }">
<div class="fixed w-full top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-black/5 transition-all duration-300">
    <div class="max-w-[1400px] mx-auto px-6 h-20">
        <div class="grid grid-cols-[1fr_auto_1fr] items-center h-full">
            <!-- SOL: Navigasyon (desktop) + Hamburger (mobil) -->
            <div class="flex items-center">
                <nav class="hidden md:flex gap-8">
                    <a href="<?= $baseUrl ?>/new-arrivals.php" class="text-[11px] font-medium tracking-luxury uppercase text-primary hover:text-primary transition">YENİLER</a>
                    <a href="<?= $baseUrl ?>/women.php" class="text-[11px] font-medium tracking-luxury uppercase text-secondary hover:text-primary transition">KADIN</a>
                    <a href="<?= $baseUrl ?>/men.php" class="text-[11px] font-medium tracking-luxury uppercase text-secondary hover:text-primary transition">ERKEK</a>
                    <a href="<?= $baseUrl ?>/accessories.php" class="text-[11px] font-medium tracking-luxury uppercase text-secondary hover:text-primary transition">AKSESUAR</a>
                    <a href="<?= $baseUrl ?>/sale.php" class="text-[11px] font-medium tracking-luxury uppercase text-[#991b1b] hover:text-primary transition flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                        İndirim
                    </a>
                </nav>
                <button type="button" @click="mobileMenuOpen = true" class="md:hidden p-2 text-primary" aria-label="Menüyü aç">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>

            <!-- ORTA: Logo (footer ile aynı – metin LUMINA) -->
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
                <a href="<?= $baseUrl ?>/sepet" class="relative text-primary hover:opacity-70 transition" aria-label="Sepet">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12c.07-.404.323-.747.745-.933H3.75a.75.75 0 0 0-.75.75v.75c0 .414.336.75.75.75h14.5a.75.75 0 0 0 .75-.75V3a.75.75 0 0 0-.75-.75h-.745a1.125 1.125 0 0 1-.745.933Z" />
                    </svg>
                    <?php if ($cartCount > 0): ?>
                        <span class="absolute -top-1 -right-2 bg-black text-white text-[9px] w-4 h-4 flex items-center justify-center rounded-full font-medium"><?= $cartCount > 99 ? '99+' : $cartCount ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Arama barı (searchOpen ile) -->
    <div x-show="searchOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute left-0 right-0 top-full bg-white border-b border-black/5 shadow-sm" @click.outside="searchOpen = false">
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

<!-- Mobil menü (slide-over, soldan sağa kayarak) - aynı x-data içinde -->
<div x-show="mobileMenuOpen" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-[-100%]" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-[-100%]" class="fixed inset-0 z-[60] bg-white" style="display: none;">
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
            <a href="<?= $baseUrl ?>/new-arrivals.php" class="text-3xl font-display font-medium text-primary tracking-tight" @click="mobileMenuOpen = false">YENİLER</a>
            <a href="<?= $baseUrl ?>/women.php" class="text-3xl font-display font-medium text-secondary hover:text-primary tracking-tight transition" @click="mobileMenuOpen = false">KADIN</a>
            <a href="<?= $baseUrl ?>/men.php" class="text-3xl font-display font-medium text-secondary hover:text-primary tracking-tight transition" @click="mobileMenuOpen = false">ERKEK</a>
            <a href="<?= $baseUrl ?>/accessories.php" class="text-3xl font-display font-medium text-secondary hover:text-primary tracking-tight transition" @click="mobileMenuOpen = false">AKSESUAR</a>
            <a href="<?= $baseUrl ?>/sale.php" class="text-3xl font-display font-medium text-[#991b1b] hover:text-primary tracking-tight transition flex items-center gap-2" @click="mobileMenuOpen = false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                İndirim
            </a>
        </nav>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

<!-- Header yüksekliği kadar boşluk (fixed olduğu için içerik üstte kalmasın) -->
<div class="h-20" aria-hidden="true"></div>
</div>
