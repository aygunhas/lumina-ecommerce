<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use App\Models\Product;
use PDO;

/**
 * Mağaza: Ürün detay sayfası
 */
class ProductController extends FrontendBaseController
{
    public function show(): void
    {
        $slug = $_GET['_slug'] ?? '';
        if ($slug === '') {
            $this->redirect('/');
        }
        
        $product = Product::findActiveBySlug($slug);
        if (!$product) {
            require BASE_PATH . '/includes/render-404.php';
            return;
        }
        
        Product::incrementViewCount($product['id']);
        
        $pdo = Database::getConnection();
        
        // Ana ürün görselleri (attribute_value_id NULL olanlar)
        $productImagePaths = Product::getImages($product['id']);
        
        // Renk bazlı görselleri çek (attribute_value_id ile bağlı)
        $colorImages = [];
        $colorImagesStmt = $pdo->prepare('
            SELECT attribute_value_id, path, sort_order 
            FROM product_images 
            WHERE product_id = ? AND attribute_value_id IS NOT NULL 
            ORDER BY attribute_value_id ASC, sort_order ASC, id ASC
        ');
        $colorImagesStmt->execute([$product['id']]);
        $colorImagesRaw = $colorImagesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Renk ID'sine göre grupla ve baseUrl ekle
        foreach ($colorImagesRaw as $img) {
            $colorId = (int)$img['attribute_value_id'];
            if (!isset($colorImages[$colorId])) {
                $colorImages[$colorId] = [];
            }
            $colorImages[$colorId][] = $img['path'];
        }

        // Benzer ürünler (A17): aynı kategoriden, mevcut ürün hariç, en fazla 4
        $categoryId = $product['category_id'] ?? null;
        $relatedProducts = Product::getRelated($product['id'], $categoryId ? (int) $categoryId : null, 4);
        
        $relatedProductImages = [];
        $relatedProductHasVariants = [];
        $relatedProductColorImages = [];
        $relatedProductColorVariants = [];
        if (!empty($relatedProducts)) {
            $ids = array_column($relatedProducts, 'id');
            $relatedProductImages = Product::getMainImagesForProducts($ids);
            $relatedProductHasVariants = Product::hasVariantsForProducts($ids);
            $relatedProductColorImages = Product::getColorImagesForProducts($ids);
            
            // Her ürün için renk varyantlarını çek
            foreach ($ids as $productId) {
                // Renk attribute'unu bul
                $colorAttrStmt = $pdo->prepare('SELECT id FROM attributes WHERE type = ? LIMIT 1');
                $colorAttrStmt->execute(['color']);
                $colorAttrId = $colorAttrStmt->fetchColumn();
                
                if ($colorAttrId) {
                    // Bu ürünün varyantlarında kullanılan renkleri bul
                    $colorIdsStmt = $pdo->prepare('
                        SELECT DISTINCT av.id, av.value, av.color_hex, av.sort_order
                        FROM product_variants pv
                        INNER JOIN product_variant_attribute_values pvav ON pv.id = pvav.variant_id
                        INNER JOIN attribute_values av ON pvav.attribute_value_id = av.id
                        WHERE pv.product_id = ? AND av.attribute_id = ?
                        ORDER BY av.sort_order ASC, av.value ASC
                    ');
                    $colorIdsStmt->execute([$productId, $colorAttrId]);
                    $relatedProductColorVariants[$productId] = $colorIdsStmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        }

        $isInWishlist = false;
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $pdo = Database::getConnection();
        if ($userId > 0) {
            $stmt = $pdo->prepare('SELECT 1 FROM wishlists WHERE user_id = ? AND product_id = ? LIMIT 1');
            $stmt->execute([$userId, $product['id']]);
            $isInWishlist = (bool) $stmt->fetch();
        }

        // Varyantlar: beden/renk seçimi için
        $productVariants = [];
        $attributesForVariant = [];
        $attributeValuesByAttr = [];
        $stmt = $pdo->prepare('
            SELECT pv.id, pv.sku, pv.stock, pv.price, pv.sale_price
            FROM product_variants pv
            WHERE pv.product_id = ?
            ORDER BY pv.sort_order ASC, pv.id ASC
        ');
        $stmt->execute([$product['id']]);
        $productVariants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($productVariants as &$v) {
            $st = $pdo->prepare('
                SELECT a.id AS attribute_id, a.name AS attribute_name, av.id AS attribute_value_id, av.value
                FROM product_variant_attribute_values pvav
                INNER JOIN attribute_values av ON pvav.attribute_value_id = av.id
                INNER JOIN attributes a ON av.attribute_id = a.id
                WHERE pvav.variant_id = ?
                ORDER BY a.sort_order, av.sort_order
            ');
            $st->execute([$v['id']]);
            $v['attrs'] = $st->fetchAll(PDO::FETCH_ASSOC);
            $v['attribute_value_ids'] = array_column($v['attrs'], 'attribute_value_id');
            sort($v['attribute_value_ids']);
            $v['attributes_summary'] = implode(', ', array_column($v['attrs'], 'value'));
        }
        unset($v);
        
        // Varyantlarda kullanılan renkleri ve bedenleri topla
        $usedColorIds = [];
        $sizeAttributeId = null;
        $availableSizes = []; // Beden değerleri (sadece ID ve value, stok durumu renk-beden kombinasyonuna göre)
        $sizeStockByColor = []; // Renk-beden kombinasyonlarına göre stok durumları [colorId][sizeValue] => inStock
        
        // Önce tüm bedenleri ve ID'lerini topla
        foreach ($productVariants as $v) {
            $variantColorId = null;
            $variantSizeValue = null;
            $variantSizeId = null;
            
            foreach ($v['attrs'] as $attr) {
                // Attribute type'ını bul
                $attrStmt = $pdo->prepare('SELECT type FROM attributes WHERE id = ? LIMIT 1');
                $attrStmt->execute([$attr['attribute_id']]);
                $attrType = $attrStmt->fetchColumn();
                
                if ($attrType === 'color') {
                    $usedColorIds[$attr['attribute_value_id']] = true;
                    $variantColorId = (int)$attr['attribute_value_id'];
                } elseif ($attrType === 'size' || strtolower($attr['attribute_name']) === 'beden') {
                    if ($sizeAttributeId === null) {
                        $sizeAttributeId = (int)$attr['attribute_id'];
                    }
                    $variantSizeValue = $attr['value'];
                    $variantSizeId = (int)$attr['attribute_value_id'];
                    
                    // Beden bilgisini kaydet
                    if (!isset($availableSizes[$variantSizeValue])) {
                        $availableSizes[$variantSizeValue] = [
                            'id' => $variantSizeId,
                            'value' => $variantSizeValue
                        ];
                    }
                }
            }
            
            // Renk ve beden bilgisi varsa, bu kombinasyon için stok durumunu kaydet
            if ($variantColorId !== null && $variantSizeValue !== null) {
                if (!isset($sizeStockByColor[$variantColorId])) {
                    $sizeStockByColor[$variantColorId] = [];
                }
                // Bu renk-beden kombinasyonu için stok durumunu kaydet
                // Eğer daha önce kaydedilmediyse veya stok varsa true yap
                $variantStock = (int)$v['stock'];
                if (!isset($sizeStockByColor[$variantColorId][$variantSizeValue])) {
                    $sizeStockByColor[$variantColorId][$variantSizeValue] = $variantStock > 0;
                } else {
                    // Eğer zaten kayıtlıysa, stok varsa true yap (en az bir varyantta stok varsa aktif)
                    if ($variantStock > 0) {
                        $sizeStockByColor[$variantColorId][$variantSizeValue] = true;
                    }
                }
            }
        }
        
        if (!empty($productVariants)) {
            $stmt = $pdo->query('SELECT id, name, type FROM attributes ORDER BY sort_order ASC, name ASC');
            $attributesForVariant = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($attributesForVariant as $a) {
                // Renk attribute'u için sadece varyantlarda kullanılan renkleri getir
                if ($a['type'] === 'color') {
                    if (!empty($usedColorIds)) {
                        $placeholders = implode(',', array_fill(0, count($usedColorIds), '?'));
                        $stmt = $pdo->prepare("SELECT id, value, color_hex FROM attribute_values WHERE attribute_id = ? AND id IN ($placeholders) ORDER BY sort_order ASC, value ASC");
                        $stmt->execute(array_merge([$a['id']], array_keys($usedColorIds)));
                    } else {
                        $stmt = $pdo->prepare('SELECT id, value, color_hex FROM attribute_values WHERE attribute_id = ? ORDER BY sort_order ASC, value ASC');
                        $stmt->execute([$a['id']]);
                    }
                } else {
                    $stmt = $pdo->prepare('SELECT id, value, color_hex FROM attribute_values WHERE attribute_id = ? ORDER BY sort_order ASC, value ASC');
                    $stmt->execute([$a['id']]);
                }
                $attributeValuesByAttr[$a['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        // Varyantlı ürünlerde en düşük fiyatı hesapla
        $minPrice = (float)($product['price'] ?? 0);
        $minSalePrice = null;
        if (!empty($productVariants)) {
            $variantPrices = array_column($productVariants, 'price');
            $variantSalePrices = array_filter(array_column($productVariants, 'sale_price'), function($sp) {
                return $sp !== null && $sp !== '' && (float)$sp > 0;
            });
            if (!empty($variantPrices)) {
                $minPrice = min(array_map('floatval', $variantPrices));
            }
            if (!empty($variantSalePrices)) {
                $minSalePrice = min(array_map('floatval', $variantSalePrices));
                // Eğer indirimli fiyat normal fiyattan küçük değilse null yap
                if ($minSalePrice >= $minPrice) {
                    $minSalePrice = null;
                }
            }
        } else {
            $minSalePrice = $product['sale_price'] !== null && $product['sale_price'] !== '' ? (float)$product['sale_price'] : null;
            if ($minSalePrice !== null && $minSalePrice >= $minPrice) {
                $minSalePrice = null;
            }
        }
        
        // Ürün fiyatlarını güncelle
        $product['price'] = $minPrice;
        $product['sale_price'] = $minSalePrice;

        $title = $product['name'] . ' - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $this->render('frontend/product/show', compact('title', 'baseUrl', 'product', 'productImagePaths', 'colorImages', 'relatedProducts', 'relatedProductImages', 'relatedProductHasVariants', 'relatedProductColorImages', 'relatedProductColorVariants', 'isInWishlist', 'userId', 'productVariants', 'attributesForVariant', 'attributeValuesByAttr', 'sizeAttributeId', 'availableSizes', 'sizeStockByColor'));
    }
}
