<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use PDO;

/**
 * Mağaza: Ürün detay sayfası
 */
class ProductController
{
    public function show(): void
    {
        $slug = $_GET['_slug'] ?? '';
        if ($slug === '') {
            header('Location: ' . $this->baseUrl() . '/');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.slug = ? AND p.is_active = 1 LIMIT 1
        ');
        $stmt->execute([$slug]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            http_response_code(404);
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>404</title></head><body><h1>Ürün bulunamadı</h1></body></html>';
            exit;
        }
        $pdo->prepare('UPDATE products SET view_count = view_count + 1 WHERE id = ?')->execute([$product['id']]);
        $stmt = $pdo->prepare('SELECT path FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1');
        $stmt->execute([$product['id']]);
        $firstImage = $stmt->fetch(PDO::FETCH_ASSOC);
        $productImagePath = $firstImage ? $firstImage['path'] : null;

        // Benzer ürünler (A17): aynı kategoriden, mevcut ürün hariç, en fazla 4
        $relatedProducts = [];
        $categoryId = $product['category_id'] ?? null;
        if ($categoryId !== null && $categoryId !== '') {
            $stmt = $pdo->prepare('
                SELECT id, name, slug, price, sale_price, is_featured, is_new
                FROM products
                WHERE category_id = ? AND id != ? AND is_active = 1
                ORDER BY RAND()
                LIMIT 4
            ');
            $stmt->execute([$categoryId, $product['id']]);
            $relatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if (empty($relatedProducts)) {
            $stmt = $pdo->prepare('
                SELECT id, name, slug, price, sale_price, is_featured, is_new
                FROM products
                WHERE id != ? AND is_active = 1
                ORDER BY RAND()
                LIMIT 4
            ');
            $stmt->execute([$product['id']]);
            $relatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $relatedProductImages = [];
        if (!empty($relatedProducts)) {
            $ids = array_column($relatedProducts, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT product_id, path FROM product_images WHERE product_id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
            $stmt->execute($ids);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($relatedProductImages[$row['product_id']])) {
                    $relatedProductImages[$row['product_id']] = $row['path'];
                }
            }
        }

        $isInWishlist = false;
        $userId = (int) ($_SESSION['user_id'] ?? 0);
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
        if (!empty($productVariants)) {
            $stmt = $pdo->query('SELECT id, name, type FROM attributes ORDER BY sort_order ASC, name ASC');
            $attributesForVariant = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($attributesForVariant as $a) {
                $stmt = $pdo->prepare('SELECT id, value, color_hex FROM attribute_values WHERE attribute_id = ? ORDER BY sort_order ASC, value ASC');
                $stmt->execute([$a['id']]);
                $attributeValuesByAttr[$a['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        $title = $product['name'] . ' - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $this->render('frontend/product/show', compact('title', 'baseUrl', 'product', 'productImagePath', 'relatedProducts', 'relatedProductImages', 'isInWishlist', 'userId', 'productVariants', 'attributesForVariant', 'attributeValuesByAttr'));
    }

    private function baseUrl(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = dirname($script);
        return ($base === '/' || $base === '\\') ? '' : $base;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewPath = BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewPath)) {
            echo '<p>Görünüm bulunamadı.</p>';
            return;
        }
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        $layoutPath = BASE_PATH . '/app/Views/frontend/layouts/main.php';
        require $layoutPath;
    }
}
