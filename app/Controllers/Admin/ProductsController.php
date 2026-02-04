<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;
use PDO;

/**
 * Ürün yönetimi – liste, ekleme, düzenleme, ürün görseli
 */
class ProductsController extends AdminBaseController
{
    private const UPLOAD_DIR = 'uploads/products';
    private const MAX_IMAGE_SIZE = 2 * 1024 * 1024; // 2 MB
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    private function handleProductImage(int $productId, string $productName): void
    {
        if (empty($_FILES['image']['tmp_name']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
            return;
        }
        $file = $_FILES['image'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, self::ALLOWED_TYPES, true) || $file['size'] > self::MAX_IMAGE_SIZE) {
            return;
        }
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        $dir = BASE_PATH . '/public/' . self::UPLOAD_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = $productId . '_' . preg_replace('/[^a-z0-9]/', '', (string) microtime(true)) . '.' . $ext;
        $path = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $path)) {
            return;
        }
        $relativePath = self::UPLOAD_DIR . '/' . $filename;
        $pdo = Database::getConnection();
        $pdo->prepare('INSERT INTO product_images (product_id, path, alt, sort_order, created_at) VALUES (?, ?, ?, 0, NOW())')
            ->execute([$productId, $relativePath, $productName]);
    }
    public function index(): void
    {
        $pdo = Database::getConnection();
        $search = trim($_GET['q'] ?? $_GET['search'] ?? '');
        $categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int) $_GET['category_id'] : null;
        $stockFilter = trim($_GET['stock'] ?? '');

        $sql = 'SELECT p.id, p.name, p.sku, p.price, p.sale_price, p.stock, p.is_active, p.is_featured, p.is_new, p.created_at, p.low_stock_threshold,
                       c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ?)';
            $term = '%' . $search . '%';
            $params[] = $term;
            $params[] = $term;
        }
        if ($categoryId !== null && $categoryId > 0) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        if ($stockFilter === 'out_of_stock') {
            $sql .= ' AND p.stock <= 0';
        } elseif ($stockFilter === 'low_stock') {
            $sql .= ' AND p.stock > 0 AND p.stock <= COALESCE(NULLIF(p.low_stock_threshold, 0), 5)';
        } elseif ($stockFilter === 'in_stock') {
            $sql .= ' AND p.stock > COALESCE(NULLIF(p.low_stock_threshold, 0), 5)';
        }

        $sql .= ' ORDER BY p.created_at DESC, p.name ASC LIMIT 500';
        $stmt = $params === [] ? $pdo->query($sql) : $pdo->prepare($sql);
        if ($params !== []) {
            $stmt->execute($params);
        }
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        $baseUrl = $this->baseUrl();
        $this->render('admin/products/index', [
            'pageTitle' => 'Ürünler',
            'baseUrl' => $baseUrl,
            'products' => $products,
            'categories' => $categories,
            'filterQ' => $search,
            'filterCategoryId' => $categoryId,
            'filterStock' => $stockFilter,
        ]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }
        $pdo = Database::getConnection();
        $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        $baseUrl = $this->baseUrl();
        $errors = $_SESSION['product_errors'] ?? [];
        $old = $_SESSION['product_old'] ?? [];
        unset($_SESSION['product_errors'], $_SESSION['product_old']);
        $this->render('admin/products/create', [
            'pageTitle' => 'Yeni ürün',
            'baseUrl' => $baseUrl,
            'categories' => $categories,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $categoryId = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
        $description = trim($_POST['description'] ?? '');
        $shortDescription = trim($_POST['short_description'] ?? '');
        $price = (float) str_replace(',', '.', $_POST['price'] ?? '0');
        $salePrice = trim($_POST['sale_price'] ?? '');
        $salePrice = $salePrice !== '' ? (float) str_replace(',', '.', $salePrice) : null;
        $sku = trim($_POST['sku'] ?? '');
        $stock = (int) ($_POST['stock'] ?? 0);
        $lowStockThreshold = (int) ($_POST['low_stock_threshold'] ?? 5);
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isNew = isset($_POST['is_new']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Ürün adı zorunludur.';
        }
        if ($price < 0) {
            $errors['price'] = 'Fiyat 0 veya üzeri olmalıdır.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            $_SESSION['product_errors'] = $errors;
            $_SESSION['product_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/products/create');
            exit;
        }

        $slug = $this->slugify($name);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM products WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug = $slug . '-' . time();
        }
        $stmt = $pdo->prepare('
            INSERT INTO products (category_id, name, slug, description, short_description, price, sale_price, sku, stock, low_stock_threshold, is_featured, is_new, is_active, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ');
        $stmt->execute([$categoryId, $name, $slug, $description ?: null, $shortDescription ?: null, $price, $salePrice, $sku ?: null, $stock, $lowStockThreshold, $isFeatured, $isNew, $isActive, $sortOrder]);
        $productId = (int) $pdo->lastInsertId();
        $this->handleProductImage($productId, $name);
        header('Location: ' . $baseUrl . '/admin/products');
        exit;
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id < 1) {
            header('Location: ' . $this->baseUrl() . '/admin/products');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            header('Location: ' . $this->baseUrl() . '/admin/products');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
            return;
        }
        $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare('SELECT id, path, alt, sort_order FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC');
        $stmt->execute([$id]);
        $productImages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $variants = $pdo->prepare('
            SELECT pv.id, pv.sku, pv.stock, pv.price, pv.sale_price
            FROM product_variants pv
            WHERE pv.product_id = ?
            ORDER BY pv.sort_order ASC, pv.id ASC
        ');
        $variants->execute([$id]);
        $productVariants = $variants->fetchAll(PDO::FETCH_ASSOC);
        foreach ($productVariants as &$v) {
            $stmt = $pdo->prepare('
                SELECT av.value FROM product_variant_attribute_values pvav
                INNER JOIN attribute_values av ON pvav.attribute_value_id = av.id
                WHERE pvav.variant_id = ?
                ORDER BY av.sort_order ASC
            ');
            $stmt->execute([$v['id']]);
            $v['attributes_summary'] = implode(', ', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'value'));
        }
        unset($v);

        $attributesForVariant = $pdo->query('
            SELECT a.id, a.name, a.type
            FROM attributes a
            ORDER BY a.sort_order ASC, a.name ASC
        ')->fetchAll(PDO::FETCH_ASSOC);
        $attributeValuesByAttr = [];
        foreach ($attributesForVariant as $a) {
            $stmt = $pdo->prepare('SELECT id, value, color_hex FROM attribute_values WHERE attribute_id = ? ORDER BY sort_order ASC, value ASC');
            $stmt->execute([$a['id']]);
            $attributeValuesByAttr[$a['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $baseUrl = $this->baseUrl();
        $errors = $_SESSION['product_errors'] ?? [];
        $old = $_SESSION['product_old'] ?? [];
        unset($_SESSION['product_errors'], $_SESSION['product_old']);
        $this->render('admin/products/edit', [
            'pageTitle' => 'Ürün düzenle',
            'baseUrl' => $baseUrl,
            'product' => $product,
            'categories' => $categories,
            'productImages' => $productImages,
            'productVariants' => $productVariants,
            'attributesForVariant' => $attributesForVariant,
            'attributeValuesByAttr' => $attributeValuesByAttr,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function update(int $id): void
    {
        $name = trim($_POST['name'] ?? '');
        $categoryId = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
        $description = trim($_POST['description'] ?? '');
        $shortDescription = trim($_POST['short_description'] ?? '');
        $price = (float) str_replace(',', '.', $_POST['price'] ?? '0');
        $salePrice = trim($_POST['sale_price'] ?? '');
        $salePrice = $salePrice !== '' ? (float) str_replace(',', '.', $salePrice) : null;
        $sku = trim($_POST['sku'] ?? '');
        $stock = (int) ($_POST['stock'] ?? 0);
        $lowStockThreshold = (int) ($_POST['low_stock_threshold'] ?? 5);
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isNew = isset($_POST['is_new']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Ürün adı zorunludur.';
        }
        if ($price < 0) {
            $errors['price'] = 'Fiyat 0 veya üzeri olmalıdır.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            $_SESSION['product_errors'] = $errors;
            $_SESSION['product_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/products/edit?id=' . $id);
            exit;
        }

        $slug = $this->slugify($name);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM products WHERE slug = ? AND id != ? LIMIT 1');
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $slug = $slug . '-' . $id;
        }
        $stmt = $pdo->prepare('
            UPDATE products SET category_id = ?, name = ?, slug = ?, description = ?, short_description = ?, price = ?, sale_price = ?, sku = ?, stock = ?, low_stock_threshold = ?, is_featured = ?, is_new = ?, is_active = ?, sort_order = ?, updated_at = NOW()
            WHERE id = ?
        ');
        $stmt->execute([$categoryId, $name, $slug, $description ?: null, $shortDescription ?: null, $price, $salePrice, $sku ?: null, $stock, $lowStockThreshold, $isFeatured, $isNew, $isActive, $sortOrder, $id]);
        $this->handleProductImage($id, $name);
        header('Location: ' . $baseUrl . '/admin/products');
        exit;
    }

    /** Varyant ekle: product_variants + product_variant_attribute_values */
    public function addVariant(): void
    {
        $baseUrl = $this->baseUrl();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $baseUrl . '/admin/products');
            exit;
        }
        $productId = (int) ($_POST['product_id'] ?? 0);
        if ($productId < 1) {
            header('Location: ' . $baseUrl . '/admin/products');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM products WHERE id = ? LIMIT 1');
        $stmt->execute([$productId]);
        if (!$stmt->fetch()) {
            header('Location: ' . $baseUrl . '/admin/products');
            exit;
        }
        $attributeValueIds = isset($_POST['attribute_value_id']) && is_array($_POST['attribute_value_id'])
            ? array_map('intval', array_filter($_POST['attribute_value_id']))
            : [];
        $sku = trim($_POST['variant_sku'] ?? '');
        $stock = (int) ($_POST['variant_stock'] ?? 0);
        $price = trim($_POST['variant_price'] ?? '');
        $price = $price !== '' ? (float) str_replace(',', '.', $price) : null;
        $salePrice = trim($_POST['variant_sale_price'] ?? '');
        $salePrice = $salePrice !== '' ? (float) str_replace(',', '.', $salePrice) : null;

        if ($sku === '') {
            $_SESSION['product_errors'] = ['variant' => 'Varyant SKU zorunludur.'];
            header('Location: ' . $baseUrl . '/admin/products/edit?id=' . $productId);
            exit;
        }
        $stmt = $pdo->prepare('SELECT id FROM product_variants WHERE sku = ? LIMIT 1');
        $stmt->execute([$sku]);
        if ($stmt->fetch()) {
            $_SESSION['product_errors'] = ['variant' => 'Bu SKU zaten kullanılıyor.'];
            header('Location: ' . $baseUrl . '/admin/products/edit?id=' . $productId);
            exit;
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('
                INSERT INTO product_variants (product_id, sku, stock, price, sale_price, sort_order, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 0, NOW(), NOW())
            ');
            $stmt->execute([$productId, $sku, $stock, $price, $salePrice]);
            $variantId = (int) $pdo->lastInsertId();
            foreach ($attributeValueIds as $avId) {
                if ($avId < 1) continue;
                $pdo->prepare('INSERT INTO product_variant_attribute_values (variant_id, attribute_value_id) VALUES (?, ?)')
                    ->execute([$variantId, $avId]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            $_SESSION['product_errors'] = ['variant' => 'Varyant eklenirken hata oluştu.'];
        }
        header('Location: ' . $baseUrl . '/admin/products/edit?id=' . $productId . '#variants');
        exit;
    }

    /** Varyant sil */
    public function deleteVariant(): void
    {
        $baseUrl = $this->baseUrl();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $baseUrl . '/admin/products');
            exit;
        }
        $variantId = (int) ($_POST['variant_id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);
        if ($variantId < 1 || $productId < 1) {
            header('Location: ' . $baseUrl . '/admin/products/edit?id=' . $productId);
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM product_variants WHERE id = ? AND product_id = ? LIMIT 1');
        $stmt->execute([$variantId, $productId]);
        if (!$stmt->fetch()) {
            header('Location: ' . $baseUrl . '/admin/products/edit?id=' . $productId);
            exit;
        }
        $pdo->prepare('DELETE FROM product_variant_attribute_values WHERE variant_id = ?')->execute([$variantId]);
        $pdo->prepare('DELETE FROM product_variants WHERE id = ?')->execute([$variantId]);
        header('Location: ' . $baseUrl . '/admin/products/edit?id=' . $productId . '&variant_removed=1#variants');
        exit;
    }

    /** Ürün görseli kaldır (product_images + dosya) */
    public function deleteImage(): void
    {
        $baseUrl = $this->baseUrl();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $baseUrl . '/admin/products');
            exit;
        }
        $imageId = isset($_POST['image_id']) ? (int) $_POST['image_id'] : 0;
        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        if ($imageId < 1 || $productId < 1) {
            header('Location: ' . $baseUrl . '/admin/products/edit?id=' . $productId);
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, product_id, path FROM product_images WHERE id = ? AND product_id = ? LIMIT 1');
        $stmt->execute([$imageId, $productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            header('Location: ' . $baseUrl . '/admin/products/edit?id=' . $productId);
            exit;
        }
        $pdo->prepare('DELETE FROM product_images WHERE id = ?')->execute([$imageId]);
        $filePath = BASE_PATH . '/public/' . $row['path'];
        if (is_file($filePath)) {
            @unlink($filePath);
        }
        header('Location: ' . $baseUrl . '/admin/products/edit?id=' . $productId . '&image_removed=1');
        exit;
    }

    public function delete(): void
    {
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
        if ($id < 1) {
            header('Location: ' . $this->baseUrl() . '/admin/products');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, name FROM products WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            header('Location: ' . $this->baseUrl() . '/admin/products');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM order_items WHERE product_id = ?');
            $stmt->execute([$id]);
            if ((int) $stmt->fetchColumn() > 0) {
                $_SESSION['product_delete_error'] = 'Bu ürün siparişlerde kullanıldığı için silinemez.';
                header('Location: ' . $this->baseUrl() . '/admin/products/delete?id=' . $id);
                exit;
            }
            $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
            header('Location: ' . $this->baseUrl() . '/admin/products');
            exit;
        }
        $baseUrl = $this->baseUrl();
        $error = $_SESSION['product_delete_error'] ?? null;
        unset($_SESSION['product_delete_error']);
        $this->render('admin/products/delete', [
            'pageTitle' => 'Ürün sil',
            'baseUrl' => $baseUrl,
            'product' => $product,
            'error' => $error,
        ]);
    }

    private function slugify(string $text): string
    {
        $map = ['ı' => 'i', 'ğ' => 'g', 'ü' => 'u', 'ş' => 's', 'ö' => 'o', 'ç' => 'c', 'İ' => 'i', 'Ğ' => 'g', 'Ü' => 'u', 'Ş' => 's', 'Ö' => 'o', 'Ç' => 'c'];
        $text = strtr(mb_strtolower($text, 'UTF-8'), $map);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', trim($text));
        return $text ?: 'urun';
    }
}
