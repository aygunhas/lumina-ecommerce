<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;

/**
 * Yönetim paneli ana sayfa (dashboard)
 */
class DashboardController extends AdminBaseController
{
    public function index(): void
    {
        $pdo = Database::getConnection();

        // KPI Metrikleri
        $stats = [
            // Bugünkü satış (iptal/iade hariç)
            'sales_today' => (float) $pdo->query("
                SELECT COALESCE(SUM(total), 0) 
                FROM orders 
                WHERE DATE(created_at) = CURDATE() 
                  AND status NOT IN ('cancelled', 'refunded')
            ")->fetchColumn(),

            // Dünkü satış (iptal/iade hariç)
            'sales_yesterday' => (float) $pdo->query("
                SELECT COALESCE(SUM(total), 0) 
                FROM orders 
                WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                  AND status NOT IN ('cancelled', 'refunded')
            ")->fetchColumn(),

            // Bekleyen sevkiyatlar (beklemede, onaylanmış veya hazırlanıyor, henüz kargoda değil)
            'pending_shipments' => (int) $pdo->query("
                SELECT COUNT(*) 
                FROM orders 
                WHERE status IN ('pending', 'confirmed', 'processing')
            ")->fetchColumn(),

            // İade oranı (toplam siparişlerin yüzde kaçı iade/iptal)
            'return_rate' => 0.0, // Aşağıda hesaplanacak

            // Ortalama sepet tutarı (AOV)
            'avg_order_value' => (float) $pdo->query("
                SELECT COALESCE(AVG(total), 0) 
                FROM orders 
                WHERE status NOT IN ('cancelled', 'refunded')
            ")->fetchColumn(),
        ];

        // İade oranı hesaplama
        $totalOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        if ($totalOrders > 0) {
            $cancelledRefunded = (int) $pdo->query("
                SELECT COUNT(*) 
                FROM orders 
                WHERE status IN ('cancelled', 'refunded')
            ")->fetchColumn();
            $stats['return_rate'] = ($cancelledRefunded / $totalOrders) * 100;
        }

        // Son 30 gün gelir grafiği
        $chartData = $pdo->query("
            SELECT DATE(created_at) AS day, COALESCE(SUM(total), 0) AS total
            FROM orders
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
              AND status NOT IN ('cancelled', 'refunded')
            GROUP BY DATE(created_at)
            ORDER BY day ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        // En çok satan 3 ürün (görsellerle)
        $topProducts = $pdo->query("
            SELECT 
                p.id,
                p.name,
                p.slug,
                COALESCE(SUM(oi.quantity), 0) AS total_sold,
                (
                    SELECT pi.path 
                    FROM product_images pi 
                    WHERE pi.product_id = p.id 
                    ORDER BY pi.sort_order ASC, pi.id ASC 
                    LIMIT 1
                ) AS image_path
            FROM products p
            LEFT JOIN order_items oi ON oi.product_id = p.id
            LEFT JOIN orders o ON o.id = oi.order_id AND o.status NOT IN ('cancelled', 'refunded')
            WHERE p.is_active = 1
            GROUP BY p.id, p.name, p.slug
            HAVING total_sold > 0
            ORDER BY total_sold DESC
            LIMIT 3
        ")->fetchAll(\PDO::FETCH_ASSOC);

        // Görsel yollarını düzenle
        $baseUrl = $this->baseUrl();
        foreach ($topProducts as &$product) {
            if (!empty($product['image_path'])) {
                // Path zaten tam yol içeriyorsa olduğu gibi bırak, değilse uploads/products/ ekle
                if (strpos($product['image_path'], 'http') === 0 || strpos($product['image_path'], '/') === 0) {
                    // Zaten tam yol
                } else {
                    $product['image_path'] = $baseUrl . '/uploads/products/' . $product['image_path'];
                }
            }
        }
        unset($product);

        // Son 5 sipariş
        $recentOrders = $pdo->query("
            SELECT id, order_number, guest_first_name, guest_last_name, guest_email, total, status, created_at
            FROM orders
            ORDER BY created_at DESC
            LIMIT 5
        ")->fetchAll(\PDO::FETCH_ASSOC);

        // Varyant bazlı stok uyarıları (tükenme sınırındaki ve tükenmiş olanlar)
        $lowStockVariants = $pdo->query("
            SELECT 
                pv.id AS variant_id,
                pv.product_id,
                p.name AS product_name,
                p.slug,
                pv.sku,
                pv.stock,
                p.low_stock_threshold,
                (
                    SELECT GROUP_CONCAT(
                        CONCAT(av.value) 
                        ORDER BY av.sort_order 
                        SEPARATOR ' - '
                    )
                    FROM product_variant_attribute_values pvav
                    JOIN attribute_values av ON av.id = pvav.attribute_value_id
                    WHERE pvav.variant_id = pv.id
                ) AS attributes_summary
            FROM product_variants pv
            JOIN products p ON p.id = pv.product_id
            WHERE p.is_active = 1 
              AND pv.stock <= COALESCE(NULLIF(p.low_stock_threshold, 0), 5)
            ORDER BY pv.stock ASC
            LIMIT 15
        ")->fetchAll(\PDO::FETCH_ASSOC);
        
        // Ana ürünlerde stok düşükse onları da ekle
        // (Varyantı olmayanlar VEYA varyantlı ama ana ürün stoku da düşük olanlar)
        // NOT: Varyantlı ürünlerde ana ürün stoku da kontrol edilmeli
        $lowStockProducts = $pdo->query("
            SELECT 
                0 AS variant_id,
                p.id AS product_id,
                p.name AS product_name,
                p.slug,
                p.sku,
                p.stock,
                p.low_stock_threshold,
                NULL AS attributes_summary
            FROM products p
            WHERE p.is_active = 1 
              AND p.stock <= COALESCE(NULLIF(p.low_stock_threshold, 0), 5)
              AND (
                  -- Varyantı olmayan ürünler
                  NOT EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id)
                  OR
                  -- Varyantlı ürünlerde ana ürün stoku da düşükse
                  -- (Varyantların hiçbiri düşük stokta değilse bile ana ürün stoku gösterilmeli)
                  EXISTS (SELECT 1 FROM product_variants pv2 WHERE pv2.product_id = p.id)
              )
            ORDER BY p.stock ASC
            LIMIT 10
        ")->fetchAll(\PDO::FETCH_ASSOC);
        
        // İkisini birleştir ve tekrarları kaldır (aynı product_id'ye sahip kayıtlar)
        $allLowStock = array_merge($lowStockVariants, $lowStockProducts);
        $uniqueLowStock = [];
        $seenProducts = [];
        foreach ($allLowStock as $item) {
            $key = $item['product_id'] . '_' . ($item['variant_id'] ?? 0);
            if (!isset($seenProducts[$key])) {
                $uniqueLowStock[] = $item;
                $seenProducts[$key] = true;
            }
        }
        $lowStockVariants = $uniqueLowStock;

        $this->render('admin/dashboard', compact(
            'stats',
            'baseUrl',
            'chartData',
            'topProducts',
            'recentOrders',
            'lowStockVariants'
        ));
    }
}
