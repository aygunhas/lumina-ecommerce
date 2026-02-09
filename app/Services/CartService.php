<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use PDO;

/**
 * Sepet yönetimi için Service sınıfı
 * 
 * Bu sınıf sepet işlemlerini merkezi olarak yönetir:
 * - Sepet key parse etme
 * - Sepet key oluşturma
 * - Sepet öğelerini getirme
 * - Sepete ürün ekleme/çıkarma/güncelleme
 * - Sepet toplamını hesaplama
 */
class CartService
{
    /**
     * Sepet key'ini parse eder
     * 
     * Sepet key formatları:
     * - p123: Ürün ID 123 (varyant yok)
     * - p123_v456: Ürün ID 123, Varyant ID 456
     * - p123_s_M: Ürün ID 123, Beden M
     * 
     * @param string $key Sepet key'i
     * @return array ['product_id' => int, 'variant_id' => int|null, 'size' => string|null]
     */
    public static function parseCartKey(string $key): array
    {
        // Varyant ID ile: p123_v456
        if (strpos($key, '_v') !== false) {
            $parts = explode('_v', $key, 2);
            if (count($parts) === 2 && $parts[0] !== '' && preg_match('/^p\d+$/', $parts[0]) && is_numeric($parts[1])) {
                return [
                    'product_id' => (int) ltrim($parts[0], 'p'),
                    'variant_id' => (int) $parts[1],
                    'size' => null
                ];
            }
        }
        
        // Beden ile: p123_s_M
        if (preg_match('/^p(\d+)_s_(.+)$/', $key, $m)) {
            return [
                'product_id' => (int) $m[1],
                'variant_id' => null,
                'size' => $m[2]
            ];
        }
        
        // Sadece ürün ID: p123 veya 123
        if (preg_match('/^p?\d+$/', $key)) {
            $id = (int) ltrim($key, 'p');
            return [
                'product_id' => $id,
                'variant_id' => null,
                'size' => null
            ];
        }
        
        // Geçersiz key
        return [
            'product_id' => 0,
            'variant_id' => null,
            'size' => null
        ];
    }

    /**
     * Sepet key'i oluşturur
     * 
     * @param int $productId Ürün ID'si
     * @param int|null $variantId Varyant ID'si (varsa)
     * @param string|null $size Beden (varsa)
     * @return string Sepet key'i
     */
    public static function cartKey(int $productId, ?int $variantId = null, ?string $size = null): string
    {
        if ($variantId !== null && $variantId > 0) {
            return 'p' . $productId . '_v' . $variantId;
        }
        if ($size !== null && $size !== '') {
            return 'p' . $productId . '_s_' . $size;
        }
        return 'p' . $productId;
    }

