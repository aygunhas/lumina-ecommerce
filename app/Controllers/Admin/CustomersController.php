<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;
use PDO;

/**
 * Admin: Müşteri (üye) listesi, detay, arama
 */
class CustomersController extends AdminBaseController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $search = trim($_GET['q'] ?? $_GET['search'] ?? '');
        $sql = 'SELECT u.id, u.email, u.first_name, u.last_name, u.phone, u.is_active, u.created_at,
                       (SELECT MAX(o.created_at) FROM orders o WHERE o.user_id = u.id) AS last_order_at
                FROM users u
                WHERE 1=1';
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.phone LIKE ? OR CONCAT(u.first_name, \' \', u.last_name) LIKE ?)';
            $term = '%' . $search . '%';
            $params = array_merge($params, [$term, $term, $term, $term, $term]);
        }
        $sql .= ' ORDER BY u.created_at DESC LIMIT 200';
        $stmt = $params === [] ? $pdo->query($sql) : $pdo->prepare($sql);
        if ($params !== []) {
            $stmt->execute($params);
        }
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $baseUrl = $this->baseUrl();
        $this->render('admin/customers/index', [
            'pageTitle' => 'Müşteriler',
            'baseUrl' => $baseUrl,
            'customers' => $customers,
            'filterQ' => $search,
        ]);
    }

    public function show(): void
    {
        $baseUrl = $this->baseUrl();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/admin/customers');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$customer) {
            header('Location: ' . $baseUrl . '/admin/customers');
            exit;
        }
        $stmt = $pdo->prepare('SELECT id, order_number, total, status, payment_method, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
        $stmt->execute([$id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id ASC');
        $stmt->execute([$id]);
        $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $statusLabels = [
            'pending' => 'Beklemede', 'confirmed' => 'Onaylandı', 'processing' => 'Hazırlanıyor',
            'shipped' => 'Kargoda', 'delivered' => 'Teslim edildi', 'cancelled' => 'İptal', 'refunded' => 'İade',
        ];
        $this->render('admin/customers/show', [
            'pageTitle' => 'Müşteri: ' . trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')),
            'baseUrl' => $baseUrl,
            'customer' => $customer,
            'orders' => $orders,
            'addresses' => $addresses,
            'statusLabels' => $statusLabels,
        ]);
    }
}
