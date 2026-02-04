<?php

/**
 * Demo görselleri yönetir. Hero (yatay) veya product (dikey) tipinde,
 * rastgele veya indeks ile Unsplash URL döndürür.
 *
 * @param string $type  'hero' = yatay moda çekimleri, 'product' = dikey ürün çekimleri
 * @param string|int   $index 'random' = rastgele, 0-based sayı = o sıradaki resim (mod alınır)
 * @return string       Unsplash görsel URL
 */
function getLuminaImage($type = 'product', $index = 'random')
{
    $base = 'https://images.unsplash.com/photo-';

    // Yatay (Landscape), sinematik, stüdyo ışığı, minimalist moda çekimleri
    $hero_images = [
        $base . '1496747611176-843222e1e57c?w=1600&q=80',
        $base . '1483985988355-763728e1935b?w=1600&q=80',
        $base . '1515886657613-9f3515b0c78f?w=1600&q=80', // stüdyo / moda (çalışan ID)
        $base . '1469334031218-e382a71b716b?w=1600&q=80',
    ];

    // Dikey (Portrait), e-ticaret formatı, nötr renkler (bej, gri, siyah), manken/kiyafet
    $product_images = [
        $base . '1515886657613-9f3515b0c78f?w=800&q=80',
        $base . '1532453288672-3a27e9be9efd?w=800&q=80',
        $base . '1550614000-4b9519e02d48?w=800&q=80',
        $base . '1507680434567-5739c80be1ac?w=800&q=80',
        $base . '1496747611176-843222e1e57c?w=800&q=80',
        $base . '1483985988355-763728e1935b?w=800&q=80',
    ];

    // SALE / İndirim: Unsplash CDN bu slug ile URL vermiyor; çalışan hero görseli kullanılıyor.
    // Özel görsel için: https://unsplash.com/photos/6NzU3Av0Ew8 adresinden indirip
    // public/assets/images/sale.jpg olarak kaydedin; home-categories.php'de $imgSale yerel dosyaya yönlendirilebilir.
    $sale_images = [
        $base . '1515886657613-9f3515b0c78f?w=800&q=80', // geçici: çalışan moda görseli
    ];

    $list = ($type === 'hero') ? $hero_images : (($type === 'sale') ? $sale_images : $product_images);
    $count = count($list);

    if ($index === 'random' || !is_numeric($index)) {
        return $list[array_rand($list)];
    }

    $i = (int) $index % $count;
    if ($i < 0) {
        $i += $count;
    }
    return $list[$i];
}
