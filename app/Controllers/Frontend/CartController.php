<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use App\Helpers\Settings;
use PDO;

/**
 * Mağaza: Sepet (session tabanlı)
 */
class CartController
{
    private static function parseCartKey(string $key): array
    {
        if (strpos($key, '_v') !== false) {
            $parts = explode('_v', $key, 2);
            if (count($parts) === 2 && $parts[0] !== '' && preg_match('/^p\d+$/', $parts[0]) && is_numeric($parts[1])) {
                return ['product_id' => (int) ltrim($parts[0], 'p'), 'variant_id' => (int) $parts[1], 'size' => null];
            }
        }
        if (preg_match('/^p(\d+)_s_(.+)$/', $key, $m)) {
            return ['product_id' => (int) $m[1], 'variant_id' => null, 'size' => $m[2]];
        }
        if (preg_match('/^p?\d+$/', $key)) {
            $id = (int) ltrim($key, 'p');
            return ['product_id' => $id, 'variant_id' => null, 'size' => null];
        }
        return ['product_id' => 0, 'variant_id' => null, 'size' => null];
    }

    private static function cartKey(int $productId, ?int $variantId, ?string $size = null): string
    {
        if ($variantId !== null && $variantId > 0) {
            return 'p' . $productId . '_v' . $variantId;
        }
        if ($size !== null && $size !== '') {
            return 'p' . $productId . '_s_' . $size;
        }
        return 'p' . $productId;
    }

