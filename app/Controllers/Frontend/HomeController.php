<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use PDO;

/**
 * Mağaza anasayfa – kategoriler ve öne çıkan ürünler
 */
class HomeController extends FrontendBaseController
{
    public function index(): void
    {
        $categories = Category::getMainCategories(5);
        
        // Sadece öne çıkan ürünleri göster (8'den az olsa bile)
        $featuredProducts = Product::getFeatured(8);
        
        $productImages = [];
        $productHasVariants = [];
        $productColorImages = [];
        $productColorVariants = [];
        if (!empty($featuredProducts)) {
            $ids = array_column($featuredProducts, 'id');
            $productImages = Product::getMainImagesForProducts($ids);
            $productHasVariants = Product::hasVariantsForProducts($ids);
            $productColorImages = Product::getColorImagesForProducts($ids);
            
            // Her ürün için renk varyantlarını çek
            $pdo = Database::getConnection();
            foreach ($ids as $productId) {
                // Renk attribute'unu bul
                $colorAttrStmt = $pdo->prepare('SELECT id FROM attributes WHERE type = ? LIMIT 1');
                $colorAttrStmt->execute(['color']);
                $colorAttrId = $colorAttrStmt->fetchColumn();
                
                if ($colorAttrId) {
                    // Bu ürünün varyantlarındaki renkleri çek
                    $variantStmt = $pdo->prepare('
                        SELECT DISTINCT av.id, av.value, av.color_hex, av.sort_order
                        FROM product_variants pv
                        INNER JOIN product_variant_attribute_values pvav ON pv.id = pvav.variant_id
                        INNER JOIN attribute_values av ON pvav.attribute_value_id = av.id
                        WHERE pv.product_id = ? AND av.attribute_id = ?
                        ORDER BY av.sort_order ASC
                    ');
                    $variantStmt->execute([$productId, $colorAttrId]);
                    $productColorVariants[$productId] = $variantStmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $productColorVariants[$productId] = [];
                }
            }
        }

        $sliders = Slider::getActive();

        $title = env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $this->render('frontend/home', compact('title', 'baseUrl', 'categories', 'featuredProducts', 'productImages', 'productHasVariants', 'productColorImages', 'productColorVariants', 'sliders'));
    }
}