    /**
     * Sepet öğelerini getirir (ürün bilgileriyle birlikte)
     * 
     * @return array ['items' => array, 'subtotal' => float, 'count' => int]
     */
    public static function getItems(): array
    {
        $cart = $_SESSION['cart'] ?? [];
        $normalized = [];
        
        // Sepet key'lerini normalize et (aynı ürünleri birleştir)
        foreach ($cart as $key => $qty) {
            $parsed = self::parseCartKey(is_int($key) ? (string) $key : (string) $key);
            if ($parsed['product_id'] < 1) {
                continue;
            }
            $k = self::cartKey($parsed['product_id'], $parsed['variant_id'], $parsed['size'] ?? null);
            $normalized[$k] = ((int) ($normalized[$k] ?? 0)) + (int) $qty;
        }
        
        // Normalize edilmiş sepeti session'a kaydet
        $_SESSION['cart'] = $normalized;
        $cart = $normalized;
        
        $items = [];
        $subtotal = 0.0;
        
        if (!empty($cart)) {
            $pdo = Database::getConnection();
            
            foreach ($cart as $key => $qty) {
                $qty = (int) $qty;
                if ($qty < 1) {
                    continue;
                }
                
                $parsed = self::parseCartKey((string) $key);
                $productId = $parsed['product_id'];
                $variantId = $parsed['variant_id'];
                
                if ($productId < 1) {
                    continue;
                }
                
                // Ürün bilgilerini getir
                $stmt = $pdo->prepare('SELECT id, name, slug, price, sale_price, stock, sku FROM products WHERE id = ? AND is_active = 1 LIMIT 1');
                $stmt->execute([$productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$product) {
                    continue;
                }
                
                // Varyant varsa varyant bilgilerini getir
                $price = (float) $product['price'];
                $salePrice = $product['sale_price'] ? (float) $product['sale_price'] : null;
                $stock = (int) $product['stock'];
                $sku = $product['sku'] ?? '';
                $attributesSummary = null;
                
                if ($variantId !== null && $variantId > 0) {
                    $stmt = $pdo->prepare('
                        SELECT pv.id AS variant_id, pv.product_id, pv.sku, pv.stock, pv.price, pv.sale_price,
                               p.name, p.slug
                        FROM product_variants pv
                        INNER JOIN products p ON pv.product_id = p.id
                        WHERE pv.id = ? AND pv.product_id = ? AND p.is_active = 1 LIMIT 1
                    ');
                    $stmt->execute([$variantId, $productId]);
                    $variant = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$variant) {
                        continue;
                    }
                    
                    // Varyant attribute değerlerini getir
                    $stmt = $pdo->prepare('
                        SELECT av.value 
                        FROM product_variant_attribute_values pvav
                        INNER JOIN attribute_values av ON pvav.attribute_value_id = av.id
                        WHERE pvav.variant_id = ? 
                        ORDER BY av.sort_order
                    ');
                    $stmt->execute([$variantId]);
                    $attrValues = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    $attributesSummary = !empty($attrValues) ? implode(', ', $attrValues) : null;
                    
                    $price = $variant['price'] !== null ? (float) $variant['price'] : $price;
                    $salePrice = $variant['sale_price'] ? (float) $variant['sale_price'] : null;
                    $stock = (int) $variant['stock'];
                    $sku = $variant['sku'];
                } elseif (!empty($parsed['size'])) {
                    $attributesSummary = 'Beden: ' . $parsed['size'];
                }
                
                // Fiyat hesaplama (indirimli fiyat varsa onu kullan)
                $finalPrice = $salePrice ?? $price;
                $itemTotal = $finalPrice * $qty;
                $subtotal += $itemTotal;
                
                // Ürün görselini getir
                $stmtImg = $pdo->prepare('SELECT path FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1');
                $stmtImg->execute([$productId]);
                $imagePath = $stmtImg->fetchColumn() ?: null;
                
                $items[] = [
                    'key' => $key,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'id' => (int) $product['id'],
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'quantity' => $qty,
                    'price' => $finalPrice,
                    'total' => $itemTotal,
                    'stock' => $stock,
                    'product_sku' => $sku,
                    'attributes_summary' => $attributesSummary,
                    'image_path' => $imagePath,
                    'cart_key' => $key,
                ];
            }
        }
        
        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'count' => count($items),
        ];
    }

    /**
     * Sepete ürün ekler
     * 
     * @param int $productId Ürün ID'si
     * @param int $quantity Miktar (varsayılan: 1)
     * @param int|null $variantId Varyant ID'si (varsa)
     * @param string|null $size Beden (varsa)
     * @return bool Başarılı ise true
     */
    public static function add(int $productId, int $quantity = 1, ?int $variantId = null, ?string $size = null): bool
    {
        if ($productId < 1 || $quantity < 1) {
            return false;
        }
        
        $key = self::cartKey($productId, $variantId, $size);
        $cart = $_SESSION['cart'] ?? [];
        $cart[$key] = ((int) ($cart[$key] ?? 0)) + $quantity;
        $_SESSION['cart'] = $cart;
        return true;
    }

    /**
     * Sepetten ürün çıkarır
     * 
     * @param string $key Sepet key'i
     * @return bool Başarılı ise true
     */
    public static function remove(string $key): bool
    {
        $cart = $_SESSION['cart'] ?? [];
        if (isset($cart[$key])) {
            unset($cart[$key]);
            $_SESSION['cart'] = $cart;
            return true;
        }
        return false;
    }

    /**
     * Sepet miktarını günceller
     * 
     * @param string $key Sepet key'i
     * @param int $quantity Yeni miktar
     * @return bool Başarılı ise true
     */
    public static function update(string $key, int $quantity): bool
    {
        if ($quantity <= 0) {
            return self::remove($key);
        }
        
        $cart = $_SESSION['cart'] ?? [];
        $cart[$key] = $quantity;
        $_SESSION['cart'] = $cart;
        return true;
    }

    /**
     * Sepeti temizler
     */
    public static function clear(): void
    {
        $_SESSION['cart'] = [];
    }

    /**
     * Sepet toplamını getirir
     * 
     * @return float Sepet toplamı
     */
    public static function getTotal(): float
    {
        $result = self::getItems();
        return $result['subtotal'];
    }

    /**
     * Sepet ürün sayısını getirir
     * 
     * @return int Sepetteki ürün sayısı
     */
    public static function getCount(): int
    {
        $result = self::getItems();
        return $result['count'];
    }

    /**
     * Sepet toplam ürün adedini getirir (miktarların toplamı)
     * 
     * @return int Toplam ürün adedi
     */
    public static function getTotalQuantity(): int
    {
        $cart = $_SESSION['cart'] ?? [];
        $total = 0;
        foreach ($cart as $qty) {
            $total += (int) $qty;
        }
        return $total;
    }
}
