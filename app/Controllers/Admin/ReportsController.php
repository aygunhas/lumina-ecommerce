<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;
use PDO;

/**
 * Admin: Satış ve stok raporları (B39, B40)
 */
class ReportsController extends AdminBaseController
{
    public function index(): void
    {
        $title = 'Raporlar';
        $baseUrl = $this->baseUrl();
        $this->render('admin/reports/index', [
            'pageTitle' => $title,
            'baseUrl' => $baseUrl,
        ]);
    }

    /** Satış raporu: tarih aralığı, satış tutarı, sipariş sayısı, en çok satan ürünler */
    public function sales(): void
    {
        $baseUrl = $this->baseUrl();
        $dateFrom = trim($_GET['date_from'] ?? date('Y-m-01'));
        $dateTo = trim($_GET['date_to'] ?? date('Y-m-d'));
        if ($dateFrom === '') {
            $dateFrom = date('Y-m-01');
        }
        if ($dateTo === '') {
            $dateTo = date('Y-m-d');
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT COUNT(*) AS order_count, COALESCE(SUM(total), 0) AS total_sales
            FROM orders
            WHERE DATE(created_at) >= ? AND DATE(created_at) <= ? AND status NOT IN (\'cancelled\', \'refunded\')
        ');
        $stmt->execute([$dateFrom, $dateTo]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare('
            SELECT oi.product_id, oi.product_name, oi.product_sku, SUM(oi.quantity) AS total_qty, SUM(oi.total) AS total_amount
            FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            WHERE DATE(o.created_at) >= ? AND DATE(o.created_at) <= ? AND o.status NOT IN (\'cancelled\', \'refunded\')
            GROUP BY oi.product_id, oi.product_name, oi.product_sku
            ORDER BY total_qty DESC
            LIMIT 20
        ');
        $stmt->execute([$dateFrom, $dateTo]);
        $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->render('admin/reports/sales', [
            'pageTitle' => 'Satış raporu',
            'baseUrl' => $baseUrl,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'summary' => $summary,
            'topProducts' => $topProducts,
        ]);
    }

    /** Stok raporu: mevcut stok listesi, düşük stok uyarısı */
    public function stock(): void
    {
        $baseUrl = $this->baseUrl();
        $pdo = Database::getConnection();
        $stmt = $pdo->query('
            SELECT p.id, p.name, p.sku, p.stock, p.low_stock_threshold, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1
            ORDER BY p.stock ASC, p.name ASC
        ');
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $lowStock = [];
        foreach ($products as $p) {
            $threshold = (int) ($p['low_stock_threshold'] ?? 5);
            if ($threshold === 0) {
                $threshold = 5;
            }
            if ((int) $p['stock'] <= $threshold) {
                $lowStock[] = $p;
            }
        }
        $this->render('admin/reports/stock', [
            'pageTitle' => 'Stok raporu',
            'baseUrl' => $baseUrl,
            'products' => $products,
            'lowStock' => $lowStock,
        ]);
    }
}
