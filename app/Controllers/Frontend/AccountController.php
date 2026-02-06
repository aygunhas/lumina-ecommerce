<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use PDO;

/**
 * Mağaza: Hesabım – siparişlerim, adreslerim, bilgilerim
 */
class AccountController
{
    private function userId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
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
        $baseUrl = $baseUrl ?? $this->baseUrl();
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

    /** Hesabım ana sayfa – tüm sekmeler ?tab= ile tek view */
    public function index(): void
    {
        $uid = $this->userId();
        $baseUrl = $this->baseUrl();
        $userName = trim($_SESSION['user_name'] ?? 'Üye');
        $userEmail = $_SESSION['user_email'] ?? '';
        $activeTab = $_GET['tab'] ?? 'overview';
        $orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        $pdo = Database::getConnection();
        $statusLabels = [
            'pending' => 'Beklemede', 'confirmed' => 'Onaylandı', 'processing' => 'Hazırlanıyor',
            'shipped' => 'Kargoda', 'delivered' => 'Teslim edildi', 'cancelled' => 'İptal', 'refunded' => 'İade',
        ];

        $lastOrder = null;
        $defaultAddress = null;
        $orders = [];
        $orderItemsCount = [];
        $order = null;
        $items = [];
        $shipments = [];
        $addresses = [];
        $user = null;
        $products = [];
        $productImages = [];

        if ($uid > 0) {
            $stmt = $pdo->prepare('SELECT id, order_number, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
            $stmt->execute([$uid]);
            $lastOrder = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = $pdo->prepare('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id ASC LIMIT 1');
            $stmt->execute([$uid]);
            $defaultAddress = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($activeTab === 'orders' || $activeTab === 'overview') {
            $stmt = $pdo->prepare('SELECT id, order_number, total, status, payment_method, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC');
            $stmt->execute([$uid]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($orders)) {
                $ids = array_column($orders, 'id');
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("SELECT order_id, SUM(quantity) AS item_count FROM order_items WHERE order_id IN ($placeholders) GROUP BY order_id");
                $stmt->execute($ids);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $orderItemsCount[(int) $row['order_id']] = (int) $row['item_count'];
                }
            }
        }

        if ($activeTab === 'order-detail') {
            if ($orderId < 1) {
                header('Location: ' . $baseUrl . '/hesabim?tab=orders');
                exit;
            }
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
            $stmt->execute([$orderId, $uid]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$order) {
                header('Location: ' . $baseUrl . '/hesabim?tab=orders');
                exit;
            }
            $stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = $pdo->prepare('SELECT * FROM shipments WHERE order_id = ? ORDER BY shipped_at DESC');
            $stmt->execute([$orderId]);
            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($activeTab === 'addresses') {
            $stmt = $pdo->prepare('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id ASC');
            $stmt->execute([$uid]);
            $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($activeTab === 'details') {
            $stmt = $pdo->prepare('SELECT id, email, first_name, last_name, phone FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$uid]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($activeTab === 'favorites') {
            $stmt = $pdo->prepare('
                SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.is_featured
                FROM wishlists w
                INNER JOIN products p ON w.product_id = p.id AND p.is_active = 1
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC
            ');
            $stmt->execute([$uid]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($products)) {
                $ids = array_column($products, 'id');
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("SELECT product_id, path FROM product_images WHERE product_id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
                $stmt->execute($ids);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (!isset($productImages[$row['product_id']])) {
                        $productImages[$row['product_id']] = $row['path'];
                    }
                }
            }
        }

        $errors = $_SESSION['profile_errors'] ?? $_SESSION['address_errors'] ?? [];
        $old = $_SESSION['profile_old'] ?? $_SESSION['address_old'] ?? [];
        unset($_SESSION['profile_errors'], $_SESSION['profile_old'], $_SESSION['address_errors'], $_SESSION['address_old']);

        $title = 'Hesabım - ' . env('APP_NAME', 'Lumina Boutique');
        $this->render('frontend/account/index', compact('title', 'baseUrl', 'userName', 'userEmail', 'activeTab', 'lastOrder', 'defaultAddress', 'statusLabels', 'orders', 'orderItemsCount', 'order', 'items', 'shipments', 'addresses', 'user', 'products', 'productImages', 'errors', 'old'));
    }

    /** Siparişlerim listesi – yönlendirme */
    public function orders(): void
    {
        header('Location: ' . $this->baseUrl() . '/hesabim?tab=orders');
        exit;
    }

    /** Sipariş detay – yönlendirme */
    public function orderShow(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $baseUrl = $this->baseUrl();
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/hesabim?tab=orders');
            exit;
        }
        header('Location: ' . $baseUrl . '/hesabim?tab=order-detail&id=' . $id);
        exit;
    }

    /** Adreslerim listesi – yönlendirme */
    public function addresses(): void
    {
        header('Location: ' . $this->baseUrl() . '/hesabim?tab=addresses');
        exit;
    }

    /** Yeni adres formu veya kaydet */
    public function addressCreate(): void
    {
        $uid = $this->userId();
        $baseUrl = $this->baseUrl();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $district = trim($_POST['district'] ?? '');
            $addressLine = trim($_POST['address_line'] ?? '');
            $postalCode = trim($_POST['postal_code'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $isDefault = isset($_POST['is_default']) ? 1 : 0;
            $errors = [];
            if ($firstName === '') $errors['first_name'] = 'Ad zorunludur.';
            if ($lastName === '') $errors['last_name'] = 'Soyad zorunludur.';
            if ($phone === '') $errors['phone'] = 'Telefon zorunludur.';
            if ($city === '') $errors['city'] = 'İl zorunludur.';
            if ($district === '') $errors['district'] = 'İlçe zorunludur.';
            if ($addressLine === '') $errors['address_line'] = 'Adres zorunludur.';
            $redirect = trim($_POST['redirect'] ?? $_GET['redirect'] ?? '');
            $redirectUrl = ($redirect !== '' && strpos($redirect, '/') === 0) ? $redirect : '/hesabim?tab=addresses';
            if (!empty($errors)) {
                $_SESSION['address_errors'] = $errors;
                $_SESSION['address_old'] = $_POST;
                header('Location: ' . $baseUrl . $redirectUrl);
                exit;
            }
            $pdo = Database::getConnection();
            if ($isDefault) {
                $pdo->prepare('UPDATE addresses SET is_default = 0 WHERE user_id = ?')->execute([$uid]);
            }
            $pdo->prepare('
                INSERT INTO addresses (user_id, title, first_name, last_name, phone, city, district, address_line, postal_code, is_default, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ')->execute([$uid, $title ?: null, $firstName, $lastName, $phone, $city, $district, $addressLine, $postalCode ?: null, $isDefault]);
            if (strpos($redirectUrl, '/hesabim') === 0) {
                $sep = strpos($redirectUrl, '?') !== false ? '&' : '?';
                $redirectUrl .= $sep . 'added=1';
            }
            header('Location: ' . $baseUrl . $redirectUrl);
            exit;
        }
        $errors = $_SESSION['address_errors'] ?? [];
        $old = $_SESSION['address_old'] ?? [];
        unset($_SESSION['address_errors'], $_SESSION['address_old']);
        $title = 'Yeni adres - ' . env('APP_NAME', 'Lumina Boutique');
        $this->render('frontend/account/address_form', compact('title', 'baseUrl', 'errors', 'old'));
    }

    /** Adres düzenle formu veya güncelle */
    public function addressEdit(): void
    {
        $uid = $this->userId();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $baseUrl = $this->baseUrl();
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/hesabim/adresler');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM addresses WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $uid]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$address) {
            header('Location: ' . $baseUrl . '/hesabim/adresler');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $district = trim($_POST['district'] ?? '');
            $addressLine = trim($_POST['address_line'] ?? '');
            $postalCode = trim($_POST['postal_code'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $isDefault = isset($_POST['is_default']) ? 1 : 0;
            $errors = [];
            if ($firstName === '') $errors['first_name'] = 'Ad zorunludur.';
            if ($lastName === '') $errors['last_name'] = 'Soyad zorunludur.';
            if ($phone === '') $errors['phone'] = 'Telefon zorunludur.';
            if ($city === '') $errors['city'] = 'İl zorunludur.';
            if ($district === '') $errors['district'] = 'İlçe zorunludur.';
            if ($addressLine === '') $errors['address_line'] = 'Adres zorunludur.';
            if (!empty($errors)) {
                $_SESSION['address_errors'] = $errors;
                $_SESSION['address_old'] = $_POST;
                header('Location: ' . $baseUrl . '/hesabim?tab=addresses');
                exit;
            }
            if ($isDefault) {
                $pdo->prepare('UPDATE addresses SET is_default = 0 WHERE user_id = ?')->execute([$uid]);
            }
            $pdo->prepare('
                UPDATE addresses SET title = ?, first_name = ?, last_name = ?, phone = ?, city = ?, district = ?, address_line = ?, postal_code = ?, is_default = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ')->execute([$title ?: null, $firstName, $lastName, $phone, $city, $district, $addressLine, $postalCode ?: null, $isDefault, $id, $uid]);
            header('Location: ' . $baseUrl . '/hesabim?tab=addresses&updated=1');
            exit;
        }
        $errors = $_SESSION['address_errors'] ?? [];
        $old = $_SESSION['address_old'] ?? [];
        unset($_SESSION['address_errors'], $_SESSION['address_old']);
        $title = 'Adres düzenle - ' . env('APP_NAME', 'Lumina Boutique');
        $this->render('frontend/account/address_form', compact('title', 'baseUrl', 'errors', 'old', 'address'));
    }

    /** Adres sil (GET: onay, POST: sil) */
    public function addressDelete(): void
    {
        $uid = $this->userId();
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
        $baseUrl = $this->baseUrl();
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/hesabim?tab=addresses');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM addresses WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $uid]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$address) {
            header('Location: ' . $baseUrl . '/hesabim?tab=addresses');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo->prepare('DELETE FROM addresses WHERE id = ? AND user_id = ?')->execute([$id, $uid]);
            header('Location: ' . $baseUrl . '/hesabim?tab=addresses&deleted=1');
            exit;
        }
        $title = 'Adresi sil - ' . env('APP_NAME', 'Lumina Boutique');
        $this->render('frontend/account/address_delete', compact('title', 'baseUrl', 'address'));
    }

    /** Bilgilerim (profil) – GET yönlendirme, POST güncelle */
    public function profile(): void
    {
        $uid = $this->userId();
        $baseUrl = $this->baseUrl();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $baseUrl . '/hesabim?tab=details');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, email, first_name, last_name, phone FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            header('Location: ' . $baseUrl . '/cikis');
            exit;
        }
        $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $errors = [];
            if ($firstName === '') $errors['first_name'] = 'Ad zorunludur.';
            if ($lastName === '') $errors['last_name'] = 'Soyad zorunludur.';
            if ($newPassword !== '') {
                if (strlen($newPassword) < 8) {
                    $errors['new_password'] = 'Yeni şifre en az 8 karakter olmalıdır.';
                } elseif (!preg_match('/[A-Z]/', $newPassword)) {
                    $errors['new_password'] = 'Şifre en az bir büyük harf içermelidir.';
                } elseif (!preg_match('/[0-9]/', $newPassword)) {
                    $errors['new_password'] = 'Şifre en az bir rakam içermelidir.';
                } elseif (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
                    $errors['new_password'] = 'Şifre en az bir özel karakter içermelidir (!@#$% vb.).';
                } else {
                    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
                    $stmt->execute([$uid]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$row || !password_verify($currentPassword, $row['password'])) {
                        $errors['current_password'] = 'Mevcut şifre hatalı.';
                    }
                }
            }
            if (!empty($errors)) {
                $_SESSION['profile_errors'] = $errors;
                $_SESSION['profile_old'] = $_POST;
                header('Location: ' . $baseUrl . '/hesabim?tab=details');
                exit;
            }
            if ($newPassword !== '') {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, phone = ?, password = ?, updated_at = NOW() WHERE id = ?')
                    ->execute([$firstName, $lastName, $phone ?: null, $hash, $uid]);
            } else {
                $pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, phone = ?, updated_at = NOW() WHERE id = ?')
                    ->execute([$firstName, $lastName, $phone ?: null, $uid]);
            }
            $_SESSION['user_name'] = trim($firstName . ' ' . $lastName);
            header('Location: ' . $baseUrl . '/hesabim?tab=details&updated=1');
            exit;
    }

    /** Favori listesi – yönlendirme */
    public function favoriler(): void
    {
        header('Location: ' . $this->baseUrl() . '/hesabim?tab=favorites');
        exit;
    }

    /** Favorilere ekle (POST product_id, redirect) */
    public function wishlistAdd(): void
    {
        $uid = $this->userId();
        $productId = (int) ($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        $baseUrl = $this->baseUrl();
        $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? $baseUrl . '/';
        if ($productId < 1) {
            header('Location: ' . $redirect);
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM products WHERE id = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$productId]);
        if (!$stmt->fetch()) {
            header('Location: ' . $redirect);
            exit;
        }
        $pdo->prepare('INSERT IGNORE INTO wishlists (user_id, product_id, created_at) VALUES (?, ?, NOW())')->execute([$uid, $productId]);
        header('Location: ' . $redirect);
        exit;
    }

    /** Favorilerden çıkar */
    public function wishlistRemove(): void
    {
        $uid = $this->userId();
        $productId = (int) ($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        $baseUrl = $this->baseUrl();
        $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? $baseUrl . '/hesabim/favoriler';
        if ($productId > 0) {
            $pdo = Database::getConnection();
            $pdo->prepare('DELETE FROM wishlists WHERE user_id = ? AND product_id = ?')->execute([$uid, $productId]);
        }
        header('Location: ' . $redirect);
        exit;
    }
}
