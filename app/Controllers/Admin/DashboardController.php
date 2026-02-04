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
        $stats = [
            'orders_today' => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
            'orders_total' => (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'products_total' => (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
            'users_total' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        ];
        $recentOrders = $pdo->query('
            SELECT id, order_number, guest_first_name, guest_last_name, guest_email, total, status, payment_method, created_at
            FROM orders
            ORDER BY created_at DESC
            LIMIT 10
        ')->fetchAll(\PDO::FETCH_ASSOC);
        // Düşük stok: stock <= low_stock_threshold (eşik 0 ise sadece stok 0)
        $lowStockProducts = $pdo->query('
            SELECT id, name, slug, sku, stock, low_stock_threshold
            FROM products
            WHERE is_active = 1 AND stock <= COALESCE(NULLIF(low_stock_threshold, 0), 5)
            ORDER BY stock ASC
            LIMIT 20
        ')->fetchAll(\PDO::FETCH_ASSOC);

        // Satış grafiği (B7): son 30 gün günlük toplam (delivered veya paid sayılan siparişler)
        $chartData = $pdo->query("
            SELECT DATE(created_at) AS day, COUNT(*) AS count, COALESCE(SUM(total), 0) AS total
            FROM orders
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
              AND status NOT IN ('cancelled', 'refunded')
            GROUP BY DATE(created_at)
            ORDER BY day ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);
        $chartMax = 0;
        foreach ($chartData as $row) {
            $t = (float) $row['total'];
            if ($t > $chartMax) {
                $chartMax = $t;
            }
        }
        if ($chartMax <= 0) {
            $chartMax = 1;
        }

        $baseUrl = $this->baseUrl();
        $this->render('admin/dashboard', compact('stats', 'baseUrl', 'recentOrders', 'lowStockProducts', 'chartData', 'chartMax'));
    }
}
