<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use App\Helpers\Settings;
use PDO;

/**
 * Mağaza: Ödeme formu ve sipariş oluşturma
 */
class CheckoutController
{
    private function parseCartKey(string $key): array
    {
        if (strpos($key, '_v') !== false) {
            $parts = explode('_v', $key, 2);
            if (count($parts) === 2 && $parts[0] !== '' && preg_match('/^p\d+$/', $parts[0]) && is_numeric($parts[1])) {
                return ['product_id' => (int) ltrim($parts[0], 'p'), 'variant_id' => (int) $parts[1]];
            }
        }
        if (preg_match('/^p?\d+$/', $key)) {
            $id = (int) ltrim($key, 'p');
            return ['product_id' => $id, 'variant_id' => null];
        }
        return ['product_id' => 0, 'variant_id' => null];
    }

    private static function getShippingCost(float $subtotal): float
    {
        $freeMin = Settings::get('shipping', 'free_shipping_min');
        if ($freeMin !== null && $freeMin !== '' && $subtotal >= (float) $freeMin) {
            return 0.0;
        }
        $cost = Settings::get('shipping', 'shipping_cost');
        return $cost !== null && $cost !== '' ? (float) str_replace(',', '.', $cost) : 0.0;
    }

    /** Kupon doğrula; geçerliyse [coupon row, discountAmount] döner, değilse null */
    private static function validateCoupon(PDO $pdo, string $code, float $subtotal): ?array
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return null;
        }
        $stmt = $pdo->prepare('SELECT * FROM coupons WHERE code = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$code]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$coupon) {
            return null;
        }
        $now = date('Y-m-d H:i:s');
        if ($coupon['starts_at'] !== null && $coupon['starts_at'] > $now) {
            return null;
        }
        if ($coupon['ends_at'] !== null && $coupon['ends_at'] < $now) {
            return null;
        }
        $minOrder = $coupon['min_order_amount'] !== null ? (float) $coupon['min_order_amount'] : 0;
        if ($subtotal < $minOrder) {
            return null;
        }
        $maxUse = $coupon['max_use_count'];
        if ($maxUse !== null && (int) $coupon['used_count'] >= (int) $maxUse) {
            return null;
        }
        $value = (float) $coupon['value'];
        if ($coupon['type'] === 'percent') {
            $discount = round($subtotal * $value / 100, 2);
        } else {
            $discount = min($value, $subtotal);
        }
        $discount = max(0, $discount);
        return ['coupon' => $coupon, 'discount' => $discount];
    }

    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            header('Location: ' . $this->baseUrl() . '/sepet');
            exit;
        }
        $pdo = Database::getConnection();
        $items = [];
        $subtotal = 0.0;
        foreach ($cart as $key => $qty) {
            $qty = (int) $qty;
            if ($qty < 1) continue;
            $parsed = $this->parseCartKey((string) $key);
            $productId = $parsed['product_id'];
            $variantId = $parsed['variant_id'];
            if ($productId < 1) continue;
            if ($variantId !== null && $variantId > 0) {
                $stmt = $pdo->prepare('SELECT pv.id AS variant_id, pv.product_id, pv.sku, pv.stock, pv.price, pv.sale_price, p.name FROM product_variants pv INNER JOIN products p ON pv.product_id = p.id WHERE pv.id = ? AND pv.product_id = ? AND p.is_active = 1 LIMIT 1');
                $stmt->execute([$variantId, $productId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) continue;
                $stmt = $pdo->prepare('SELECT av.value FROM product_variant_attribute_values pvav INNER JOIN attribute_values av ON pvav.attribute_value_id = av.id WHERE pvav.variant_id = ? ORDER BY av.sort_order');
                $stmt->execute([$variantId]);
                $row['attributes_summary'] = implode(', ', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'value'));
            } else {
                $stmt = $pdo->prepare('SELECT id AS product_id, name, sku, price, sale_price, stock FROM products WHERE id = ? AND is_active = 1 LIMIT 1');
                $stmt->execute([$productId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) continue;
                $row['variant_id'] = null;
                $row['attributes_summary'] = null;
            }
            $price = $row['sale_price'] !== null && (float) $row['sale_price'] > 0 ? (float) $row['sale_price'] : (float) $row['price'];
            $stock = (int) ($row['stock'] ?? 0);
            $row['quantity'] = $stock > 0 ? min($qty, $stock) : $qty;
            $row['price'] = $price;
            $row['total'] = $row['price'] * $row['quantity'];
            $row['id'] = (int) $row['product_id'];
            $subtotal += $row['total'];
            $items[] = $row;
        }
        if (empty($items)) {
            header('Location: ' . $this->baseUrl() . '/sepet');
            exit;
        }
        $shippingCost = self::getShippingCost($subtotal);
        $errors = $_SESSION['checkout_errors'] ?? [];
        $old = $_SESSION['checkout_old'] ?? [];
        unset($_SESSION['checkout_errors'], $_SESSION['checkout_old']);
        $couponCode = trim($old['coupon_code'] ?? '');
        $discountAmount = 0.0;
        $appliedCoupon = null;
        if ($couponCode !== '') {
            $valid = self::validateCoupon($pdo, $couponCode, $subtotal);
            if ($valid !== null) {
                $discountAmount = $valid['discount'];
                $c = $valid['coupon'];
                $appliedCoupon = [
                    'code' => $c['code'],
                    'label' => $c['type'] === 'percent' ? '%' . (float)$c['value'] . ' indirim' : number_format((float)$c['value'], 2, ',', '.') . ' ₺ indirim',
                ];
            }
        }
        $total = $subtotal + $shippingCost - $discountAmount;
        $total = max(0, $total);
        $paymentSettings = [
            'cod_enabled' => Settings::get('payment', 'cod_enabled', '1') === '1',
            'bank_transfer_enabled' => Settings::get('payment', 'bank_transfer_enabled', '1') === '1',
            'bank_name' => Settings::get('payment', 'bank_name'),
            'bank_iban' => Settings::get('payment', 'bank_iban'),
            'bank_account_name' => Settings::get('payment', 'bank_account_name'),
        ];
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $userAddresses = [];
        $userEmail = '';
        $userName = '';
        $userPhone = '';
        if ($userId > 0) {
            $stmt = $pdo->prepare('SELECT id, title, first_name, last_name, phone, city, district, address_line, postal_code FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id ASC');
            $stmt->execute([$userId]);
            $userAddresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = $pdo->prepare('SELECT email, first_name, last_name, phone FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($u) {
                $userEmail = $u['email'] ?? '';
                $userName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                $userPhone = $u['phone'] ?? '';
            }
        }
        $title = 'Ödeme - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $this->render('frontend/checkout/form', compact('title', 'baseUrl', 'items', 'subtotal', 'shippingCost', 'discountAmount', 'appliedCoupon', 'total', 'errors', 'old', 'paymentSettings', 'userAddresses', 'userId', 'userEmail', 'userName', 'userPhone'));
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->baseUrl() . '/odeme');
            exit;
        }
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            header('Location: ' . $this->baseUrl() . '/sepet');
            exit;
        }
        $baseUrl = $this->baseUrl();
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $addressId = isset($_POST['address_id']) && $_POST['address_id'] !== '' ? (int) $_POST['address_id'] : null;
        $firstName = trim($_POST['shipping_first_name'] ?? '');
        $lastName = trim($_POST['shipping_last_name'] ?? '');
        $phone = trim($_POST['shipping_phone'] ?? '');
        $city = trim($_POST['shipping_city'] ?? '');
        $district = trim($_POST['shipping_district'] ?? '');
        $addressLine = trim($_POST['shipping_address_line'] ?? '');
        $email = trim($_POST['guest_email'] ?? '');
        $paymentMethod = $_POST['payment_method'] ?? 'cod';
        if (!in_array($paymentMethod, ['cod', 'bank_transfer', 'stripe'], true)) {
            $paymentMethod = 'cod';
        }
        $pdo = Database::getConnection();
        $addr = null;
        if ($userId > 0 && $addressId > 0) {
            $stmt = $pdo->prepare('SELECT * FROM addresses WHERE id = ? AND user_id = ? LIMIT 1');
            $stmt->execute([$addressId, $userId]);
            $addr = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if ($addr) {
                $firstName = trim($addr['first_name'] ?? '');
                $lastName = trim($addr['last_name'] ?? '');
                $phone = trim($addr['phone'] ?? '');
                $city = trim($addr['city'] ?? '');
                $district = trim($addr['district'] ?? '');
                $addressLine = trim($addr['address_line'] ?? '');
                $postalCode = trim($addr['postal_code'] ?? '');
                $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$userId]);
                $u = $stmt->fetch(PDO::FETCH_ASSOC);
                $email = $u ? trim($u['email'] ?? '') : $email;
        }
        $postalCode = $addr ? trim($addr['postal_code'] ?? '') : trim($_POST['shipping_postal_code'] ?? '');
        $errors = [];
        if ($firstName === '') $errors['shipping_first_name'] = 'Ad zorunludur.';
        if ($lastName === '') $errors['shipping_last_name'] = 'Soyad zorunludur.';
        if ($phone === '') $errors['shipping_phone'] = 'Telefon zorunludur.';
        if ($city === '') $errors['shipping_city'] = 'İl zorunludur.';
        if ($district === '') $errors['shipping_district'] = 'İlçe zorunludur.';
        if ($addressLine === '') $errors['shipping_address_line'] = 'Adres zorunludur.';
        if ($email === '') $errors['guest_email'] = 'E-posta zorunludur.';
        if (!empty($errors)) {
            $_SESSION['checkout_errors'] = $errors;
            $_SESSION['checkout_old'] = $_POST;
            header('Location: ' . $baseUrl . '/odeme');
            exit;
        }
        $pdo = Database::getConnection();
        $orderItems = [];
        $subtotal = 0.0;
        foreach ($cart as $key => $qty) {
            $qty = (int) $qty;
            if ($qty < 1) continue;
            $parsed = $this->parseCartKey((string) $key);
            $productId = $parsed['product_id'];
            $variantId = $parsed['variant_id'];
            if ($productId < 1) continue;
            if ($variantId !== null && $variantId > 0) {
                $stmt = $pdo->prepare('SELECT pv.id AS variant_id, pv.product_id, pv.sku, pv.stock, pv.price, pv.sale_price, p.name FROM product_variants pv INNER JOIN products p ON pv.product_id = p.id WHERE pv.id = ? AND pv.product_id = ? AND p.is_active = 1 LIMIT 1');
                $stmt->execute([$variantId, $productId]);
                $p = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$p) continue;
                $stmt = $pdo->prepare('SELECT av.value FROM product_variant_attribute_values pvav INNER JOIN attribute_values av ON pvav.attribute_value_id = av.id WHERE pvav.variant_id = ? ORDER BY av.sort_order');
                $stmt->execute([$variantId]);
                $attributesSummary = implode(', ', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'value'));
            } else {
                $stmt = $pdo->prepare('SELECT id, name, sku, price, sale_price, stock FROM products WHERE id = ? AND is_active = 1 LIMIT 1');
                $stmt->execute([$productId]);
                $p = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$p) continue;
                $p['variant_id'] = null;
                $attributesSummary = null;
            }
            $price = $p['sale_price'] !== null && (float) $p['sale_price'] > 0 ? (float) $p['sale_price'] : (float) $p['price'];
            $stock = (int) ($p['stock'] ?? 0);
            $qty = min($qty, $stock > 0 ? $stock : $qty);
            if ($qty < 1) continue;
            $total = $price * $qty;
            $subtotal += $total;
            $orderItems[] = [
                'product_id' => (int) $p['product_id'] ?? (int) $p['id'],
                'product_variant_id' => isset($p['variant_id']) && $p['variant_id'] !== null ? (int) $p['variant_id'] : null,
                'product_name' => $p['name'],
                'product_sku' => $p['sku'] ?? '',
                'attributes_summary' => $attributesSummary,
                'quantity' => $qty,
                'price' => $price,
                'total' => $total,
            ];
        }
        if (empty($orderItems)) {
            $_SESSION['checkout_errors'] = ['Sepette geçerli ürün kalmadı.'];
            header('Location: ' . $baseUrl . '/sepet');
            exit;
        }
        $shippingCost = self::getShippingCost($subtotal);
        $couponCode = trim($_POST['coupon_code'] ?? '');
        $couponId = null;
        $discountAmount = 0.0;
        if ($couponCode !== '') {
            $valid = self::validateCoupon($pdo, $couponCode, $subtotal);
            if ($valid !== null) {
                $couponId = (int) $valid['coupon']['id'];
                $discountAmount = $valid['discount'];
            }
        }
        $total = $subtotal + $shippingCost - $discountAmount;
        $total = max(0, round($total, 2));
        $orderNumber = $this->generateOrderNumber($pdo);
        $notes = trim($_POST['customer_notes'] ?? '');
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('
                INSERT INTO orders (user_id, order_number, status, payment_method, payment_status, subtotal, shipping_cost, discount_amount, total, coupon_id,
                    guest_email, guest_first_name, guest_last_name, guest_phone,
                    shipping_first_name, shipping_last_name, shipping_phone, shipping_city, shipping_district, shipping_address_line, shipping_postal_code,
                    billing_same_as_shipping, billing_first_name, billing_last_name, billing_phone, billing_city, billing_district, billing_address_line, billing_postal_code,
                    customer_notes, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?,
                    1, ?, ?, ?, ?, ?, ?, ?,
                    ?, NOW(), NOW())
            ');
            $orderUserId = $userId > 0 ? $userId : null;
            $stmt->execute([
                $orderUserId,
                $orderNumber, 'pending', $paymentMethod, 'pending', $subtotal, $shippingCost, $discountAmount, $total, $couponId,
                $email, $firstName, $lastName, $phone,
                $firstName, $lastName, $phone, $city, $district, $addressLine, $postalCode,
                $firstName, $lastName, $phone, $city, $district, $addressLine, $postalCode,
                $notes ?: null,
            ]);
            $orderId = (int) $pdo->lastInsertId();
            $orderItemsStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_variant_id, product_name, product_sku, attributes_summary, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            foreach ($orderItems as $item) {
                $orderItemsStmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['product_variant_id'] ?? null,
                    $item['product_name'],
                    $item['product_sku'],
                    $item['attributes_summary'] ?? null,
                    $item['quantity'],
                    $item['price'],
                    $item['total'],
                ]);
                if (!empty($item['product_variant_id'])) {
                    $pdo->prepare('UPDATE product_variants SET stock = stock - ? WHERE id = ?')->execute([$item['quantity'], $item['product_variant_id']]);
                } else {
                    $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?')->execute([$item['quantity'], $item['product_id']]);
                }
            }
            $pdo->prepare('INSERT INTO order_status_history (order_id, status, created_at) VALUES (?, ?, NOW())')->execute([$orderId, 'pending']);
            if ($couponId !== null) {
                $pdo->prepare('UPDATE coupons SET used_count = used_count + 1, updated_at = NOW() WHERE id = ?')->execute([$couponId]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            error_log('Checkout order error: ' . $e->getMessage());
            $_SESSION['checkout_errors'] = ['Sipariş oluşturulurken hata oluştu.'];
            $_SESSION['checkout_old'] = $_POST;
            header('Location: ' . $baseUrl . '/odeme');
            exit;
        }
        $_SESSION['cart'] = [];
        $_SESSION['order_success'] = $orderNumber;
        header('Location: ' . $baseUrl . '/odeme/tamamlandi');
        exit;
    }

    public function success(): void
    {
        $orderNumber = $_SESSION['order_success'] ?? null;
        unset($_SESSION['order_success']);
        $title = 'Siparişiniz alındı - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $this->render('frontend/checkout/success', compact('title', 'baseUrl', 'orderNumber'));
    }

    private function generateOrderNumber(PDO $pdo): string
    {
        $prefix = 'LB-' . date('Ymd') . '-';
        for ($i = 0; $i < 20; $i++) {
            $num = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $orderNumber = $prefix . $num;
            $stmt = $pdo->prepare('SELECT id FROM orders WHERE order_number = ? LIMIT 1');
            $stmt->execute([$orderNumber]);
            if (!$stmt->fetch()) {
                return $orderNumber;
            }
        }
        return $prefix . uniqid();
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
}
