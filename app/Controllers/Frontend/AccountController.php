<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use PDO;

/**
 * Mağaza: Hesabım – siparişlerim, adreslerim, bilgilerim
 */
class AccountController extends FrontendBaseController
{

    /** Hesabım ana sayfa – tüm sekmeler ?tab= ile tek view */
    public function index(): void
    {
        $uid = $this->userId();
        $baseUrl = $this->baseUrl();
        $userName = trim($_SESSION['user_name'] ?? 'Üye');
        $userEmail = $_SESSION['user_email'] ?? '';
        $activeTab = $_GET['tab'] ?? 'overview';
        $orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

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
            // Son sipariş
            $allOrders = Order::getByUserId($uid);
            $lastOrder = !empty($allOrders) ? $allOrders[0] : null;
            
            // Varsayılan adres
            $defaultAddress = Address::getDefaultByUserId($uid);
        }

        if ($activeTab === 'orders' || $activeTab === 'overview') {
            $orders = Order::getByUserId($uid);
            if (!empty($orders)) {
                $pdo = Database::getConnection();
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
                $this->redirect('/hesabim?tab=orders');
            }
            $order = Order::find($orderId);
            if (!$order || (int) $order['user_id'] !== $uid) {
                $this->redirect('/hesabim?tab=orders');
            }
            $items = Order::getItems($orderId);
            $shipments = Order::getShipments($orderId);
        }

        if ($activeTab === 'addresses') {
            $addresses = Address::getByUserId($uid);
        }

        if ($activeTab === 'details') {
            $user = User::find($uid);
        }

        if ($activeTab === 'favorites') {
            $pdo = Database::getConnection();
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
                $productImages = Product::getMainImagesForProducts($ids);
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
        $this->redirect('/hesabim?tab=orders');
    }

    /** Sipariş detay – yönlendirme */
    public function orderShow(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $baseUrl = $this->baseUrl();
        if ($id < 1) {
            $this->redirect('/hesabim?tab=orders');
        }
        $this->redirect('/hesabim?tab=order-detail&id=' . $id);
    }

    /** Adreslerim listesi – yönlendirme */
    public function addresses(): void
    {
        $this->redirect('/hesabim?tab=addresses');
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
            $this->redirect($redirectUrl);
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
            $this->redirect('/hesabim/adresler');
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM addresses WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $uid]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$address) {
            $this->redirect('/hesabim/adresler');
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
                $this->redirect('/hesabim?tab=addresses');
            }
            if ($isDefault) {
                $pdo->prepare('UPDATE addresses SET is_default = 0 WHERE user_id = ?')->execute([$uid]);
            }
            $pdo->prepare('
                UPDATE addresses SET title = ?, first_name = ?, last_name = ?, phone = ?, city = ?, district = ?, address_line = ?, postal_code = ?, is_default = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ')->execute([$title ?: null, $firstName, $lastName, $phone, $city, $district, $addressLine, $postalCode ?: null, $isDefault, $id, $uid]);
            $this->redirect('/hesabim?tab=addresses&updated=1');
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
            $this->redirect('/hesabim?tab=addresses');
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM addresses WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $uid]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$address) {
            $this->redirect('/hesabim?tab=addresses');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo->prepare('DELETE FROM addresses WHERE id = ? AND user_id = ?')->execute([$id, $uid]);
            $this->redirect('/hesabim?tab=addresses&deleted=1');
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
            $this->redirect('/hesabim?tab=details');
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, email, first_name, last_name, phone FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $this->redirect('/cikis');
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
                $this->redirect('/hesabim?tab=details');
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
            $this->redirect('/hesabim?tab=details&updated=1');
    }

    /** Favori listesi – yönlendirme */
    public function favoriler(): void
    {
        $this->redirect('/hesabim?tab=favorites');
    }

    /** Favorilere ekle (POST product_id, redirect) */
    public function wishlistAdd(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $uid = $this->userId();
        $productId = (int) ($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        $baseUrl = $this->baseUrl();
        $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? $baseUrl . '/';
        
        if ($productId < 1) {
            if ($isAjax) {
                $this->json(['success' => false, 'message' => 'Geçersiz ürün ID.']);
            }
            $this->redirect($redirect);
        }
        
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM products WHERE id = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$productId]);
        if (!$stmt->fetch()) {
            if ($isAjax) {
                $this->json(['success' => false, 'message' => 'Ürün bulunamadı.']);
            }
            $this->redirect($redirect);
        }
        
        // Zaten favorilerde mi kontrol et
        $checkStmt = $pdo->prepare('SELECT id FROM wishlists WHERE user_id = ? AND product_id = ? LIMIT 1');
        $checkStmt->execute([$uid, $productId]);
        $alreadyExists = $checkStmt->fetch();
        
        if (!$alreadyExists) {
            $pdo->prepare('INSERT INTO wishlists (user_id, product_id, created_at) VALUES (?, ?, NOW())')->execute([$uid, $productId]);
        }
        
        // Favori sayısını hesapla
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM wishlists WHERE user_id = ?');
        $countStmt->execute([$uid]);
        $wishlistCount = (int) $countStmt->fetchColumn();
        
        if ($isAjax) {
            $this->json(['success' => true, 'message' => 'Ürün favorilere eklendi.', 'inWishlist' => true, 'count' => $wishlistCount]);
        } else {
            $this->redirect($redirect);
        }
    }

    /** Favorilerden çıkar */
    public function wishlistRemove(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $uid = $this->userId();
        $productId = (int) ($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        $baseUrl = $this->baseUrl();
        $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? $baseUrl . '/hesabim/favoriler';
        
        $pdo = Database::getConnection();
        if ($productId > 0) {
            $pdo->prepare('DELETE FROM wishlists WHERE user_id = ? AND product_id = ?')->execute([$uid, $productId]);
        }
        
        // Favori sayısını hesapla
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM wishlists WHERE user_id = ?');
        $countStmt->execute([$uid]);
        $wishlistCount = (int) $countStmt->fetchColumn();
        
        if ($isAjax) {
            $this->json(['success' => true, 'message' => 'Ürün favorilerden çıkarıldı.', 'inWishlist' => false, 'count' => $wishlistCount]);
        } else {
            $this->redirect($redirect);
        }
    }
}
