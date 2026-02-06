<?php
$baseUrl = $baseUrl ?? '';
$featuredProducts = $featuredProducts ?? [];
$productImages = $productImages ?? [];
?>
<section class="min-h-[60vh] flex flex-col items-center justify-center text-center px-6">
    <h1 class="text-[10rem] font-display leading-none text-gray-100 select-none">404</h1>
    <p class="-mt-12 text-xl font-medium tracking-widest relative z-10 text-primary">ARADIĞINIZ SAYFA BULUNAMADI</p>
    <p class="mt-4 text-secondary max-w-md">Üzgünüz, ulaşmaya çalıştığınız sayfa taşınmış veya silinmiş olabilir.</p>
    <a href="<?= $baseUrl ?>/" class="inline-block bg-black text-white px-8 py-4 mt-8 text-xs font-bold tracking-widest hover:bg-primary/90 transition-colors">
        ANASAYFAYA DÖN
    </a>
</section>

<section class="py-16 border-t border-subtle">
    <h2 class="font-display text-2xl tracking-tighter text-center text-primary mb-10">BUNLARI BEĞENEBİLİRSİNİZ</h2>
    <div class="max-w-[1400px] mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-8 md:gap-x-8 md:gap-y-12">
            <?php
            $items = $featuredProducts;
            $total = count($items);
            if ($total === 0) {
                $items = [
                    ['name' => 'İpek Karışımlı Midi Elbise', 'price' => 2450, 'sale_price' => null, 'slug' => '', 'id' => 0],
                    ['name' => 'Oversize Yün Palto', 'price' => 3890, 'sale_price' => null, 'slug' => '', 'id' => 1],
                    ['name' => 'Yüksek Bel Pantolon', 'price' => 1650, 'sale_price' => null, 'slug' => '', 'id' => 2],
                    ['name' => 'Kaşmir Karışımlı Kazak', 'price' => 1290, 'sale_price' => null, 'slug' => '', 'id' => 3],
                ];
                $productImages = [];
            }
            foreach (array_slice($items, 0, 4) as $i => $item):
                $productUrl = !empty($item['slug']) ? $baseUrl . '/urun/' . htmlspecialchars($item['slug']) : $baseUrl . '/';
                $itemName = $item['name'] ?? 'Ürün';
                $priceVal = isset($item['sale_price']) && $item['sale_price'] !== null && (float)$item['sale_price'] > 0 ? (float)$item['sale_price'] : (float)($item['price'] ?? 0);
                $itemPrice = number_format($priceVal, 2, ',', '.');
                $imgPath = isset($item['id'], $productImages[$item['id']]) ? $baseUrl . '/' . $productImages[$item['id']] : getLuminaImage('product', $i % 6);
            ?>
                <a href="<?= $productUrl ?>" class="block group" aria-label="<?= htmlspecialchars($itemName) ?>">
                    <div class="relative overflow-hidden aspect-[3/4] bg-gray-100 mb-3">
                        <img src="<?= htmlspecialchars($imgPath) ?>" alt="" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-90" />
                    </div>
                    <p class="text-sm font-medium text-primary"><?= htmlspecialchars($itemName) ?></p>
                    <p class="text-xs text-secondary mt-1">₺<?= htmlspecialchars($itemPrice) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
