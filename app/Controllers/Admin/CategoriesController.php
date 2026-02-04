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
            SELECT c.id, c.name, c.slug, c.sort_order, c.is_active, c.created_at,
                   p.name AS parent_name
            FROM categories c
            LEFT JOIN categories p ON c.parent_id = p.id
            ORDER BY c.sort_order ASC, c.name ASC
        ');
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $baseUrl = $this->baseUrl();
        $this->render('admin/categories/index', [
            'pageTitle' => 'Kategoriler',
            'baseUrl' => $baseUrl,
            'categories' => $categories,
        ]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }
        $pdo = Database::getConnection();
        $parents = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        $baseUrl = $this->baseUrl();
        $errors = $_SESSION['category_errors'] ?? [];
        $old = $_SESSION['category_old'] ?? [];
        unset($_SESSION['category_errors'], $_SESSION['category_old']);
        $this->render('admin/categories/create', [
            'pageTitle' => 'Yeni kategori',
            'baseUrl' => $baseUrl,
            'parents' => $parents,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int) $_POST['parent_id'] : null;
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Kategori adı zorunludur.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            $_SESSION['category_errors'] = $errors;
            $_SESSION['category_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/categories/create');
            exit;
        }

        $slug = $this->slugify($name);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug = $slug . '-' . time();
        }
        $stmt = $pdo->prepare('
            INSERT INTO categories (parent_id, name, slug, description, sort_order, is_active, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ');
        $stmt->execute([$parentId, $name, $slug, $description ?: null, $sortOrder, $isActive]);
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
        $parents = $pdo->query('SELECT id, name FROM categories WHERE id != ' . (int) $id . ' ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        $baseUrl = $this->baseUrl();
        $errors = $_SESSION['category_errors'] ?? [];
        $old = $_SESSION['category_old'] ?? [];
        unset($_SESSION['category_errors'], $_SESSION['category_old']);
        $this->render('admin/categories/edit', [
            'pageTitle' => 'Kategori düzenle',
            'baseUrl' => $baseUrl,
            'category' => $category,
            'parents' => $parents,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function update(int $id): void
    {
        $name = trim($_POST['name'] ?? '');
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int) $_POST['parent_id'] : null;
        if ($parentId === $id) {
            $parentId = null;
        }
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Kategori adı zorunludur.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            $_SESSION['category_errors'] = $errors;
            $_SESSION['category_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/categories/edit?id=' . $id);
            exit;
        }

        $slug = $this->slugify($name);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ? AND id != ? LIMIT 1');
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $slug = $slug . '-' . $id;
        }
        $stmt = $pdo->prepare('
            UPDATE categories SET parent_id = ?, name = ?, slug = ?, description = ?, sort_order = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ');
        $stmt->execute([$parentId, $name, $slug, $description ?: null, $sortOrder, $isActive, $id]);
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo->prepare('UPDATE categories SET parent_id = NULL WHERE parent_id = ?')->execute([$id]);
            $pdo->prepare('UPDATE products SET category_id = NULL WHERE category_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            header('Location: ' . $this->baseUrl() . '/admin/categories');
            exit;
        }
        $baseUrl = $this->baseUrl();
        $this->render('admin/categories/delete', [
            'pageTitle' => 'Kategori sil',
            'baseUrl' => $baseUrl,
            'category' => $category,
        ]);
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
