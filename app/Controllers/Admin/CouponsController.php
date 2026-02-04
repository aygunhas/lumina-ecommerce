<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;
use PDO;

/**
 * Admin: Kupon CRUD (B29, B30)
 */
class CouponsController extends AdminBaseController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('
            SELECT id, code, type, value, min_order_amount, max_use_count, used_count, starts_at, ends_at, is_active, created_at
            FROM coupons
            ORDER BY created_at DESC
        ');
        $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $baseUrl = $this->baseUrl();
        $this->render('admin/coupons/index', [
            'pageTitle' => 'Kuponlar',
            'baseUrl' => $baseUrl,
            'coupons' => $coupons,
        ]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }
        $baseUrl = $this->baseUrl();
        $errors = $_SESSION['coupon_errors'] ?? [];
        $old = $_SESSION['coupon_old'] ?? [];
        unset($_SESSION['coupon_errors'], $_SESSION['coupon_old']);
        $this->render('admin/coupons/form', [
            'pageTitle' => 'Yeni kupon',
            'baseUrl' => $baseUrl,
            'coupon' => null,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function store(): void
    {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['type'] ?? 'percent';
        if (!in_array($type, ['percent', 'fixed'], true)) {
            $type = 'percent';
        }
        $value = (float) str_replace(',', '.', $_POST['value'] ?? '0');
        $minOrderAmount = trim($_POST['min_order_amount'] ?? '');
        $minOrderAmount = $minOrderAmount !== '' ? (float) str_replace(',', '.', $minOrderAmount) : null;
        $maxUseCount = trim($_POST['max_use_count'] ?? '');
        $maxUseCount = $maxUseCount !== '' ? (int) $maxUseCount : null;
        $startsAt = trim($_POST['starts_at'] ?? '');
        $startsAt = $startsAt !== '' ? $startsAt . ' 00:00:00' : null;
        $endsAt = trim($_POST['ends_at'] ?? '');
        $endsAt = $endsAt !== '' ? $endsAt . ' 23:59:59' : null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if ($code === '') {
            $errors['code'] = 'Kupon kodu zorunludur.';
        }
        if ($value <= 0) {
            $errors['value'] = 'İndirim değeri 0\'dan büyük olmalıdır.';
        }
        if ($type === 'percent' && $value > 100) {
            $errors['value'] = 'Yüzde indirim en fazla 100 olabilir.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            $_SESSION['coupon_errors'] = $errors;
            $_SESSION['coupon_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/coupons/create');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM coupons WHERE code = ? LIMIT 1');
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            $_SESSION['coupon_errors'] = ['code' => 'Bu kupon kodu zaten kayıtlı.'];
            $_SESSION['coupon_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/coupons/create');
            exit;
        }

        $pdo->prepare('
            INSERT INTO coupons (code, type, value, min_order_amount, max_use_count, used_count, starts_at, ends_at, is_active, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, NOW(), NOW())
        ')->execute([$code, $type, $value, $minOrderAmount, $maxUseCount, $startsAt, $endsAt, $isActive]);
        header('Location: ' . $baseUrl . '/admin/coupons?created=1');
        exit;
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $baseUrl = $this->baseUrl();
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/admin/coupons');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM coupons WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$coupon) {
            header('Location: ' . $baseUrl . '/admin/coupons');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
            return;
        }
        $errors = $_SESSION['coupon_errors'] ?? [];
        $old = $_SESSION['coupon_old'] ?? [];
        unset($_SESSION['coupon_errors'], $_SESSION['coupon_old']);
        $this->render('admin/coupons/form', [
            'pageTitle' => 'Kupon düzenle',
            'baseUrl' => $baseUrl,
            'coupon' => $coupon,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function update(int $id): void
    {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['type'] ?? 'percent';
        if (!in_array($type, ['percent', 'fixed'], true)) {
            $type = 'percent';
        }
        $value = (float) str_replace(',', '.', $_POST['value'] ?? '0');
        $minOrderAmount = trim($_POST['min_order_amount'] ?? '');
        $minOrderAmount = $minOrderAmount !== '' ? (float) str_replace(',', '.', $minOrderAmount) : null;
        $maxUseCount = trim($_POST['max_use_count'] ?? '');
        $maxUseCount = $maxUseCount !== '' ? (int) $maxUseCount : null;
        $startsAt = trim($_POST['starts_at'] ?? '');
        $startsAt = $startsAt !== '' ? $startsAt . ' 00:00:00' : null;
        $endsAt = trim($_POST['ends_at'] ?? '');
        $endsAt = $endsAt !== '' ? $endsAt . ' 23:59:59' : null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if ($code === '') {
            $errors['code'] = 'Kupon kodu zorunludur.';
        }
        if ($value <= 0) {
            $errors['value'] = 'İndirim değeri 0\'dan büyük olmalıdır.';
        }
        if ($type === 'percent' && $value > 100) {
            $errors['value'] = 'Yüzde indirim en fazla 100 olabilir.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            $_SESSION['coupon_errors'] = $errors;
            $_SESSION['coupon_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/coupons/edit?id=' . $id);
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM coupons WHERE code = ? AND id != ? LIMIT 1');
        $stmt->execute([$code, $id]);
        if ($stmt->fetch()) {
            $_SESSION['coupon_errors'] = ['code' => 'Bu kupon kodu zaten kayıtlı.'];
            $_SESSION['coupon_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/coupons/edit?id=' . $id);
            exit;
        }

        $pdo->prepare('
            UPDATE coupons SET code = ?, type = ?, value = ?, min_order_amount = ?, max_use_count = ?, starts_at = ?, ends_at = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ')->execute([$code, $type, $value, $minOrderAmount, $maxUseCount, $startsAt, $endsAt, $isActive, $id]);
        header('Location: ' . $baseUrl . '/admin/coupons?updated=1');
        exit;
    }

    public function delete(): void
    {
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
        $baseUrl = $this->baseUrl();
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/admin/coupons');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM coupons WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$coupon) {
            header('Location: ' . $baseUrl . '/admin/coupons');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo->prepare('DELETE FROM coupons WHERE id = ?')->execute([$id]);
            header('Location: ' . $baseUrl . '/admin/coupons?deleted=1');
            exit;
        }
        $this->render('admin/coupons/delete', [
            'pageTitle' => 'Kupon sil',
            'baseUrl' => $baseUrl,
            'coupon' => $coupon,
        ]);
    }
}
