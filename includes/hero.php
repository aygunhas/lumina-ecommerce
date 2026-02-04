<?php
if (!function_exists('getLuminaImage')) {
    require_once __DIR__ . '/functions.php';
}
$baseUrl = $baseUrl ?? '';
$heroImgs = [
    getLuminaImage('hero', 0),
    getLuminaImage('hero', 1),
    getLuminaImage('hero', 2),
    getLuminaImage('hero', 3),
];
$heroSlides = [
    ['title' => 'ZARAFETİN YENİ TANIMI', 'subtitle' => 'Sonbahar / Kış Koleksiyonu', 'btn' => 'KEŞFET'],
    ['title' => 'MİNİMALİST DOKUNUŞLAR', 'subtitle' => 'Zamansız Parçalar', 'btn' => 'ALIŞVERİŞE BAŞLA'],
    ['title' => 'STÜDYO IŞIĞINDA', 'subtitle' => 'Özenle Seçilmiş Parçalar', 'btn' => 'KEŞFET'],
    ['title' => 'LUMINA BOUTIQUE', 'subtitle' => 'Kadın Giyim & Aksesuar', 'btn' => 'ALIŞVERİŞE BAŞLA'],
];
?>
<section class="relative h-[65vh] min-h-[420px] max-h-[720px] w-full overflow-hidden" x-data="{
    activeSlide: 0,
    slides: [
        <?php for ($i = 0; $i < 4; $i++): ?>
        { img: '<?= htmlspecialchars($heroImgs[$i], ENT_QUOTES, 'UTF-8') ?>', title: '<?= htmlspecialchars($heroSlides[$i]['title'], ENT_QUOTES, 'UTF-8') ?>', subtitle: '<?= htmlspecialchars($heroSlides[$i]['subtitle'], ENT_QUOTES, 'UTF-8') ?>', btn: '<?= htmlspecialchars($heroSlides[$i]['btn'], ENT_QUOTES, 'UTF-8') ?>', link: '<?= $baseUrl ?>/' }<?= $i < 3 ? ',' : '' ?>
        <?php endfor; ?>
    ],
    autoplay() {
        setInterval(() => { this.activeSlide = (this.activeSlide + 1) % this.slides.length; }, 5000);
    }
}" x-init="autoplay()">
    <!-- Slider katmanları: resimler absolute inset-0 ile üst üste -->
    <template x-for="(slide, index) in slides" :key="index">
        <div
            x-show="activeSlide === index"
            x-transition:enter="transition ease-out duration-[2000ms]"
            x-transition:enter-start="opacity-0 scale-110"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-700"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute inset-0 w-full h-full"
        >
            <img :src="slide.img" :alt="slide.title" class="absolute inset-0 w-full h-full object-cover" />
            <!-- Karartma overlay -->
            <div class="absolute inset-0 bg-black/20"></div>
            <!-- İçerik katmanı: ortada, metinler sırayla aşağıdan yukarı -->
            <div class="absolute inset-0 flex items-center justify-center flex-col gap-4 px-6 text-center text-white">
                <h2
                    x-show="activeSlide === index"
                    x-transition:enter="transition ease-out duration-700 delay-300"
                    x-transition:enter-start="opacity-0 translate-y-8"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="font-display text-4xl md:text-5xl tracking-tighter"
                    x-text="slide.title"
                ></h2>
                <p
                    x-show="activeSlide === index"
                    x-transition:enter="transition ease-out duration-700 delay-500"
                    x-transition:enter-start="opacity-0 translate-y-6"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="text-[11px] font-medium tracking-[0.3em] uppercase"
                    x-text="slide.subtitle"
                ></p>
                <a
                    :href="slide.link"
                    x-show="activeSlide === index"
                    x-transition:enter="transition ease-out duration-700 delay-700"
                    x-transition:enter-start="opacity-0 translate-y-6"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="mt-2 inline-block border border-white bg-transparent px-8 py-3 text-[11px] font-medium tracking-luxury uppercase text-white hover:bg-white hover:text-primary transition"
                    x-text="slide.btn"
                ></a>
            </div>
        </div>
    </template>

    <!-- Prev / Next butonları: glassmorphism & minimalist (resimlerin üzerinde) -->
    <button
        type="button"
        @click="activeSlide = activeSlide === 0 ? slides.length - 1 : activeSlide - 1"
        class="absolute left-8 top-1/2 -translate-y-1/2 z-10 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white hover:bg-white hover:text-black hover:scale-110 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white/50"
        aria-label="Önceki slide"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
    </button>
    <button
        type="button"
        @click="activeSlide = activeSlide === slides.length - 1 ? 0 : activeSlide + 1"
        class="absolute right-8 top-1/2 -translate-y-1/2 z-10 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white hover:bg-white hover:text-black hover:scale-110 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white/50"
        aria-label="Sonraki slide"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
    </button>

    <!-- Alt navigasyon: minimalist çizgiler (her zaman görünür) -->
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center justify-center gap-2 z-10">
        <template x-for="(slide, index) in slides" :key="index">
            <button
                type="button"
                @click="activeSlide = index"
                class="h-1 w-10 md:w-12 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-white/50"
                :class="activeSlide === index ? 'bg-white' : 'bg-white/40 hover:bg-white/60'"
                :aria-label="'Slide ' + (index + 1)"
            ></button>
        </template>
    </div>
</section>
