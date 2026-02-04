<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;
use PDO;

/**
 * Sipariş yönetimi – liste, detay, durum güncelleme
 */
class OrdersController extends AdminBaseController
{
    private const STATUS_OPTIONS = [
        'pending' => 'Beklemede',
        'confirmed' => 'Onaylandı',
        'processing' => 'Hazırlanıyor',
        'shipped' => 'Kargoda',
        'delivered' => 'Teslim edildi',
        'cancelled' => 'İptal',
        'refunded' => 'İade',
    ];

    public function index(): void
    {
        $pdo = Database::getConnection();
        $search = trim($_GET['q'] ?? $_GET['search'] ?? '');
        $statusFilter = trim($_GET['status'] ?? '');
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');

        $sql = 'SELECT id, order_number, guest_first_name, guest_last_name, guest_email, total, status, payment_method, payment_status, created_at FROM orders WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (order_number LIKE ? OR guest_first_name LIKE ? OR guest_last_name LIKE ? OR guest_email LIKE ? OR CONCAT(guest_first_name, \' \', guest_last_name) LIKE ?)';
            $term = '%' . $search . '%';
            $params = array_merge($params, [$term, $term, $term, $term, $term]);
        }
        if ($statusFilter !== '' && isset(self::STATUS_OPTIONS[$statusFilter])) {
            $sql .= ' AND status = ?';
            $params[] = $statusFilter;
        }
        if ($dateFrom !== '') {
            $sql .= ' AND DATE(created_at) >= ?';
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $sql .= ' AND DATE(created_at) <= ?';
            $params[] = $dateTo;
        }

        $sql .= ' ORDER BY created_at DESC LIMIT 200';
        $stmt = $params === [] ? $pdo->query($sql) : $pdo->prepare($sql);
        if ($params !== []) {
            $stmt->execute($params);
        }
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $baseUrl = $this->baseUrl();
        $this->render('admin/orders/index', [
            'pageTitle' => 'Siparişler',
            'baseUrl' => $baseUrl,
            'orders' => $orders,
            'statusOptions' => self::STATUS_OPTIONS,
            'filterQ' => $search,
            'filterStatus' => $statusFilter,
            'filterDateFrom' => $dateFrom,
            'filterDateTo' => $dateTo,
        ]);
    }

    public function show(): void
    {
        $baseUrl = $this->baseUrl();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/admin/orders');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            header('Location: ' . $baseUrl . '/admin/orders');
            exit;
        }
        $stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
        $stmt->execute([$id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare('SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at DESC');
        $stmt->execute([$id]);
        $statusHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare('SELECT * FROM shipments WHERE order_id = ? ORDER BY shipped_at DESC, id DESC');
        $stmt->execute([$id]);
        $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->render('admin/orders/show', [
            'pageTitle' => 'Sipariş ' . $order['order_number'],
            'baseUrl' => $baseUrl,
            'order' => $order,
            'items' => $items,
            'statusHistory' => $statusHistory,
            'shipments' => $shipments,
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    /** Sipariş fişi – yazdırma dostu sayfa (layout yok) */
    public function print(): void
    {
        $baseUrl = $this->baseUrl();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/admin/orders');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            header('Location: ' . $baseUrl . '/admin/orders');
            exit;
        }
        $stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
        $stmt->execute([$id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare('SELECT * FROM shipments WHERE order_id = ? ORDER BY shipped_at DESC, id DESC');
        $stmt->execute([$id]);
        $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->renderWithoutLayout('admin/orders/print', [
            'baseUrl' => $baseUrl,
            'order' => $order,
            'items' => $items,
            'shipments' => $shipments,
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function addShipment(): void
    {
        $baseUrl = $this->baseUrl();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $baseUrl . '/admin/orders');
            exit;
        }
        $id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
        $carrier = trim($_POST['carrier'] ?? '');
        $trackingNumber = trim($_POST['tracking_number'] ?? '');
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/admin/orders');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, status FROM orders WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            header('Location: ' . $baseUrl . '/admin/orders');
            exit;
        }
        $pdo->prepare('INSERT INTO shipments (order_id, carrier, tracking_number, shipped_at, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW(), NOW())')
            ->execute([$id, $carrier ?: null, $trackingNumber ?: null]);
        $currentStatus = $order['status'];
        $setShipped = !empty($_POST['set_status_shipped']) && $currentStatus !== 'shipped' && $currentStatus !== 'delivered' && $currentStatus !== 'cancelled' && $currentStatus !== 'refunded';
        if ($setShipped) {
            $pdo->prepare('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?')->execute(['shipped', $id]);
            $pdo->prepare('INSERT INTO order_status_history (order_id, status, note, created_at) VALUES (?, ?, ?, NOW())')
                ->execute([$id, 'shipped', $trackingNumber ? 'Takip no: ' . $trackingNumber : null]);
        }
        header('Location: ' . $baseUrl . '/admin/orders/show?id=' . $id . '&shipment=1');
        exit;
    }

    public function updateStatus(): void
    {
        $baseUrl = $this->baseUrl();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $baseUrl . '/admin/orders');
            exit;
        }
        $id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
        $newStatus = trim($_POST['status'] ?? '');
        if ($id < 1 || !isset(self::STATUS_OPTIONS[$newStatus])) {
            header('Location: ' . $baseUrl . '/admin/orders');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, status FROM orders WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            header('Location: ' . $baseUrl . '/admin/orders');
            exit;
        }
        if ($order['status'] !== $newStatus) {
            $pdo->prepare('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$newStatus, $id]);
            $note = trim($_POST['status_note'] ?? '');
            $pdo->prepare('INSERT INTO order_status_history (order_id, status, note, created_at) VALUES (?, ?, ?, NOW())')->execute([$id, $newStatus, $note ?: null]);
        }
        header('Location: ' . $baseUrl . '/admin/orders/show?id=' . $id . '&updated=1');
        exit;
    }
}
