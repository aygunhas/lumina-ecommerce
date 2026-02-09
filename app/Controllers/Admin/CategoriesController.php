<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;
use PDO;

/**
 * Kategori yönetimi – liste, ekleme, düzenleme
 */
class CategoriesController extends AdminBaseController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('
            SELECT c.*, p.name AS parent_name
            FROM categories c
            LEFT JOIN categories p ON c.parent_id = p.id
            ORDER BY c.sort_order ASC, c.name ASC
        ');
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Üst kategori seçimi için tüm kategorileri al
        $parentsStmt = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC');
        $parents = $parentsStmt->fetchAll(PDO::FETCH_ASSOC);

        $baseUrl = $this->baseUrl();
        $this->render('admin/categories/index', [
            'pageTitle' => 'Kategoriler',
            'baseUrl' => $baseUrl,
            'categories' => $categories,
            'parents' => $parents,
        ]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }
        // GET ise index'e yönlendir (modal açılacak)
        header('Location: ' . $this->baseUrl() . '/admin/categories');
        exit;
    }

    private function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int) $_POST['parent_id'] : null;
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $homeHeroText = trim($_POST['home_hero_text'] ?? '');
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Kategori adı zorunludur.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            $_SESSION['category_errors'] = $errors;
            $_SESSION['category_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/categories');
            exit;
        }

        // Slug boşsa otomatik oluştur
        if ($slug === '') {
            $slug = $this->slugify($name);
        } else {
            $slug = $this->slugify($slug);
        }

        // Resim yükleme
        $imagePath = null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = 'cat_' . time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = '/uploads/categories/' . $fileName;
            }
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug = $slug . '-' . time();
        }
        $stmt = $pdo->prepare('
            INSERT INTO categories (parent_id, name, slug, description, image, home_hero_text, meta_title, meta_description, sort_order, is_active, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ');
        $stmt->execute([$parentId, $name, $slug, $description ?: null, $imagePath, $homeHeroText ?: null, $metaTitle ?: null, $metaDescription ?: null, $sortOrder, $isActive]);
        $_SESSION['success'] = 'Kategori başarıyla eklendi.';
        
        // AJAX isteği ise JSON döndür
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'message' => 'Kategori başarıyla eklendi.']);
            exit;
        }
        
        header('Location: ' . $baseUrl . '/admin/categories');
        exit;
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id < 1) {
            header('Location: ' . $this->baseUrl() . '/admin/categories');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$category) {
            header('Location: ' . $this->baseUrl() . '/admin/categories');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
            return;
        }
        // GET ise JSON döndür (AJAX için)
        $parents = $pdo->query('SELECT id, name FROM categories WHERE id != ' . (int) $id . ' ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'category' => $category,
            'parents' => $parents,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function update(int $id): void
    {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int) $_POST['parent_id'] : null;
        if ($parentId === $id) {
            $parentId = null;
        }
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $homeHeroText = trim($_POST['home_hero_text'] ?? '');
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Kategori adı zorunludur.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            // AJAX isteği ise JSON döndür
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }
            $_SESSION['category_errors'] = $errors;
            $_SESSION['category_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/categories');
            exit;
        }

        // Slug boşsa otomatik oluştur
        if ($slug === '') {
            $slug = $this->slugify($name);
        } else {
            $slug = $this->slugify($slug);
        }

        $pdo = Database::getConnection();
        
        // Mevcut kategoriyi al
        $stmt = $pdo->prepare('SELECT image FROM categories WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        $imagePath = $current['image'] ?? null;

        // Resim yükleme
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            // Eski resmi sil
            if ($imagePath && file_exists(BASE_PATH . '/public' . $imagePath)) {
                @unlink(BASE_PATH . '/public' . $imagePath);
            }
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = 'cat_' . time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = '/uploads/categories/' . $fileName;
            }
        }

        $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ? AND id != ? LIMIT 1');
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $slug = $slug . '-' . $id;
        }
        $stmt = $pdo->prepare('
            UPDATE categories SET parent_id = ?, name = ?, slug = ?, description = ?, image = ?, home_hero_text = ?, meta_title = ?, meta_description = ?, sort_order = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ');
        $stmt->execute([$parentId, $name, $slug, $description ?: null, $imagePath, $homeHeroText ?: null, $metaTitle ?: null, $metaDescription ?: null, $sortOrder, $isActive, $id]);
        $_SESSION['success'] = 'Kategori başarıyla güncellendi.';
        
        // AJAX isteği ise JSON döndür
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'message' => 'Kategori başarıyla güncellendi.']);
            exit;
        }
        
        header('Location: ' . $baseUrl . '/admin/categories');
        exit;
    }

    public function delete(): void
    {
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
        if ($id < 1) {
            header('Location: ' . $this->baseUrl() . '/admin/categories');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, name FROM categories WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$category) {
            header('Location: ' . $this->baseUrl() . '/admin/categories');
            exit;
        }

        // Alt kategori kontrolü
        $childStmt = $pdo->prepare('SELECT COUNT(*) as count FROM categories WHERE parent_id = ?');
        $childStmt->execute([$id]);
        $childCount = $childStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($childCount > 0) {
                $_SESSION['error'] = 'Bu kategorinin alt kategorileri var. Önce alt kategorileri silin.';
                header('Location: ' . $this->baseUrl() . '/admin/categories');
                exit;
            }
            // Resmi sil
            $imgStmt = $pdo->prepare('SELECT image FROM categories WHERE id = ? LIMIT 1');
            $imgStmt->execute([$id]);
            $img = $imgStmt->fetch(PDO::FETCH_ASSOC);
            if ($img && $img['image'] && file_exists(BASE_PATH . '/public' . $img['image'])) {
                @unlink(BASE_PATH . '/public' . $img['image']);
            }
            $pdo->prepare('UPDATE categories SET parent_id = NULL WHERE parent_id = ?')->execute([$id]);
            $pdo->prepare('UPDATE products SET category_id = NULL WHERE category_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            $_SESSION['success'] = 'Kategori başarıyla silindi.';
            
            // AJAX isteği ise JSON döndür
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true, 'message' => 'Kategori başarıyla silindi.']);
                exit;
            }
            
            header('Location: ' . $this->baseUrl() . '/admin/categories');
            exit;
        }
        // GET ise JSON döndür
        header('Content-Type: application/json');
        echo json_encode([
            'category' => $category,
            'has_children' => $childCount > 0,
        ]);
        exit;
    }

    private function slugify(string $text): string
    {
        $map = ['ı' => 'i', 'ğ' => 'g', 'ü' => 'u', 'ş' => 's', 'ö' => 'o', 'ç' => 'c', 'İ' => 'i', 'Ğ' => 'g', 'Ü' => 'u', 'Ş' => 's', 'Ö' => 'o', 'Ç' => 'c'];
        $text = strtr(mb_strtolower($text, 'UTF-8'), $map);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', trim($text));
        return $text ?: 'kategori';
    }
}
