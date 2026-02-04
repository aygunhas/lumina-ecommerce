<?php
if (!function_exists('getLuminaImage')) {
    require_once __DIR__ . '/functions.php';
}
$baseUrl = $baseUrl ?? '';
// Demo ürünler (20 adet); ilk 10 görünür, scroll ile kalan 10 Alpine ile açılır
$featuredItems = [
    ['name' => 'İpek Karışımlı Midi Elbise', 'price' => '2.450,00', 'colors' => ['#0a0a0a', '#c4a77d', '#8b7355']],
    ['name' => 'Oversize Yün Palto', 'price' => '3.890,00', 'colors' => ['#1a1a1a', '#4a4a4a', '#e8e0d8']],
    ['name' => 'Yüksek Bel Pantolon', 'price' => '1.650,00', 'colors' => ['#2c2c2c', '#5c4033', '#ffffff']],
    ['name' => 'Kaşmir Karışımlı Kazak', 'price' => '1.290,00', 'colors' => ['#3d3d3d', '#8b4513', '#d2b48c']],
    ['name' => 'Saten Bluz', 'price' => '890,00', 'colors' => ['#ffffff', '#1a1a1a', '#c4a77d']],
    ['name' => 'Deri Çanta', 'price' => '2.190,00', 'colors' => ['#0a0a0a', '#5c4033', '#8b7355']],
    ['name' => 'Yün Blend Triko', 'price' => '1.450,00', 'colors' => ['#4a4a4a', '#c4a77d', '#1a1a1a']],
    ['name' => 'Kadife Midi Etek', 'price' => '1.190,00', 'colors' => ['#2c2c2c', '#5c4033', '#8b7355']],
    ['name' => 'Oversize Gömlek', 'price' => '990,00', 'colors' => ['#ffffff', '#e8e0d8', '#1a1a1a']],
    ['name' => 'Deri Ceket', 'price' => '4.290,00', 'colors' => ['#0a0a0a', '#5c4033', '#3d3d3d']],
    ['name' => 'İpek Fular', 'price' => '490,00', 'colors' => ['#c4a77d', '#8b7355', '#1a1a1a']],
    ['name' => 'Pamuklu Basic Tişört', 'price' => '450,00', 'colors' => ['#ffffff', '#0a0a0a', '#4a4a4a']],
    ['name' => 'Dantel Detaylı Bluz', 'price' => '1.350,00', 'colors' => ['#ffffff', '#e8e0d8', '#1a1a1a']],
    ['name' => 'Yüksek Bel Midi Pantolon', 'price' => '1.790,00', 'colors' => ['#0a0a0a', '#2c2c2c', '#5c4033']],
    ['name' => 'Örme Hırka', 'price' => '1.590,00', 'colors' => ['#8b7355', '#c4a77d', '#3d3d3d']],
    ['name' => 'Saten Midi Etek', 'price' => '1.090,00', 'colors' => ['#1a1a1a', '#4a4a4a', '#c4a77d']],
    ['name' => 'Klasik Trench', 'price' => '3.490,00', 'colors' => ['#5c4033', '#0a0a0a', '#8b7355']],
    ['name' => 'Minimalist Kolye', 'price' => '690,00', 'colors' => ['#8b7355', '#1a1a1a', '#c4a77d']],
    ['name' => 'Yün Şal', 'price' => '790,00', 'colors' => ['#c4a77d', '#5c4033', '#2c2c2c']],
    ['name' => 'Deri Kemer', 'price' => '590,00', 'colors' => ['#0a0a0a', '#5c4033', '#8b7355']],
];
?>
<section class="py-20" x-data="{ limit: 10, total: 20 }">
    <h2 class="font-display text-3xl tracking-tighter text-center text-primary mb-12">SEZONUN FAVORİLERİ</h2>
    <div class="max-w-[1400px] mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-10 md:gap-x-8 md:gap-y-16">
            <?php foreach ($featuredItems as $i => $item): ?>
                <?php
                $imgBlack = getLuminaImage('product', $i % 6);
                $imgBeige = getLuminaImage('hero', ($i + 1) % 4);
                $imgWhite = getLuminaImage('product', ($i + 2) % 6);
                $img2 = getLuminaImage('hero', $i % 4);
                $cardData = [
                    'currentImg' => $imgBlack,
                    'selectedColor' => 'black',
                    'imgBlack' => $imgBlack,
                    'imgBeige' => $imgBeige,
                    'imgWhite' => $imgWhite,
                ];
                $c0 = $item['colors'][0] ?? '#0a0a0a';
                $c1 = $item['colors'][1] ?? '#c4a77d';
                $c2 = $item['colors'][2] ?? '#ffffff';
                ?>
                <div
                    x-show="<?= (int) $i ?> < limit"
                    x-transition:enter="transition ease-out duration-1000"
                    x-transition:enter-start="opacity-0 translate-y-12"
                    x-transition:enter-end="opacity-100 translate-y-0"
                >
                    <a href="<?= htmlspecialchars($baseUrl) ?>/" class="cursor-pointer block" aria-label="<?= htmlspecialchars($item['name']) ?>"
                        x-data="<?= htmlspecialchars(json_encode($cardData), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <div class="group relative overflow-hidden aspect-[3/4] bg-gray-100 mb-4">
                            <img :src="currentImg" alt="" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 group-hover:opacity-0" />
                            <img src="<?= htmlspecialchars($img2) ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100" />
                            <span class="absolute top-2 left-2 text-[10px] bg-white px-2 py-1 tracking-widest uppercase text-primary">NEW</span>
                        </div>
                        <p class="text-sm font-medium text-primary mt-3"><?= htmlspecialchars($item['name']) ?></p>
                        <p class="text-xs text-secondary mt-1">₺<?= htmlspecialchars($item['price']) ?></p>
                        <div class="flex gap-1 mt-2" @click.prevent.stop>
                            <button type="button" aria-label="Siyah renk"
                                class="w-3.5 h-3.5 rounded-full flex-shrink-0 border border-black/10 cursor-pointer focus:outline-none focus:ring-1 focus:ring-black"
                                :class="{ 'ring-1 ring-black': selectedColor === 'black' }"
                                style="background-color: <?= htmlspecialchars($c0) ?>"
                                @click.prevent="currentImg = imgBlack; selectedColor = 'black'"
                            ></button>
                            <button type="button" aria-label="Bej renk"
                                class="w-3.5 h-3.5 rounded-full flex-shrink-0 border border-black/10 cursor-pointer focus:outline-none focus:ring-1 focus:ring-black"
                                :class="{ 'ring-1 ring-black': selectedColor === 'beige' }"
                                style="background-color: <?= htmlspecialchars($c1) ?>"
                                @click.prevent="currentImg = imgBeige; selectedColor = 'beige'"
                            ></button>
                            <button type="button" aria-label="Beyaz renk"
                                class="w-3.5 h-3.5 rounded-full flex-shrink-0 border border-black/10 cursor-pointer focus:outline-none focus:ring-1 focus:ring-black"
                                :class="{ 'ring-1 ring-black': selectedColor === 'white' }"
                                style="background-color: <?= htmlspecialchars($c2) ?>"
                                @click.prevent="currentImg = imgWhite; selectedColor = 'white'"
                            ></button>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Scroll tetikleyici: bu alan görünür olunca limit 20 yapılır, kalan 10 ürün animasyonla açılır -->
        <div x-intersect.full="limit = 20" class="h-10 w-full" aria-hidden="true"></div>
    </div>
</section>
