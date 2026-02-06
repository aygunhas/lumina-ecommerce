<?php
$baseUrl = $baseUrl ?? '';
$errors = $errors ?? [];
$old = $old ?? [];
$success = $success ?? !empty($_GET['sent']);
?>
<style>.contact-map-filter iframe { filter: grayscale(1) invert(0.1); }</style>
<!-- Sayfa Başlığı -->
<header class="pt-20 pb-12 text-center">
    <h1 class="font-display text-3xl tracking-widest uppercase text-primary">BİZE ULAŞIN</h1>
    <p class="text-gray-500 mt-2 text-sm">Sorularınız, iş birlikleri veya sadece merhaba demek için.</p>
</header>

<!-- Ana Düzen -->
<div class="max-w-[1200px] mx-auto px-6 mb-20" x-data="{ sent: <?= $success ? 'true' : 'false' ?> }">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-24">
        <!-- SOL KOLON: Bilgi & Harita -->
        <div class="space-y-8">
            <div>
                <h2 class="text-xs font-bold tracking-widest uppercase mb-1 text-primary">MAĞAZA</h2>
                <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">Abdi İpekçi Cad. No:42
Nişantaşı, İstanbul</p>
            </div>
            <div>
                <h2 class="text-xs font-bold tracking-widest uppercase mb-1 text-primary">DESTEK</h2>
                <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">hello@lumina.com
+90 (212) 555 0199</p>
            </div>
            <div>
                <h2 class="text-xs font-bold tracking-widest uppercase mb-1 text-primary">ÇALIŞMA SAATLERİ</h2>
                <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">Pzt - Cmt: 10:00 - 20:00
Pazar: 12:00 - 18:00</p>
            </div>
            <!-- Estetik Harita (gri tonlamalı) -->
            <div class="w-full h-64 rounded-sm overflow-hidden contact-map-filter">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3010.279456298!2d28.9922!3d41.0542!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDHCsDAzJzE1LjEiTiAyOMKwNTknMzIuMCJF!5e0!3m2!1str!2str!4v1600000000000!5m2!1str!2str"
                    width="100%"
                    height="100%"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Lumina Mağaza Konumu"
                ></iframe>
            </div>
        </div>

        <!-- SAĞ KOLON: İletişim Formu -->
        <div>
            <!-- Durum A: Form -->
            <div x-show="!sent" x-cloak class="space-y-6">
                <?php if (!empty($errors)): ?>
                    <ul class="p-4 bg-red-50 border border-red-200 rounded-md text-sm text-red-700 list-none space-y-1">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars(is_string($err) ? $err : implode(' ', (array)$err)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/iletisim" class="space-y-6">
                    <div>
                        <input type="text" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required placeholder="Ad Soyad" class="w-full border-b border-gray-300 py-3 text-sm focus:border-black focus:outline-none transition bg-transparent placeholder-gray-400">
                    </div>
                    <div>
                        <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required placeholder="E-posta" class="w-full border-b border-gray-300 py-3 text-sm focus:border-black focus:outline-none transition bg-transparent placeholder-gray-400">
                    </div>
                    <div>
                        <select name="subject" class="w-full border-b border-gray-300 py-3 text-sm focus:border-black focus:outline-none transition bg-transparent text-gray-700 appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%239ca3af%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22m19.5 8.25-7.5 7.5-7.5-7.5%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 0 center; background-size: 1.25rem;">
                            <option value="">Konu seçin</option>
                            <option value="Sipariş Durumu" <?= ($old['subject'] ?? '') === 'Sipariş Durumu' ? 'selected' : '' ?>>Sipariş Durumu</option>
                            <option value="İade/Değişim" <?= ($old['subject'] ?? '') === 'İade/Değişim' ? 'selected' : '' ?>>İade/Değişim</option>
                            <option value="İş Birliği" <?= ($old['subject'] ?? '') === 'İş Birliği' ? 'selected' : '' ?>>İş Birliği</option>
                            <option value="Diğer" <?= ($old['subject'] ?? '') === 'Diğer' ? 'selected' : '' ?>>Diğer</option>
                        </select>
                    </div>
                    <div>
                        <textarea name="message" rows="5" required placeholder="Mesajınız" class="w-full border-b border-gray-300 py-3 text-sm focus:border-black focus:outline-none transition bg-transparent placeholder-gray-400 resize-none"><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <button type="submit" class="bg-black text-white px-8 py-4 text-xs font-bold tracking-widest hover:bg-gray-800 transition w-full md:w-auto uppercase">
                            GÖNDER
                        </button>
                    </div>
                </form>
            </div>

            <!-- Durum B: Başarılı Mesajı -->
            <div x-show="sent" x-cloak class="h-full flex flex-col justify-center items-center text-center bg-gray-50 p-8 rounded-sm">
                <svg class="w-16 h-16 text-green-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.25" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="font-display text-xl tracking-widest uppercase text-primary mb-2">MESAJINIZ ALINDI</h2>
                <p class="text-sm text-gray-600 max-w-sm">Bizimle iletişime geçtiğiniz için teşekkürler. Ekibimiz en geç 24 saat içinde size dönüş yapacaktır.</p>
                <button type="button" @click="sent = false" class="text-xs underline mt-6 cursor-pointer text-primary hover:no-underline transition">Yeni Mesaj Gönder</button>
            </div>
        </div>
    </div>

    <!-- SSS Yönlendirmesi (Footer Üstü) -->
    <div class="mt-20 pt-12 border-t border-gray-100 text-center">
        <span class="text-sm text-gray-600">Sıkça sorulan sorulara göz atmak ister misiniz?</span>
        <a href="<?= htmlspecialchars($baseUrl) ?>/sss" class="font-bold text-sm ml-2 hover:underline transition text-primary inline-block mt-2 sm:mt-0 sm:inline">Yardım Merkezi'ne Git →</a>
    </div>
</div>
