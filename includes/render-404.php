<?php

declare(strict_types=1);

/**
 * 404 sayfasını layout.php şablonu ile render eder.
 * Önce view ile $content üretilir, ardından includes/layout.php include edilir.
 */
http_response_code(404);

if (!function_exists('getLuminaImage')) {
    require_once __DIR__ . '/functions.php';
}

$script = $_SERVER['SCRIPT_NAME'] ?? '';
$baseUrl = dirname($script);
$baseUrl = ($baseUrl === '/' || $baseUrl === '\\') ? '' : $baseUrl;

$featuredProducts = [];
$productImages = [];
if (defined('BASE_PATH') && class_exists(\App\Config\Database::class)) {
    try {
        $pdo = \App\Config\Database::getConnection();
        $featuredProducts = $pdo->query('
            SELECT id, name, slug, price, sale_price, is_featured, is_new
            FROM products
            WHERE is_active = 1 AND is_featured = 1
            ORDER BY sort_order ASC, name ASC
            LIMIT 4
        ')->fetchAll(PDO::FETCH_ASSOC);
        if (count($featuredProducts) < 4) {
            $ids = array_column($featuredProducts, 'id');
            $placeholders = $ids ? implode(',', array_fill(0, count($ids), '?')) : '';
            $extraSql = $placeholders
                ? "SELECT id, name, slug, price, sale_price, is_featured, is_new FROM products WHERE is_active = 1 AND id NOT IN ($placeholders) ORDER BY created_at DESC LIMIT " . (4 - count($featuredProducts))
                : "SELECT id, name, slug, price, sale_price, is_featured, is_new FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 4";
            $stmt = $placeholders ? $pdo->prepare($extraSql) : $pdo->query($extraSql);
            if ($placeholders) {
                $stmt->execute($ids);
            }
            $extra = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $featuredProducts = array_merge($featuredProducts, $extra);
        }
        if (!empty($featuredProducts)) {
            $ids = array_column($featuredProducts, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT product_id, path FROM product_images WHERE product_id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
            $stmt->execute($ids);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($productImages[$row['product_id']])) {
                    $productImages[$row['product_id']] = $row['path'];
                }
            }
        }
    } catch (Throwable $e) {
        $featuredProducts = [];
        $productImages = [];
    }
}

$title = 'Sayfa Bulunamadı';
ob_start();
require BASE_PATH . '/app/Views/frontend/404.php';
$content = ob_get_clean();
require BASE_PATH . '/includes/layout.php';
exit;
