<?php $baseUrl = $baseUrl ?? ''; ?>
<div x-data="{ show: !localStorage.getItem('cookieConsent') }"
     x-show="show"
     x-transition:enter="transition ease-out duration-400"
     x-transition:enter-start="translate-y-full opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="translate-y-full opacity-0"
     class="fixed bottom-0 left-0 right-0 z-[90] p-4 sm:p-6 flex justify-center sm:justify-end sm:items-end pointer-events-none"
     role="dialog"
     aria-label="Çerez tercihleri">
    <div class="pointer-events-auto w-full max-w-[560px] sm:max-w-[420px] sm:mr-0 bg-white border border-subtle rounded-sm shadow-[0_8px_32px_rgba(0,0,0,0.12)] overflow-hidden">
        <div class="p-6 sm:p-8">
            <h2 class="font-display text-sm font-medium tracking-[0.2em] uppercase text-primary mb-3">
                Çerez tercihleri
            </h2>
            <p class="text-sm text-secondary leading-relaxed mb-4">
                Sitemizde deneyiminizi iyileştirmek, alışverişinizi kolaylaştırmak ve site güvenliğini sağlamak için çerezler kullanıyoruz. Aşağıda kullanım amaçlarını özetledik; detaylar için Çerez Politikamızı inceleyebilirsiniz.
            </p>
            <ul class="text-xs text-secondary space-y-2 mb-6">
                <li class="flex gap-2">
                    <span class="text-primary font-medium min-w-[5rem]">Gerekli:</span>
                    <span>Oturum, sepet ve güvenlik için zorunlu çerezler.</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-primary font-medium min-w-[5rem]">Tercihler:</span>
                    <span>Dil ve görünüm gibi seçimlerinizi hatırlamak için.</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-primary font-medium min-w-[5rem]">İstatistik:</span>
                    <span>Anonim kullanım istatistikleri (isteğe bağlı).</span>
                </li>
            </ul>
            <div class="flex flex-wrap items-center gap-3">
                <a href="<?= $baseUrl ?>/sayfa/gizlilik" class="text-xs text-secondary underline hover:text-primary transition-colors">Gizlilik Politikası</a>
                <span class="text-subtle">·</span>
                <a href="<?= $baseUrl ?>/sayfa/cerez-politikasi" class="text-xs text-secondary underline hover:text-primary transition-colors">Çerez Politikası</a>
            </div>
        </div>
        <div class="px-6 sm:px-8 py-4 bg-subtle/30 border-t border-subtle flex flex-wrap items-center justify-end gap-3">
            <button type="button"
                    @click="localStorage.setItem('cookieConsent', 'necessary'); show = false"
                    class="text-xs font-medium tracking-wide text-primary border border-primary bg-transparent px-4 py-2.5 rounded-sm hover:bg-primary hover:text-white transition-colors">
                Sadece gerekli
            </button>
            <button type="button"
                    @click="localStorage.setItem('cookieConsent', 'true'); show = false"
                    class="text-xs font-medium tracking-wide text-white bg-primary px-4 py-2.5 rounded-sm hover:opacity-90 transition-opacity">
                Tümünü kabul et
            </button>
        </div>
    </div>
</div>
