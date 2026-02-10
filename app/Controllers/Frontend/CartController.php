<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use App\Helpers\Settings;
use App\Services\CartService;
use PDO;

/**
 * Mağaza: Sepet (session tabanlı)
 */
class CartController extends FrontendBaseController
{
    /** Sepet öğeleri ve ara toplam (çekmece / layout için). */
    public static function getCartItems(): array
    {
        $result = CartService::getItems();
        return [$result['items'], $result['subtotal']];
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
        $this->render('frontend/cart/index', compact('title', 'baseUrl', 'items', 'subtotal', 'freeShippingMin', 'shippingCost', 'total'));
    }

    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek.']);
        }
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $productId = (int) ($_POST['product_id'] ?? 0);
        $variantId = isset($_POST['product_variant_id']) && $_POST['product_variant_id'] !== '' ? (int) $_POST['product_variant_id'] : null;
        $quantity = (int) ($_POST['quantity'] ?? 1);
        if ($productId < 1 || $quantity < 1) {
            $_SESSION['cart_error'] = 'Geçersiz istek.';
            if ($isAjax) {
                $this->json(['success' => false, 'message' => 'Geçersiz istek.']);
            }
            $this->redirect($_POST['redirect'] ?? '/');
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
                    $this->json(['success' => false, 'message' => 'Varyant bulunamadı.']);
                }
                $this->redirect($_POST['redirect'] ?? '/');
            }
            $maxQty = (int) $v['stock'];
        } else {
            $stmt = $pdo->prepare('SELECT id, stock FROM products WHERE id = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                $_SESSION['cart_error'] = 'Ürün bulunamadı.';
                if ($isAjax) {
                    $this->json(['success' => false, 'message' => 'Ürün bulunamadı.']);
                }
                $this->redirect($_POST['redirect'] ?? '/');
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
                $this->json(['success' => false, 'message' => 'Lütfen beden seçin.']);
            }
            $this->redirect($_POST['redirect'] ?? '/');
        }
        // Sepete ekle
        CartService::add($productId, $quantity, $variantId, $variantId === null ? $size : null);
        
        // Sepetteki toplam miktarı stok limitiyle kontrol et
        $key = CartService::cartKey($productId, $variantId, $variantId === null ? $size : null);
        if ($maxQty > 0 && isset($_SESSION['cart'][$key]) && $_SESSION['cart'][$key] > $maxQty) {
            $_SESSION['cart'][$key] = $maxQty;
        }
        
        if ($isAjax) {
            $this->json(['success' => true]);
        }
        $redirect = $_POST['redirect'] ?? '/sepet';
        $this->redirect($redirect);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/sepet');
        }
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $cartKey = trim($_POST['cart_key'] ?? '');
        $quantity = (int) ($_POST['quantity'] ?? 0);
        if ($cartKey === '') {
            if ($isAjax) {
                $this->json(['success' => false, 'message' => 'Geçersiz istek.']);
            }
            $this->redirect('/sepet');
        }
        
        CartService::update($cartKey, $quantity);
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
            $this->json([
                'success' => true,
                'quantity' => $removed ? 0 : (int) ($updatedItem['quantity'] ?? 0),
                'line_total' => $removed ? 0.0 : (float) ($updatedItem['total'] ?? 0),
                'subtotal' => (float) $subtotal,
                'shipping_cost' => (float) $shippingCost,
                'total' => (float) $total,
                'cart_count' => (int) $cartCount,
                'removed' => $removed,
            ]);
        }
        $this->redirect('/sepet');
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
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $cartKey = trim($_REQUEST['cart_key'] ?? '');
        
        if ($cartKey === '') {
            if ($isAjax) {
                $this->json(['success' => false, 'message' => 'Geçersiz istek.']);
            }
            $this->redirect('/sepet');
            return;
        }
        
        CartService::remove($cartKey);
        
        if ($isAjax) {
            [$items, $subtotal] = self::getCartItems();
            $cartCount = array_sum(array_column($items, 'quantity'));
            $freeShippingMin = Settings::get('shipping', 'free_shipping_min');
            $freeShippingMin = $freeShippingMin !== null && $freeShippingMin !== '' ? (float) str_replace(',', '.', $freeShippingMin) : 0.0;
            $shippingCost = 0.0;
            if ($freeShippingMin > 0 && $subtotal < $freeShippingMin) {
                $cost = Settings::get('shipping', 'shipping_cost');
                $shippingCost = $cost !== null && $cost !== '' ? (float) str_replace(',', '.', $cost) : 0.0;
            }
            $total = $subtotal + $shippingCost;
            $this->json([
                'success' => true,
                'message' => 'Ürün sepetten çıkarıldı.',
                'subtotal' => (float) $subtotal,
                'shipping_cost' => (float) $shippingCost,
                'total' => (float) $total,
                'cart_count' => (int) $cartCount,
            ]);
            return;
        }
        
        $this->redirect('/sepet');
    }
}
