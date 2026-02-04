<?php $baseUrl = $baseUrl ?? ''; ?>
<footer class="bg-[#111111] text-gray-400 pt-20 pb-8">
    <div class="max-w-[1400px] mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 md:gap-8">
            <!-- 1. Marka (Sol Baş) -->
            <div>
                <a href="<?= $baseUrl ?>/" class="font-display text-2xl text-white tracking-widest mb-6 block">LUMINA</a>
                <p class="text-sm leading-relaxed text-gray-400 max-w-xs">Sessiz lüksün ve zamansız tasarımın adresi. Modern estetik, geleneksel işçilikle buluşuyor.</p>
                <div class="flex gap-4 mt-6">
                    <a href="#" class="text-gray-400 hover:text-white transition" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition" aria-label="Pinterest">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.214 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.123.345 2.306.535 3.55.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition" aria-label="X (Twitter)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                </div>
            </div>

            <!-- 2. Alışveriş (KOLEKSİYON) -->
            <div>
                <h3 class="text-xs text-white font-bold tracking-widest uppercase mb-6">KOLEKSİYON</h3>
                <ul class="space-y-4">
                    <li><a href="<?= $baseUrl ?>/new-arrivals.php" class="text-sm text-gray-400 hover:text-white transition">Yeni Gelenler</a></li>
                    <li><a href="<?= $baseUrl ?>/women.php" class="text-sm text-gray-400 hover:text-white transition">Kadın</a></li>
                    <li><a href="<?= $baseUrl ?>/men.php" class="text-sm text-gray-400 hover:text-white transition">Erkek</a></li>
                    <li><a href="<?= $baseUrl ?>/accessories.php" class="text-sm text-gray-400 hover:text-white transition">Aksesuar</a></li>
                    <li><a href="<?= $baseUrl ?>/sale.php" class="text-sm text-gray-400 hover:text-white transition">İndirim</a></li>
                </ul>
            </div>

            <!-- 3. Müşteri Hizmetleri -->
            <div>
                <h3 class="text-xs text-white font-bold tracking-widest uppercase mb-6">MÜŞTERİ HİZMETLERİ</h3>
                <ul class="space-y-4">
                    <li><a href="<?= $baseUrl ?>/siparis-takip" class="text-sm text-gray-400 hover:text-white transition">Sipariş Takibi</a></li>
                    <li><a href="<?= $baseUrl ?>/sayfa/iade-kosullari" class="text-sm text-gray-400 hover:text-white transition">İade ve Değişim</a></li>
                    <li><a href="<?= $baseUrl ?>/sayfa/kargo" class="text-sm text-gray-400 hover:text-white transition">Kargo Politikası</a></li>
                    <li><a href="<?= $baseUrl ?>/iletisim" class="text-sm text-gray-400 hover:text-white transition">Bize Ulaşın</a></li>
                    <li><a href="<?= $baseUrl ?>/sss" class="text-sm text-gray-400 hover:text-white transition">SSS</a></li>
                </ul>
            </div>

            <!-- 4. Bülten (LUMINA CLUB) -->
            <div>
                <h3 class="text-xs text-white font-bold tracking-widest uppercase mb-6">LUMINA CLUB</h3>
                <p class="text-xs text-gray-400 mb-4 leading-relaxed">Yeni koleksiyonlardan ve özel davetlerden ilk siz haberdar olun.</p>
                <form action="#" method="post" class="flex flex-col gap-3">
                    <input type="email" name="newsletter_email" placeholder="E-posta adresiniz" required class="bg-transparent border border-gray-800 focus:border-white text-white px-4 py-3 text-sm outline-none transition placeholder-gray-500">
                    <button type="submit" class="bg-white text-black px-4 py-3 text-xs tracking-widest uppercase font-bold hover:bg-gray-200 transition">ABONE OL</button>
                </form>
            </div>
        </div>

        <!-- Alt Bar: Copyright & Ödeme ikonları -->
        <div class="border-t border-gray-900 mt-20 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-gray-500 text-sm">&copy; <?= date('Y') ?> Lumina Fashion. Tüm hakları saklıdır.</p>
            <div class="flex items-center gap-6 flex-wrap justify-center md:justify-end" aria-label="Ödeme yöntemleri">
                <!-- Visa -->
                <span class="inline-flex items-center text-gray-400 font-bold text-sm tracking-widest" aria-label="Visa" title="Visa">VISA</span>
                <!-- Mastercard (ikon) -->
                <span class="inline-flex items-center" aria-label="Mastercard" title="Mastercard">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="24" viewBox="0 0 36 24" fill="none" class="h-6 w-auto">
                        <circle cx="14" cy="12" r="9" fill="#EB001B"/>
                        <circle cx="22" cy="12" r="9" fill="#F79E1B"/>
                        <path d="M18 6.2a12 12 0 0 1 0 11.6 12 12 0 0 1 0-11.6z" fill="#FF5F00"/>
                    </svg>
                </span>
                <!-- American Express -->
                <span class="inline-flex items-center text-gray-400 font-semibold text-xs tracking-wider" aria-label="American Express" title="American Express">AMEX</span>
                <!-- Troy (Türkiye yerli kart) -->
                <span class="inline-flex items-center text-gray-400 font-semibold text-xs tracking-wider" aria-label="Troy" title="Troy">TROY</span>
            </div>
        </div>
    </div>
</footer>
