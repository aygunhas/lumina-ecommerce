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

    /** Hesabım ana sayfa – linkler */
    public function index(): void
    {
        $title = 'Hesabım - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $userName = $_SESSION['user_name'] ?? 'Üye';
        $this->render('frontend/account/index', compact('title', 'baseUrl', 'userName'));
    }

    /** Siparişlerim listesi */
    public function orders(): void
    {
        $uid = $this->userId();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT id, order_number, total, status, payment_method, created_at
            FROM orders
            WHERE user_id = ?
            ORDER BY created_at DESC
        ');
        $stmt->execute([$uid]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $title = 'Siparişlerim - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $statusLabels = [
            'pending' => 'Beklemede', 'confirmed' => 'Onaylandı', 'processing' => 'Hazırlanıyor',
            'shipped' => 'Kargoda', 'delivered' => 'Teslim edildi', 'cancelled' => 'İptal', 'refunded' => 'İade',
        ];
        $this->render('frontend/account/orders', compact('title', 'baseUrl', 'orders', 'statusLabels'));
    }

    /** Sipariş detay + kargo takip */
    public function orderShow(): void
    {
        $uid = $this->userId();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $baseUrl = $this->baseUrl();
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/hesabim/siparisler');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $uid]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            header('Location: ' . $baseUrl . '/hesabim/siparisler');
            exit;
        }
        $stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
        $stmt->execute([$id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare('SELECT * FROM shipments WHERE order_id = ? ORDER BY shipped_at DESC');
        $stmt->execute([$id]);
        $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $statusLabels = [
            'pending' => 'Beklemede', 'confirmed' => 'Onaylandı', 'processing' => 'Hazırlanıyor',
            'shipped' => 'Kargoda', 'delivered' => 'Teslim edildi', 'cancelled' => 'İptal', 'refunded' => 'İade',
        ];
        $title = 'Sipariş ' . $order['order_number'];
        $this->render('frontend/account/order_show', compact('title', 'baseUrl', 'order', 'items', 'shipments', 'statusLabels'));
    }

    /** Adreslerim listesi */
    public function addresses(): void
    {
        $uid = $this->userId();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id ASC');
        $stmt->execute([$uid]);
        $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $title = 'Adreslerim - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $this->render('frontend/account/addresses', compact('title', 'baseUrl', 'addresses'));
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
            if (!empty($errors)) {
                $_SESSION['address_errors'] = $errors;
                $_SESSION['address_old'] = $_POST;
                header('Location: ' . $baseUrl . '/hesabim/adresler/ekle');
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
            header('Location: ' . $baseUrl . '/hesabim/adresler?added=1');
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
                header('Location: ' . $baseUrl . '/hesabim/adresler/duzenle?id=' . $id);
                exit;
            }
            if ($isDefault) {
                $pdo->prepare('UPDATE addresses SET is_default = 0 WHERE user_id = ?')->execute([$uid]);
            }
            $pdo->prepare('
                UPDATE addresses SET title = ?, first_name = ?, last_name = ?, phone = ?, city = ?, district = ?, address_line = ?, postal_code = ?, is_default = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ')->execute([$title ?: null, $firstName, $lastName, $phone, $city, $district, $addressLine, $postalCode ?: null, $isDefault, $id, $uid]);
            header('Location: ' . $baseUrl . '/hesabim/adresler?updated=1');
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
            $pdo->prepare('DELETE FROM addresses WHERE id = ? AND user_id = ?')->execute([$id, $uid]);
            header('Location: ' . $baseUrl . '/hesabim/adresler?deleted=1');
            exit;
        }
        $title = 'Adresi sil - ' . env('APP_NAME', 'Lumina Boutique');
        $this->render('frontend/account/address_delete', compact('title', 'baseUrl', 'address'));
    }

    /** Bilgilerim (profil) formu veya güncelle */
    public function profile(): void
    {
        $uid = $this->userId();
        $baseUrl = $this->baseUrl();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, email, first_name, last_name, phone FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            header('Location: ' . $baseUrl . '/cikis');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $errors = [];
            if ($firstName === '') $errors['first_name'] = 'Ad zorunludur.';
            if ($lastName === '') $errors['last_name'] = 'Soyad zorunludur.';
            if ($newPassword !== '') {
                if (strlen($newPassword) < 6) {
                    $errors['new_password'] = 'Yeni şifre en az 6 karakter olmalıdır.';
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
                header('Location: ' . $baseUrl . '/hesabim/bilgilerim');
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
            header('Location: ' . $baseUrl . '/hesabim/bilgilerim?updated=1');
            exit;
        }
        $errors = $_SESSION['profile_errors'] ?? [];
        $old = $_SESSION['profile_old'] ?? [];
        unset($_SESSION['profile_errors'], $_SESSION['profile_old']);
        $title = 'Bilgilerim - ' . env('APP_NAME', 'Lumina Boutique');
        $this->render('frontend/account/profile', compact('title', 'baseUrl', 'user', 'errors', 'old'));
    }

    /** Favori listesi (A33) */
    public function favoriler(): void
    {
        $uid = $this->userId();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.is_featured, p.is_new
            FROM wishlists w
            INNER JOIN products p ON w.product_id = p.id AND p.is_active = 1
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ');
        $stmt->execute([$uid]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $productImages = [];
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
        $title = 'Favorilerim - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $this->render('frontend/account/favoriler', compact('title', 'baseUrl', 'products', 'productImages'));
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
