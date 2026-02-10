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
        $pdo->prepare('INSERT INTO product_images (product_id, path, alt, is_main, sort_order, created_at) VALUES (?, ?, ?, 1, 0, NOW())')
            ->execute([$productId, $relativePath, $productName]);
    }

    private function handleMultipleImages(int $productId, string $productName): void
    {
        // Görsellerin gönderilip gönderilmediğini kontrol et
        // $_FILES['images'] kontrolü - name attribute'u 'images[]' olduğu için PHP'de 'images' olarak gelir
        if (empty($_FILES['images']) || !isset($_FILES['images']['name'])) {
            return;
        }
        
        // Tek dosya mı yoksa çoklu dosya mı kontrol et
        $isMultiple = is_array($_FILES['images']['name']);
        
        if (!$isMultiple) {
            // Tek dosya gönderilmişse, çoklu dosya formatına çevir
            $_FILES['images'] = [
                'name' => [$_FILES['images']['name']],
                'type' => [$_FILES['images']['type']],
                'tmp_name' => [$_FILES['images']['tmp_name']],
                'error' => [$_FILES['images']['error']],
                'size' => [$_FILES['images']['size']]
            ];
        }
        
        // Boş dosya kontrolü
        if (empty($_FILES['images']['name']) || (is_array($_FILES['images']['name']) && empty(array_filter($_FILES['images']['name'])))) {
            return;
        }
        
        $pdo = Database::getConnection();
        $dir = BASE_PATH . '/public/' . self::UPLOAD_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $uploadedImageIds = [];
        $sortOrder = 0;
        
        foreach ($_FILES['images']['name'] as $key => $filename) {
            // Boş dosya adını atla
            if (empty($filename)) {
                continue;
            }
            
            if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            $tmpName = $_FILES['images']['tmp_name'][$key];
            if (!is_uploaded_file($tmpName)) {
                continue;
            }
            
            $fileSize = $_FILES['images']['size'][$key];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmpName);
            finfo_close($finfo);
            
            if (!in_array($mime, self::ALLOWED_TYPES, true) || $fileSize > self::MAX_IMAGE_SIZE) {
                continue;
            }
            
            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };
            
            $newFilename = $productId . '_' . time() . '_' . $key . '_' . uniqid() . '.' . $ext;
            $path = $dir . '/' . $newFilename;
            
            if (move_uploaded_file($tmpName, $path)) {
                $relativePath = self::UPLOAD_DIR . '/' . $newFilename;
                $isMain = 0; // Varsayılan olarak ana görsel değil
                $stmt = $pdo->prepare('INSERT INTO product_images (product_id, path, alt, is_main, sort_order, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$productId, $relativePath, $productName, $isMain, $sortOrder]);
                $uploadedImageIds[] = (int) $pdo->lastInsertId();
                $sortOrder++;
            }
        }
        
        // Ana görsel seçimi (main_image_index parametresi varsa - yeni görseller için)
        if (!empty($uploadedImageIds)) {
            // Mevcut ana görsel var mı kontrol et
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_main = 1');
            $stmt->execute([$productId]);
            $hasMainImage = (int) $stmt->fetchColumn() > 0;
            
            if (isset($_POST['main_image_index']) && is_numeric($_POST['main_image_index'])) {
                $mainIndex = (int) $_POST['main_image_index'];
                // Tüm görselleri sıfırla
                $pdo->prepare('UPDATE product_images SET is_main = 0 WHERE product_id = ?')->execute([$productId]);
                // Yeni yüklenen görsellerden seçilen ana görseli ayarla
                if (isset($uploadedImageIds[$mainIndex])) {
                    $pdo->prepare('UPDATE product_images SET is_main = 1 WHERE id = ?')->execute([$uploadedImageIds[$mainIndex]]);
                } elseif (count($uploadedImageIds) > 0) {
                    // Eğer index geçersizse ilk görseli ana görsel yap
                    $pdo->prepare('UPDATE product_images SET is_main = 1 WHERE id = ?')->execute([$uploadedImageIds[0]]);
                }
            } elseif (!$hasMainImage) {
                // Ana görsel seçilmemişse ve mevcut ana görsel yoksa ilk yüklenen görseli ana görsel yap
                $pdo->prepare('UPDATE product_images SET is_main = 1 WHERE id = ?')->execute([$uploadedImageIds[0]]);
            }
        }
    }

    private function handleColorImages(PDO $pdo, int $productId): void
    {
        // Önce attribute_value_id sütununun varlığını kontrol et
        try {
            $checkColumnStmt = $pdo->query("SHOW COLUMNS FROM product_images LIKE 'attribute_value_id'");
            if ($checkColumnStmt->rowCount() === 0) {
                error_log('handleColorImages: attribute_value_id sütunu bulunamadı. Migration çalıştırılmalı: 2024_01_01_000053_add_attribute_value_id_to_product_images.php');
                $_SESSION['error'] = 'Renk bazlı fotoğraflar için veritabanı güncellemesi gerekiyor. Lütfen migration dosyasını çalıştırın: 2024_01_01_000053_add_attribute_value_id_to_product_images.php';
                return;
            }
        } catch (\PDOException $e) {
            error_log('handleColorImages: Veritabanı kontrolü hatası: ' . $e->getMessage());
            return;
        }
        
        // Renk bazlı fotoğrafları kontrol et
        // FormData ile gönderildiğinde format: color_images[colorId][imgIndex]
        // PHP'de $_FILES['color_images']['name'][colorId][imgIndex] şeklinde erişilir
        if (empty($_FILES['color_images']) || !is_array($_FILES['color_images'])) {
            error_log('handleColorImages: $_FILES[color_images] boş veya array değil');
            return;
        }
        
        $dir = BASE_PATH . '/public/' . self::UPLOAD_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // PHP'nin $_FILES yapısını kontrol et
        // FormData ile color_images[colorId][imgIndex] formatında gönderildiğinde:
        // $_FILES['color_images']['name'][colorId][imgIndex] şeklinde erişilir
        $colorImagesData = $_FILES['color_images'];
        
        // Debug: $_FILES yapısını logla
        error_log('handleColorImages: $_FILES[color_images] structure: ' . print_r(array_keys($colorImagesData), true));
        
        // Her renk için fotoğrafları işle
        if (isset($colorImagesData['name']) && is_array($colorImagesData['name'])) {
            foreach ($colorImagesData['name'] as $colorId => $colorFiles) {
                $colorId = (int) $colorId;
                if ($colorId < 1) {
                    continue;
                }
                
                error_log("handleColorImages: Processing colorId: $colorId");
                
                // Renk ID'sinin geçerli bir attribute_value_id olduğunu kontrol et
                $checkStmt = $pdo->prepare('SELECT id FROM attribute_values WHERE id = ? LIMIT 1');
                $checkStmt->execute([$colorId]);
                if (!$checkStmt->fetch()) {
                    error_log("handleColorImages: Invalid colorId: $colorId");
                    continue; // Geçersiz renk ID'si
                }
                
                // Çoklu dosya kontrolü
                if (!is_array($colorFiles)) {
                    // Tek dosya ise çoklu dosya formatına çevir
                    $colorFiles = [$colorFiles];
                    $colorTypes = isset($colorImagesData['type'][$colorId]) ? 
                        (is_array($colorImagesData['type'][$colorId]) ? $colorImagesData['type'][$colorId] : [$colorImagesData['type'][$colorId]]) : 
                        [];
                    $colorTmpNames = isset($colorImagesData['tmp_name'][$colorId]) ? 
                        (is_array($colorImagesData['tmp_name'][$colorId]) ? $colorImagesData['tmp_name'][$colorId] : [$colorImagesData['tmp_name'][$colorId]]) : 
                        [];
                    $colorErrors = isset($colorImagesData['error'][$colorId]) ? 
                        (is_array($colorImagesData['error'][$colorId]) ? $colorImagesData['error'][$colorId] : [$colorImagesData['error'][$colorId]]) : 
                        [];
                    $colorSizes = isset($colorImagesData['size'][$colorId]) ? 
                        (is_array($colorImagesData['size'][$colorId]) ? $colorImagesData['size'][$colorId] : [$colorImagesData['size'][$colorId]]) : 
                        [];
                } else {
                    $colorTypes = isset($colorImagesData['type'][$colorId]) && is_array($colorImagesData['type'][$colorId]) ? $colorImagesData['type'][$colorId] : [];
                    $colorTmpNames = isset($colorImagesData['tmp_name'][$colorId]) && is_array($colorImagesData['tmp_name'][$colorId]) ? $colorImagesData['tmp_name'][$colorId] : [];
                    $colorErrors = isset($colorImagesData['error'][$colorId]) && is_array($colorImagesData['error'][$colorId]) ? $colorImagesData['error'][$colorId] : [];
                    $colorSizes = isset($colorImagesData['size'][$colorId]) && is_array($colorImagesData['size'][$colorId]) ? $colorImagesData['size'][$colorId] : [];
                }
                
                error_log("handleColorImages: colorId $colorId has " . count($colorFiles) . " files");
                
                $sortOrder = 0;
                foreach ($colorFiles as $key => $filename) {
                    // Boş dosya adını atla
                    if (empty($filename)) {
                        continue;
                    }
                    
                    if (!isset($colorErrors[$key]) || $colorErrors[$key] !== UPLOAD_ERR_OK) {
                        error_log("handleColorImages: Upload error for colorId $colorId, file $key: " . ($colorErrors[$key] ?? 'not set'));
                        continue;
                    }
                    
                    $tmpName = $colorTmpNames[$key] ?? null;
                    if (!$tmpName || !is_uploaded_file($tmpName)) {
                        error_log("handleColorImages: Invalid tmp_name for colorId $colorId, file $key");
                        continue;
                    }
                    
                    $fileSize = $colorSizes[$key] ?? 0;
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);
                    
                    if (!in_array($mime, self::ALLOWED_TYPES, true) || $fileSize > self::MAX_IMAGE_SIZE) {
                        error_log("handleColorImages: Invalid file type or size for colorId $colorId, file $key: mime=$mime, size=$fileSize");
                        continue;
                    }
                    
                    $ext = match ($mime) {
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        default => 'jpg',
                    };
                    
                    $newFilename = $productId . '_color_' . $colorId . '_' . time() . '_' . $key . '_' . uniqid() . '.' . $ext;
                    $path = $dir . '/' . $newFilename;
                    
                    if (move_uploaded_file($tmpName, $path)) {
                        $relativePath = self::UPLOAD_DIR . '/' . $newFilename;
                        $productName = $_POST['name'] ?? 'Ürün';
                        
                        // Veritabanına eklemeden önce attribute_value_id sütununun varlığını kontrol et
                        try {
                            $stmt = $pdo->prepare('INSERT INTO product_images (product_id, attribute_value_id, path, alt, is_main, sort_order, created_at) VALUES (?, ?, ?, ?, 0, ?, NOW())');
                            $stmt->execute([$productId, $colorId, $relativePath, $productName, $sortOrder]);
                            error_log("handleColorImages: Successfully saved image for colorId $colorId, file $key");
                            $sortOrder++;
                        } catch (\PDOException $e) {
                            error_log("handleColorImages: Database error for colorId $colorId, file $key: " . $e->getMessage());
                            // Dosyayı sil (veritabanına eklenemedi)
                            @unlink($path);
                        }
                    } else {
                        error_log("handleColorImages: Failed to move uploaded file for colorId $colorId, file $key");
                    }
                }
            }
        } else {
            error_log('handleColorImages: $_FILES[color_images][name] is not set or not an array');
        }
    }

    private function handleVariants(PDO $pdo, int $productId): void
    {
        if (empty($_POST['variants']) || !is_array($_POST['variants'])) {
            return;
        }
        foreach ($_POST['variants'] as $variantData) {
            if (empty($variantData['sku'])) {
                continue;
            }
            $variantSku = trim($variantData['sku']);
            $variantStock = (int) ($variantData['stock'] ?? 0);
            // Fiyat kontrolü: boş string, null veya sadece boşluk ise null, aksi halde float'a çevir
            $variantPrice = null;
            if (isset($variantData['price']) && $variantData['price'] !== '' && trim($variantData['price']) !== '') {
                $variantPrice = (float) str_replace(',', '.', trim($variantData['price']));
            }
            $variantSalePrice = null;
            if (isset($variantData['sale_price']) && $variantData['sale_price'] !== '' && trim($variantData['sale_price']) !== '') {
                $variantSalePrice = (float) str_replace(',', '.', trim($variantData['sale_price']));
            }
            $attributeValueIds = !empty($variantData['attribute_value_ids']) && is_array($variantData['attribute_value_ids'])
                ? array_map('intval', array_filter($variantData['attribute_value_ids']))
                : [];
            
            // SKU kontrolü
            $stmt = $pdo->prepare('SELECT id FROM product_variants WHERE sku = ? LIMIT 1');
            $stmt->execute([$variantSku]);
            if ($stmt->fetch()) {
                continue; // Bu SKU zaten var, atla
            }
            
            // Varyantı ekle
            $stmt = $pdo->prepare('
                INSERT INTO product_variants (product_id, sku, stock, price, sale_price, sort_order, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 0, NOW(), NOW())
            ');
            $stmt->execute([$productId, $variantSku, $variantStock, $variantPrice, $variantSalePrice]);
            $variantId = (int) $pdo->lastInsertId();
            
            // Attribute değerlerini bağla
            foreach ($attributeValueIds as $avId) {
                if ($avId > 0) {
                    $pdo->prepare('INSERT INTO product_variant_attribute_values (variant_id, attribute_value_id) VALUES (?, ?)')
                        ->execute([$variantId, $avId]);
                }
            }
        }
    }

    public function index(): void
    {
        $pdo = Database::getConnection();
        $search = trim($_GET['q'] ?? $_GET['search'] ?? '');
        $categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int) $_GET['category_id'] : null;
        $stockFilter = trim($_GET['stock'] ?? '');
        
        // Sayfalama
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $sql = 'SELECT p.id, p.name, p.sku, p.price, p.sale_price, p.stock, p.is_active, p.is_featured, p.is_new, p.created_at, p.low_stock_threshold,
                       c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE 1=1';
        $countSql = 'SELECT COUNT(*) FROM products p WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ?)';
            $countSql .= ' AND (p.name LIKE ? OR p.sku LIKE ?)';
            $term = '%' . $search . '%';
            $params[] = $term;
            $params[] = $term;
        }
        if ($categoryId !== null && $categoryId > 0) {
            $sql .= ' AND p.category_id = ?';
            $countSql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        if ($stockFilter === 'out_of_stock') {
            $sql .= ' AND p.stock <= 0';
            $countSql .= ' AND p.stock <= 0';
        } elseif ($stockFilter === 'low_stock') {
            $sql .= ' AND p.stock > 0 AND p.stock <= COALESCE(NULLIF(p.low_stock_threshold, 0), 5)';
            $countSql .= ' AND p.stock > 0 AND p.stock <= COALESCE(NULLIF(p.low_stock_threshold, 0), 5)';
        } elseif ($stockFilter === 'in_stock') {
            $sql .= ' AND p.stock > COALESCE(NULLIF(p.low_stock_threshold, 0), 5)';
            $countSql .= ' AND p.stock > COALESCE(NULLIF(p.low_stock_threshold, 0), 5)';
        }

        // Toplam kayıt sayısı
        $countStmt = $params === [] ? $pdo->query($countSql) : $pdo->prepare($countSql);
        if ($params !== []) {
            $countStmt->execute($params);
        }
        $totalRecords = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($totalRecords / $perPage));

        // Ürünleri çek (ana görsel ile)
        // LIMIT ve OFFSET değerlerini doğrudan SQL'e ekliyoruz (güvenli, kontrolümüzde)
        $sql .= ' ORDER BY p.created_at DESC, p.name ASC LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset;
        $stmt = $params === [] ? $pdo->query($sql) : $pdo->prepare($sql);
        if ($params !== []) {
            $stmt->execute($params);
        }
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Her ürün için ana görseli çek
        if (!empty($products)) {
            $productIds = array_column($products, 'id');
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            $imageStmt = $pdo->prepare("
                SELECT product_id, path 
                FROM product_images 
                WHERE product_id IN ($placeholders) AND is_main = 1
            ");
            $imageStmt->execute($productIds);
            $images = [];
            while ($row = $imageStmt->fetch(PDO::FETCH_ASSOC)) {
                $images[$row['product_id']] = $row['path'];
            }
            // Ana görsel yoksa ilk görseli al
            foreach ($productIds as $pid) {
                if (!isset($images[$pid])) {
                    $firstImgStmt = $pdo->prepare("SELECT path FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1");
                    $firstImgStmt->execute([$pid]);
                    $firstImg = $firstImgStmt->fetch(PDO::FETCH_ASSOC);
                    if ($firstImg) {
                        $images[$pid] = $firstImg['path'];
                    }
                }
            }
            foreach ($products as &$product) {
                $product['image'] = $images[$product['id']] ?? null;
            }
            unset($product);
        }

        $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        $baseUrl = $this->baseUrl();
        
        // AJAX isteği kontrolü
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'html' => $this->renderProductsTable($products, $baseUrl),
                'filters' => [
                    'q' => $search,
                    'category_id' => $categoryId,
                    'stock' => $stockFilter,
                ],
                'page' => $page,
                'totalPages' => $totalPages,
                'totalRecords' => $totalRecords,
            ]);
            return;
        }
        
        $this->render('admin/products/index', [
            'pageTitle' => 'Ürünler',
            'baseUrl' => $baseUrl,
            'products' => $products,
            'categories' => $categories,
            'filterQ' => $search,
            'filterCategoryId' => $categoryId,
            'filterStock' => $stockFilter,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalRecords' => $totalRecords,
        ]);
    }

    private function renderProductsTable(array $products, string $baseUrl): string
    {
        ob_start();
        if (empty($products)): ?>
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <p class="mt-4 text-sm font-medium text-stone-500">Henüz ürün yok</p>
                <p class="mt-1 text-xs text-stone-400">"Yeni Ürün" butonu ile ürün ekleyebilirsiniz</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-200">
                    <thead class="bg-stone-50/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-stone-500">Ürün</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-stone-500">Kategori</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-stone-500">Fiyat</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-medium uppercase tracking-wider text-stone-500">Stok</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-stone-500">Durum</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-stone-500">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200 bg-[#FAFAF9]">
                        <?php foreach ($products as $p): 
                            $displayPrice = $p['sale_price'] !== null && (float) $p['sale_price'] > 0
                                ? (float) $p['sale_price']
                                : (float) $p['price'];
                            $originalPrice = (float) $p['price'];
                            $hasSale = $p['sale_price'] !== null && (float) $p['sale_price'] > 0 && (float) $p['sale_price'] < $originalPrice;
                            $stock = (int) $p['stock'];
                            $lowStockThreshold = (int) ($p['low_stock_threshold'] ?? 5);
                            $isLowStock = $stock > 0 && $stock <= $lowStockThreshold;
                            $isOutOfStock = $stock === 0;
                        ?>
                            <tr class="hover:bg-stone-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($p['image'])): ?>
                                            <div class="h-12 w-12 shrink-0 overflow-hidden rounded-md">
                                                <img src="<?= htmlspecialchars($baseUrl . '/' . $p['image']) ?>" 
                                                     alt="<?= htmlspecialchars($p['name']) ?>" 
                                                     class="h-full w-full object-cover"
                                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'flex h-full w-full items-center justify-center bg-stone-100 text-xs font-medium text-stone-400 rounded-md\'>Görsel Yok</div>'">
                                            </div>
                                        <?php else: ?>
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-stone-100 text-xs font-medium text-stone-400">
                                                Görsel Yok
                                            </div>
                                        <?php endif; ?>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-stone-800"><?= htmlspecialchars($p['name']) ?></p>
                                            <?php if (!empty($p['sku'])): ?>
                                                <p class="mt-0.5 text-xs text-stone-400">SKU: <?= htmlspecialchars($p['sku']) ?></p>
                                            <?php endif; ?>
                                            <div class="mt-1 flex items-center gap-2">
                                                <?php if ((int) $p['is_featured']): ?>
                                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 border border-emerald-200">Öne Çıkan</span>
                                                <?php endif; ?>
                                                <?php if ((int) $p['is_new']): ?>
                                                    <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 border border-amber-200">Yeni</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-stone-600"><?= $p['category_name'] ? htmlspecialchars($p['category_name']) : '—' ?></p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="text-sm font-medium text-stone-800">
                                        <?php if ($hasSale): ?>
                                            <span class="text-rose-600"><?= number_format($displayPrice, 2, ',', '.') ?> ₺</span>
                                            <span class="ml-2 text-xs text-stone-400 line-through"><?= number_format($originalPrice, 2, ',', '.') ?> ₺</span>
                                        <?php else: ?>
                                            <?= number_format($displayPrice, 2, ',', '.') ?> ₺
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <?php if ($isOutOfStock): ?>
                                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                        <?php elseif ($isLowStock): ?>
                                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                        <?php endif; ?>
                                        <span class="text-sm font-medium <?= $isOutOfStock ? 'text-rose-600' : ($isLowStock ? 'text-amber-600' : 'text-stone-700') ?>">
                                            <?= $stock ?> Adet
                                        </span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="h-2 w-2 rounded-full <?= (int) $p['is_active'] ? 'bg-emerald-500' : 'bg-stone-300' ?>"></span>
                                        <span class="text-xs font-medium <?= (int) $p['is_active'] ? 'text-emerald-700' : 'text-stone-500' ?>">
                                            <?= (int) $p['is_active'] ? 'Aktif' : 'Pasif' ?>
                                        </span>
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products/edit?id=<?= (int) $p['id'] ?>" 
                                           class="text-stone-400 transition-colors hover:text-stone-800"
                                           title="Düzenle">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <button type="button"
                                                onclick="if(window.deleteProductHandler) window.deleteProductHandler(<?= (int) $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>')"
                                                class="text-stone-400 transition-colors hover:text-rose-600"
                                                title="Sil">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif;
        return ob_get_clean();
    }


    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }
        $pdo = Database::getConnection();
        $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        $brands = $pdo->query('SELECT id, name FROM brands ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        
        // Varyantlar için attributes ve attribute_values
        $attributes = $pdo->query('SELECT id, name, type FROM attributes ORDER BY sort_order ASC, name ASC')->fetchAll(PDO::FETCH_ASSOC);
        $attributeValuesByAttr = [];
        foreach ($attributes as $a) {
            // DISTINCT kullanarak aynı value'ya sahip duplicate kayıtları önle
            // Aynı value'ya sahip kayıtlardan sadece en küçük ID'ye sahip olanı al
            $stmt = $pdo->prepare('
                SELECT av1.id, av1.value, av1.color_hex 
                FROM attribute_values av1
                INNER JOIN (
                    SELECT MIN(id) as min_id, value, attribute_id
                    FROM attribute_values
                    WHERE attribute_id = ?
                    GROUP BY value, attribute_id
                ) av2 ON av1.id = av2.min_id AND av1.attribute_id = av2.attribute_id
                WHERE av1.attribute_id = ?
                ORDER BY av1.sort_order ASC, av1.value ASC
            ');
            $stmt->execute([$a['id'], $a['id']]);
            $attributeValuesByAttr[$a['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $baseUrl = $this->baseUrl();
        $errors = $_SESSION['product_errors'] ?? [];
        $old = $_SESSION['product_old'] ?? [];
        unset($_SESSION['product_errors'], $_SESSION['product_old']);
        $this->render('admin/products/form', [
            'pageTitle' => 'Yeni ürün',
            'baseUrl' => $baseUrl,
            'categories' => $categories,
            'brands' => $brands,
            'attributes' => $attributes,
            'attributeValuesByAttr' => $attributeValuesByAttr,
            'colorImages' => [], // Yeni ürün için boş
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function store(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $categoryId = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
        $brandId = null; // Marka kaldırıldı
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
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $materialCare = trim($_POST['material_care'] ?? '');
        $materialCare = $materialCare !== '' ? $materialCare : null;

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Ürün adı zorunludur.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Form hataları var.',
                    'errors' => $errors
                ]);
                return;
            }
            $_SESSION['product_errors'] = $errors;
            $_SESSION['product_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/products/create');
            exit;
        }

        // Slug boşsa otomatik oluştur
        if ($slug === '') {
            $slug = $this->slugify($name);
        } else {
            $slug = $this->slugify($slug);
        }

        $pdo = Database::getConnection();
        
        // Slug kontrolü
        $stmt = $pdo->prepare('SELECT id FROM products WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug = $slug . '-' . time();
        }

        // Transaction başlat
        $pdo->beginTransaction();
        try {
            // Ürünü ekle
            $stmt = $pdo->prepare('
                INSERT INTO products (category_id, brand_id, name, slug, description, short_description, material_care, price, sale_price, sku, stock, low_stock_threshold, is_featured, is_new, is_active, sort_order, meta_title, meta_description, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $categoryId, $brandId, $name, $slug, $description ?: null, $shortDescription ?: null, $materialCare ?: null,
                $price, $salePrice, $sku ?: null, $stock, $lowStockThreshold, 
                $isFeatured, $isNew, $isActive, $sortOrder, $metaTitle ?: null, $metaDescription ?: null
            ]);
            $productId = (int) $pdo->lastInsertId();

            // Varyantları ekle
            $this->handleVariants($pdo, $productId);
            
            // Renk bazlı fotoğrafları kaydet
            $this->handleColorImages($pdo, $productId);

            $pdo->commit();
            
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'message' => 'Ürün başarıyla eklendi.',
                    'productId' => $productId,
                    'redirect' => $baseUrl . '/admin/products'
                ]);
                return;
            }
            
            $_SESSION['success'] = 'Ürün başarıyla eklendi.';
            header('Location: ' . $baseUrl . '/admin/products');
            exit;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            $errorMessage = 'Ürün eklenirken bir hata oluştu: ' . $e->getMessage();
            
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage
                ]);
                return;
            }
            
            $_SESSION['product_errors'] = ['general' => $errorMessage];
            $_SESSION['product_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/products/create');
            exit;
        }
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
        $brands = $pdo->query('SELECT id, name FROM brands ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        // Ürün fotoğraflarını çek (attribute_value_id NULL olanlar - genel ürün fotoğrafları)
        $stmt = $pdo->prepare('SELECT id, path, alt, is_main, sort_order, attribute_value_id FROM product_images WHERE product_id = ? AND attribute_value_id IS NULL ORDER BY sort_order ASC, id ASC');
        $stmt->execute([$id]);
        $productImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Renk bazlı fotoğrafları çek (attribute_value_id NULL olmayanlar)
        $colorImagesStmt = $pdo->prepare('SELECT id, path, alt, attribute_value_id, sort_order FROM product_images WHERE product_id = ? AND attribute_value_id IS NOT NULL ORDER BY attribute_value_id ASC, sort_order ASC, id ASC');
        $colorImagesStmt->execute([$id]);
        $colorImagesRaw = $colorImagesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Renk bazlı fotoğrafları colorId'ye göre grupla
        $colorImages = [];
        foreach ($colorImagesRaw as $img) {
            $colorId = (int)$img['attribute_value_id'];
            if (!isset($colorImages[$colorId])) {
                $colorImages[$colorId] = [];
            }
            $colorImages[$colorId][] = $img;
        }

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
                SELECT av.id, av.value, av.attribute_id FROM product_variant_attribute_values pvav
                INNER JOIN attribute_values av ON pvav.attribute_value_id = av.id
                WHERE pvav.variant_id = ?
                ORDER BY av.sort_order ASC
            ');
            $stmt->execute([$v['id']]);
            $attrData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $v['attribute_value_ids'] = array_column($attrData, 'id');
            $v['attributes_summary'] = implode(', ', array_column($attrData, 'value'));
        }
        unset($v);

        $attributes = $pdo->query('
            SELECT a.id, a.name, a.type
            FROM attributes a
            ORDER BY a.sort_order ASC, a.name ASC
        ')->fetchAll(PDO::FETCH_ASSOC);
        $attributeValuesByAttr = [];
        foreach ($attributes as $a) {
            // DISTINCT kullanarak aynı value'ya sahip duplicate kayıtları önle
            // Aynı value'ya sahip kayıtlardan sadece en küçük ID'ye sahip olanı al
            $stmt = $pdo->prepare('
                SELECT av1.id, av1.value, av1.color_hex 
                FROM attribute_values av1
                INNER JOIN (
                    SELECT MIN(id) as min_id, value, attribute_id
                    FROM attribute_values
                    WHERE attribute_id = ?
                    GROUP BY value, attribute_id
                ) av2 ON av1.id = av2.min_id AND av1.attribute_id = av2.attribute_id
                WHERE av1.attribute_id = ?
                ORDER BY av1.sort_order ASC, av1.value ASC
            ');
            $stmt->execute([$a['id'], $a['id']]);
            $attributeValuesByAttr[$a['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $baseUrl = $this->baseUrl();
        $errors = $_SESSION['product_errors'] ?? [];
        $old = $_SESSION['product_old'] ?? [];
        unset($_SESSION['product_errors'], $_SESSION['product_old']);
        $this->render('admin/products/form', [
            'pageTitle' => 'Ürün düzenle',
            'baseUrl' => $baseUrl,
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
            'productImages' => $productImages,
            'colorImages' => $colorImages, // Renk bazlı fotoğraflar
            'productVariants' => $productVariants,
            'attributes' => $attributes,
            'attributeValuesByAttr' => $attributeValuesByAttr,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function update(int $id): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $categoryId = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
        $brandId = null; // Marka kaldırıldı
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
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $materialCare = trim($_POST['material_care'] ?? '');
        $materialCare = $materialCare !== '' ? $materialCare : null;

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Ürün adı zorunludur.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Form hataları var.',
                    'errors' => $errors
                ]);
                return;
            }
            $_SESSION['product_errors'] = $errors;
            $_SESSION['product_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/products/edit?id=' . $id);
            exit;
        }

        // Slug boşsa otomatik oluştur
        if ($slug === '') {
            $slug = $this->slugify($name);
        } else {
            $slug = $this->slugify($slug);
        }

        $pdo = Database::getConnection();
        
        // Slug kontrolü
        $stmt = $pdo->prepare('SELECT id FROM products WHERE slug = ? AND id != ? LIMIT 1');
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $slug = $slug . '-' . $id;
        }

        // Transaction başlat
        $pdo->beginTransaction();
        try {
            // Ürünü güncelle
            $stmt = $pdo->prepare('
                UPDATE products SET category_id = ?, brand_id = ?, name = ?, slug = ?, description = ?, short_description = ?, material_care = ?, price = ?, sale_price = ?, sku = ?, stock = ?, low_stock_threshold = ?, is_featured = ?, is_new = ?, is_active = ?, sort_order = ?, meta_title = ?, meta_description = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $categoryId, $brandId, $name, $slug, $description ?: null, $shortDescription ?: null, $materialCare ?: null, 
                $price, $salePrice, $sku ?: null, $stock, $lowStockThreshold, 
                $isFeatured, $isNew, $isActive, $sortOrder, $metaTitle ?: null, $metaDescription ?: null, $id
            ]);

            // Silinecek renk bazlı fotoğrafları işle
            if (!empty($_POST['delete_color_images']) && is_array($_POST['delete_color_images'])) {
                foreach ($_POST['delete_color_images'] as $imageId) {
                    $imageId = (int) $imageId;
                    if ($imageId > 0) {
                        // Görsel dosyasını sil
                        $imgStmt = $pdo->prepare('SELECT path FROM product_images WHERE id = ? AND product_id = ? AND attribute_value_id IS NOT NULL LIMIT 1');
                        $imgStmt->execute([$imageId, $id]);
                        $imgRow = $imgStmt->fetch(PDO::FETCH_ASSOC);
                        if ($imgRow) {
                            $filePath = BASE_PATH . '/public/' . $imgRow['path'];
                            if (is_file($filePath)) {
                                @unlink($filePath);
                            }
                        }
                        // Veritabanından sil
                        $pdo->prepare('DELETE FROM product_images WHERE id = ? AND product_id = ? AND attribute_value_id IS NOT NULL')->execute([$imageId, $id]);
                    }
                }
            }

            // Varyantları güncelle/sil/ekle
            $this->updateVariants($pdo, $id);
            
            // Renk bazlı fotoğrafları kaydet
            $this->handleColorImages($pdo, $id);

            $pdo->commit();
            
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                $redirectUrl = $baseUrl . '/admin/products/edit?id=' . $id;
                if (isset($_POST['active_tab']) && $_POST['active_tab'] === 'variants') {
                    $redirectUrl .= '#variants';
                }
                echo json_encode([
                    'success' => true,
                    'message' => 'Ürün başarıyla güncellendi.',
                    'redirect' => $redirectUrl
                ]);
                return;
            }
            
            $_SESSION['success'] = 'Ürün başarıyla güncellendi.';
            // Varyantlar sekmesinde isek, varyantlar sekmesine yönlendir
            $redirectUrl = $baseUrl . '/admin/products/edit?id=' . $id;
            if (isset($_POST['active_tab']) && $_POST['active_tab'] === 'variants') {
                $redirectUrl .= '#variants';
            }
            header('Location: ' . $redirectUrl);
            exit;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            $errorMessage = 'Ürün güncellenirken bir hata oluştu: ' . $e->getMessage();
            
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage
                ]);
                return;
            }
            
            $_SESSION['product_errors'] = ['general' => $errorMessage];
            $_SESSION['product_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/products/edit?id=' . $id);
            exit;
        }
    }

    private function updateVariants(PDO $pdo, int $productId): void
    {
        // Silinecek varyantlar
        if (!empty($_POST['delete_variants']) && is_array($_POST['delete_variants'])) {
            $deleteIds = array_map('intval', $_POST['delete_variants']);
            if (!empty($deleteIds)) {
                $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
                $pdo->prepare("DELETE FROM product_variant_attribute_values WHERE variant_id IN ($placeholders)")->execute($deleteIds);
                $pdo->prepare("DELETE FROM product_variants WHERE id IN ($placeholders) AND product_id = ?")->execute(array_merge($deleteIds, [$productId]));
            }
        }

        // Mevcut varyantları güncelle
        if (!empty($_POST['existing_variants']) && is_array($_POST['existing_variants'])) {
            foreach ($_POST['existing_variants'] as $variantId => $variantData) {
                $variantId = (int) $variantId;
                if ($variantId < 1) continue;
                
                $variantSku = trim($variantData['sku'] ?? '');
                $variantStock = (int) ($variantData['stock'] ?? 0);
                // Fiyat kontrolü: boş string, null veya sadece boşluk ise null, aksi halde float'a çevir
                $variantPrice = null;
                if (isset($variantData['price']) && $variantData['price'] !== '' && trim($variantData['price']) !== '') {
                    $variantPrice = (float) str_replace(',', '.', trim($variantData['price']));
                    // 0 değeri geçerli bir fiyat olabilir, bu yüzden sadece boş/null kontrolü yapıyoruz
                }
                $variantSalePrice = null;
                if (isset($variantData['sale_price']) && $variantData['sale_price'] !== '' && trim($variantData['sale_price']) !== '') {
                    $variantSalePrice = (float) str_replace(',', '.', trim($variantData['sale_price']));
                }
                $attributeValueIds = !empty($variantData['attribute_value_ids']) && is_array($variantData['attribute_value_ids'])
                    ? array_map('intval', array_filter($variantData['attribute_value_ids']))
                    : [];
                
                if ($variantSku === '') continue;
                
                // Önce varyantın bu ürüne ait olduğunu ve var olduğunu kontrol et
                $checkStmt = $pdo->prepare('SELECT id FROM product_variants WHERE id = ? AND product_id = ? LIMIT 1');
                $checkStmt->execute([$variantId, $productId]);
                if (!$checkStmt->fetch()) {
                    // Bu variant bu ürüne ait değil veya mevcut değil, atla
                    continue;
                }
                
                // Varyantı güncelle
                $stmt = $pdo->prepare('UPDATE product_variants SET sku = ?, stock = ?, price = ?, sale_price = ?, updated_at = NOW() WHERE id = ? AND product_id = ?');
                $stmt->execute([$variantSku, $variantStock, $variantPrice, $variantSalePrice, $variantId, $productId]);
                
                // UPDATE başarılı olduysa (en az 1 satır etkilendiyse) attribute değerlerini güncelle
                if ($stmt->rowCount() > 0) {
                    // Attribute değerlerini güncelle
                    $pdo->prepare('DELETE FROM product_variant_attribute_values WHERE variant_id = ?')->execute([$variantId]);
                    foreach ($attributeValueIds as $avId) {
                        if ($avId > 0) {
                            $pdo->prepare('INSERT INTO product_variant_attribute_values (variant_id, attribute_value_id) VALUES (?, ?)')
                                ->execute([$variantId, $avId]);
                        }
                    }
                }
            }
        }

        // Yeni varyantları ekle
        $this->handleVariants($pdo, $productId);
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
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Geçersiz ürün ID.']);
                return;
            }
            header('Location: ' . $this->baseUrl() . '/admin/products');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, name FROM products WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Ürün bulunamadı.']);
                return;
            }
            header('Location: ' . $this->baseUrl() . '/admin/products');
            exit;
        }
        
        // AJAX isteği kontrolü
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sipariş kontrolü - aktif siparişlerde kullanılıyor mu?
            // Sadece pending, confirmed, processing durumlarındaki siparişlerde kullanılıyorsa silme engellenir
            $stmt = $pdo->prepare('
                SELECT COUNT(*) 
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE oi.product_id = ? 
                AND o.status IN (\'pending\', \'confirmed\', \'processing\')
            ');
            $stmt->execute([$id]);
            $activeOrderCount = (int) $stmt->fetchColumn();
            
            if ($activeOrderCount > 0) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(400);
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Bu ürün aktif siparişlerde (beklemede, onaylandı, hazırlanıyor) kullanıldığı için silinemez.'
                    ]);
                    return;
                }
                $_SESSION['product_delete_error'] = 'Bu ürün aktif siparişlerde kullanıldığı için silinemez.';
                header('Location: ' . $this->baseUrl() . '/admin/products/delete?id=' . $id);
                exit;
            }
            
            // Kargoda durumundaki siparişlerde kullanılıyor mu? (Bilgi verilerek silinebilir)
            $stmt = $pdo->prepare('
                SELECT COUNT(*) 
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE oi.product_id = ? 
                AND o.status = \'shipped\'
            ');
            $stmt->execute([$id]);
            $shippedOrderCount = (int) $stmt->fetchColumn();
            
            // Kargoda durumunda bilgi mesajı (AJAX için)
            if ($shippedOrderCount > 0 && $isAjax) {
                // Kullanıcıya bilgi verilmiş kabul ediyoruz, silme işlemine devam ediyoruz
                // Frontend'de zaten uyarı gösterilecek
            }
            
            // Transaction başlat
            $pdo->beginTransaction();
            try {
                // Foreign key constraint'i geçici olarak devre dışı bırak
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
                
                // Ürün görsellerini sil (CASCADE ile otomatik silinir ama dosyaları da silmek için)
                $imageStmt = $pdo->prepare('SELECT path FROM product_images WHERE product_id = ?');
                $imageStmt->execute([$id]);
                $images = $imageStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($images as $img) {
                    $filePath = BASE_PATH . '/public/' . $img['path'];
                    if (is_file($filePath)) {
                        @unlink($filePath);
                    }
                }
                
                // Ürünü sil (CASCADE ile görseller ve varyantlar otomatik silinir)
                // Foreign key constraint devre dışı olduğu için order_items'daki referanslar sorun çıkarmayacak
                // Sipariş geçmişi korunur çünkü product_name, product_sku, price gibi bilgiler order_items'da saklanıyor
                $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
                
                // Foreign key constraint'i tekrar aktif et
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
                
                $pdo->commit();
            } catch (\Throwable $e) {
                // Hata durumunda constraint'i tekrar aktif et
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
                $pdo->rollBack();
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Ürün silinirken bir hata oluştu: ' . $e->getMessage()]);
                    return;
                }
                throw $e;
            }
            
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true, 'message' => 'Ürün başarıyla silindi.']);
                return;
            }
            
            $_SESSION['success'] = 'Ürün başarıyla silindi.';
            header('Location: ' . $this->baseUrl() . '/admin/products');
            exit;
        }
        
        // GET isteği - AJAX için JSON döndür (sipariş durumları ile birlikte)
        if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'GET') {
            // Aktif siparişlerde kullanılıyor mu?
            $stmt = $pdo->prepare('
                SELECT COUNT(*) 
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE oi.product_id = ? 
                AND o.status IN (\'pending\', \'confirmed\', \'processing\')
            ');
            $stmt->execute([$id]);
            $activeOrderCount = (int) $stmt->fetchColumn();
            
            // Kargoda durumundaki siparişler
            $stmt = $pdo->prepare('
                SELECT COUNT(*) 
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE oi.product_id = ? 
                AND o.status = \'shipped\'
            ');
            $stmt->execute([$id]);
            $shippedOrderCount = (int) $stmt->fetchColumn();
            
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'product' => [
                    'id' => $product['id'],
                    'name' => $product['name']
                ],
                'order_info' => [
                    'active_orders' => $activeOrderCount,
                    'shipped_orders' => $shippedOrderCount
                ]
            ]);
            return;
        }
        
        // Normal GET isteği - eski view'ı render et
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