    /** Sepet öğeleri ve ara toplam (çekmece / layout için). */
    public static function getCartItems(): array
    {
        $cart = $_SESSION['cart'] ?? [];
        $normalized = [];
        foreach ($cart as $key => $qty) {
            $parsed = self::parseCartKey(is_int($key) ? (string) $key : (string) $key);
            if ($parsed['product_id'] < 1) continue;
            $k = self::cartKey($parsed['product_id'], $parsed['variant_id'], $parsed['size'] ?? null);
            $normalized[$k] = ((int) ($normalized[$k] ?? 0)) + (int) $qty;
        }
        $_SESSION['cart'] = $normalized;
        $cart = $normalized;
        $items = [];
        $subtotal = 0.0;
        if (!empty($cart)) {
            $pdo = Database::getConnection();
            foreach ($cart as $key => $qty) {
                $qty = (int) $qty;
                if ($qty < 1) continue;
                $parsed = self::parseCartKey((string) $key);
                $productId = $parsed['product_id'];
                $variantId = $parsed['variant_id'];
                if ($productId < 1) continue;
                if ($variantId !== null && $variantId > 0) {
                    $stmt = $pdo->prepare('
                        SELECT pv.id AS variant_id, pv.product_id, pv.sku, pv.stock, pv.price, pv.sale_price,
                               p.name, p.slug
                        FROM product_variants pv
                        INNER JOIN products p ON pv.product_id = p.id
                        WHERE pv.id = ? AND pv.product_id = ? AND p.is_active = 1 LIMIT 1
                    ');
                    $stmt->execute([$variantId, $productId]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$row) continue;
                    $stmt = $pdo->prepare('
                        SELECT av.value FROM product_variant_attribute_values pvav
                        INNER JOIN attribute_values av ON pvav.attribute_value_id = av.id
                        WHERE pvav.variant_id = ? ORDER BY av.sort_order
                    ');
                    $stmt->execute([$variantId]);
                    $row['attributes_summary'] = implode(', ', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'value'));
                    $row['id'] = (int) $row['product_id'];
                } else {
                    $stmt = $pdo->prepare('SELECT id, name, slug, price, sale_price, stock FROM products WHERE id = ? AND is_active = 1 LIMIT 1');
                    $stmt->execute([$productId]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$row) continue;
                    $row['variant_id'] = null;
                    $row['attributes_summary'] = !empty($parsed['size']) ? 'Beden: ' . $parsed['size'] : null;
                }
                $price = $row['sale_price'] !== null && (float) $row['sale_price'] > 0 ? (float) $row['sale_price'] : (float) $row['price'];
                $stock = (int) ($row['stock'] ?? 0);
                $row['quantity'] = $stock > 0 ? min($qty, $stock) : $qty;
                $row['price'] = $price;
                $row['total'] = $row['price'] * $row['quantity'];
                $row['cart_key'] = self::cartKey($productId, $variantId ?? null, $parsed['size'] ?? null);
                $row['product_sku'] = $row['sku'] ?? '';
                unset($row['sku'], $row['sale_price']);
                $subtotal += $row['total'];
                $pid = (int) $row['id'];
                $stmtImg = $pdo->prepare('SELECT path FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1');
                $stmtImg->execute([$pid]);
                $row['image_path'] = $stmtImg->fetchColumn() ?: null;
                $items[] = $row;
            }
        }
        return [$items, $subtotal];
    }

    public function index(): void
    {
        [$items, $subtotal] = self::getCartItems();
        $freeShippingMin = Settings::get('shipping', 'free_shipping_min');
        $freeShippingMin = $freeShippingMin !== null && $freeShippingMin !== '' ? (float) str_replace(',', '.', $freeShippingMin) : 0.0;
        $shippingCost = 0.0;
        if ($freeShippingMin > 0 && $subtotal < $freeShippingMin) {
            $cost = Settings::get('shipping', 'shipping_cost');
            $shippingCost = $cost !== null && $cost !== '' ? (float) str_replace(',', '.', $cost) : 0.0;
        }
        $total = $subtotal + $shippingCost;
        $title = 'Sepetim - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $this->renderWithIncludesLayout('frontend/cart/index', compact('title', 'baseUrl', 'items', 'subtotal', 'freeShippingMin', 'shippingCost', 'total'));
    }

    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->ajaxResponse(['success' => false, 'message' => 'Geçersiz istek.']);
            return;
        }
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $productId = (int) ($_POST['product_id'] ?? 0);
        $variantId = isset($_POST['product_variant_id']) && $_POST['product_variant_id'] !== '' ? (int) $_POST['product_variant_id'] : null;
        $quantity = (int) ($_POST['quantity'] ?? 1);
        if ($productId < 1 || $quantity < 1) {
            $_SESSION['cart_error'] = 'Geçersiz istek.';
            if ($isAjax) {
                $this->ajaxResponse(['success' => false, 'message' => 'Geçersiz istek.']);
                return;
            }
            header('Location: ' . ($_POST['redirect'] ?? $this->baseUrl() . '/'));
            exit;
        }
        $pdo = Database::getConnection();
        $maxQty = 0;
        if ($variantId !== null && $variantId > 0) {
            $stmt = $pdo->prepare('SELECT pv.id, pv.product_id, pv.stock FROM product_variants pv INNER JOIN products p ON pv.product_id = p.id WHERE pv.id = ? AND pv.product_id = ? AND p.is_active = 1 LIMIT 1');
            $stmt->execute([$variantId, $productId]);
            $v = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$v) {
                $_SESSION['cart_error'] = 'Varyant bulunamadı.';
                if ($isAjax) {
                    $this->ajaxResponse(['success' => false, 'message' => 'Varyant bulunamadı.']);
                    return;
                }
                header('Location: ' . ($_POST['redirect'] ?? $this->baseUrl() . '/'));
                exit;
            }
            $maxQty = (int) $v['stock'];
        } else {
            $stmt = $pdo->prepare('SELECT id, stock FROM products WHERE id = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                $_SESSION['cart_error'] = 'Ürün bulunamadı.';
                if ($isAjax) {
                    $this->ajaxResponse(['success' => false, 'message' => 'Ürün bulunamadı.']);
                    return;
                }
                header('Location: ' . ($_POST['redirect'] ?? $this->baseUrl() . '/'));
                exit;
            }
            $maxQty = (int) $product['stock'];
        }
        if ($maxQty > 0 && $quantity > $maxQty) {
            $quantity = $maxQty;
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $size = isset($_POST['size']) ? trim((string) $_POST['size']) : null;
        if ($variantId === null && ($size === null || $size === '')) {
            $_SESSION['cart_error'] = 'Lütfen beden seçin.';
            if ($isAjax) {
                $this->ajaxResponse(['success' => false, 'message' => 'Lütfen beden seçin.']);
                return;
            }
            header('Location: ' . ($_POST['redirect'] ?? $this->baseUrl() . '/'));
            exit;
        }
        $key = self::cartKey($productId, $variantId, $variantId === null ? $size : null);
        $_SESSION['cart'][$key] = ((int) ($_SESSION['cart'][$key] ?? 0)) + $quantity;
        if ($maxQty > 0 && $_SESSION['cart'][$key] > $maxQty) {
            $_SESSION['cart'][$key] = $maxQty;
        }
        if ($isAjax) {
            $this->ajaxResponse(['success' => true]);
            return;
        }
        $redirect = $_POST['redirect'] ?? $this->baseUrl() . '/sepet';
        header('Location: ' . $redirect);
        exit;
    }

    private function ajaxResponse(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->baseUrl() . '/sepet');
            exit;
        }
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $cartKey = trim($_POST['cart_key'] ?? '');
        $quantity = (int) ($_POST['quantity'] ?? 0);
        if ($cartKey === '') {
            if ($isAjax) {
                $this->ajaxResponse(['success' => false, 'message' => 'Geçersiz istek.']);
                return;
            }
            header('Location: ' . $this->baseUrl() . '/sepet');
            exit;
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if ($quantity < 1) {
            unset($_SESSION['cart'][$cartKey]);
        } else {
            $parsed = self::parseCartKey($cartKey);
            $productId = $parsed['product_id'];
            $variantId = $parsed['variant_id'];
            $pdo = Database::getConnection();
            if ($variantId !== null && $variantId > 0) {
                $stmt = $pdo->prepare('SELECT stock FROM product_variants WHERE id = ? AND product_id = ? LIMIT 1');
                $stmt->execute([$variantId, $productId]);
                $stock = (int) ($stmt->fetchColumn() ?: 0);
            } else {
                $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ? LIMIT 1');
                $stmt->execute([$productId]);
                $stock = (int) ($stmt->fetchColumn() ?: 0);
            }
            $_SESSION['cart'][$cartKey] = $stock > 0 ? min($quantity, $stock) : $quantity;
        }
        if ($isAjax) {
            [$items, $subtotal] = self::getCartItems();
            $cartCount = array_sum(array_column($items, 'quantity'));
            $updatedItem = null;
            foreach ($items as $item) {
                if (($item['cart_key'] ?? '') === $cartKey) {
                    $updatedItem = $item;
                    break;
                }
            }
            $removed = $quantity < 1;
            $freeShippingMin = Settings::get('shipping', 'free_shipping_min');
            $freeShippingMin = $freeShippingMin !== null && $freeShippingMin !== '' ? (float) str_replace(',', '.', $freeShippingMin) : 0.0;
            $shippingCost = 0.0;
            if ($freeShippingMin > 0 && $subtotal < $freeShippingMin) {
                $cost = Settings::get('shipping', 'shipping_cost');
                $shippingCost = $cost !== null && $cost !== '' ? (float) str_replace(',', '.', $cost) : 0.0;
            }
            $total = $subtotal + $shippingCost;
            $this->ajaxResponse([
                'success' => true,
                'quantity' => $removed ? 0 : (int) ($updatedItem['quantity'] ?? 0),
                'line_total' => $removed ? 0.0 : (float) ($updatedItem['total'] ?? 0),
                'subtotal' => (float) $subtotal,
                'shipping_cost' => (float) $shippingCost,
                'total' => (float) $total,
                'cart_count' => (int) $cartCount,
                'removed' => $removed,
            ]);
            return;
        }
        header('Location: ' . $this->baseUrl() . '/sepet');
        exit;
    }

    /** AJAX: Çekmece için güncel sepet verisi (items, subtotal, cart_count). */
    public function drawerData(): void
    {
        [$items, $subtotal] = self::getCartItems();
        $cartCount = 0;
        foreach ($items as $item) {
            $cartCount += (int) ($item['quantity'] ?? 0);
        }
        $baseUrl = $this->baseUrl();
        header('Content-Type: application/json; charset=utf-8');
        header('X-Requested-With: XMLHttpRequest');
        echo json_encode([
            'baseUrl' => $baseUrl,
            'items' => $items,
            'subtotal' => (float) $subtotal,
            'cart_count' => (int) $cartCount,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function remove(): void
    {
        $cartKey = trim($_REQUEST['cart_key'] ?? '');
        if ($cartKey !== '' && isset($_SESSION['cart'][$cartKey])) {
            unset($_SESSION['cart'][$cartKey]);
        }
        header('Location: ' . $this->baseUrl() . '/sepet');
        exit;
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

    /** Sepet sayfası: includes/layout.php kullanır (header, footer, cart-drawer, toast). */
    private function renderWithIncludesLayout(string $view, array $data = []): void
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
        $layoutPath = BASE_PATH . '/includes/layout.php';
        require $layoutPath;
    }
}
