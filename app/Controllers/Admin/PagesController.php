<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;
use PDO;

/**
 * Admin: Sabit sayfalar CRUD (B32)
 */
class PagesController extends AdminBaseController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('
            SELECT id, slug, title, is_active, sort_order, created_at
            FROM pages
            ORDER BY sort_order ASC, title ASC
        ');
        $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $baseUrl = $this->baseUrl();
        $this->render('admin/pages/index', [
            'pageTitle' => 'Sayfalar',
            'baseUrl' => $baseUrl,
            'pages' => $pages,
        ]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }
        $baseUrl = $this->baseUrl();
        $errors = $_SESSION['page_errors'] ?? [];
        $old = $_SESSION['page_old'] ?? [];
        unset($_SESSION['page_errors'], $_SESSION['page_old']);
        $this->render('admin/pages/form', [
            'pageTitle' => 'Yeni sayfa',
            'baseUrl' => $baseUrl,
            'page' => null,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function store(): void
    {
        $slug = $this->normalizeSlug(trim($_POST['slug'] ?? ''));
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if ($slug === '') {
            $errors['slug'] = 'Slug zorunludur.';
        } elseif (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
            $errors['slug'] = 'Slug sadece küçük harf, rakam ve tire içerebilir.';
        }
        if ($title === '') {
            $errors['title'] = 'Başlık zorunludur.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            $_SESSION['page_errors'] = $errors;
            $_SESSION['page_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/pages/create');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM pages WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $_SESSION['page_errors'] = ['slug' => 'Bu slug zaten kullanılıyor.'];
            $_SESSION['page_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/pages/create');
            exit;
        }

        $pdo->prepare('
            INSERT INTO pages (slug, title, content, meta_title, meta_description, is_active, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ')->execute([$slug, $title, $content ?: null, $metaTitle ?: null, $metaDescription ?: null, $isActive, $sortOrder]);

        header('Location: ' . $baseUrl . '/admin/pages?created=1');
        exit;
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $baseUrl = $this->baseUrl();
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/admin/pages');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, slug, title, content, meta_title, meta_description, is_active, sort_order FROM pages WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $page = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$page) {
            header('Location: ' . $baseUrl . '/admin/pages');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
            return;
        }

        $errors = $_SESSION['page_errors'] ?? [];
        $old = $_SESSION['page_old'] ?? [];
        unset($_SESSION['page_errors'], $_SESSION['page_old']);
        if (empty($old)) {
            $old = [
                'slug' => $page['slug'],
                'title' => $page['title'],
                'content' => $page['content'] ?? '',
                'meta_title' => $page['meta_title'] ?? '',
                'meta_description' => $page['meta_description'] ?? '',
                'sort_order' => $page['sort_order'],
                'is_active' => (int) $page['is_active'],
            ];
        }

        $this->render('admin/pages/form', [
            'pageTitle' => 'Sayfa düzenle',
            'baseUrl' => $baseUrl,
            'page' => $page,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function update(int $id): void
    {
        $slug = $this->normalizeSlug(trim($_POST['slug'] ?? ''));
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if ($slug === '') {
            $errors['slug'] = 'Slug zorunludur.';
        } elseif (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
            $errors['slug'] = 'Slug sadece küçük harf, rakam ve tire içerebilir.';
        }
        if ($title === '') {
            $errors['title'] = 'Başlık zorunludur.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            $_SESSION['page_errors'] = $errors;
            $_SESSION['page_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/pages/edit?id=' . $id);
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM pages WHERE slug = ? AND id != ? LIMIT 1');
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $_SESSION['page_errors'] = ['slug' => 'Bu slug zaten başka bir sayfada kullanılıyor.'];
            $_SESSION['page_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/pages/edit?id=' . $id);
            exit;
        }

        $pdo->prepare('
            UPDATE pages SET slug = ?, title = ?, content = ?, meta_title = ?, meta_description = ?, is_active = ?, sort_order = ?, updated_at = NOW()
            WHERE id = ?
        ')->execute([$slug, $title, $content ?: null, $metaTitle ?: null, $metaDescription ?: null, $isActive, $sortOrder, $id]);

        header('Location: ' . $baseUrl . '/admin/pages?updated=1');
        exit;
    }

    public function delete(): void
    {
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
        $baseUrl = $this->baseUrl();
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/admin/pages');
            exit;
        }

        $pdo = Database::getConnection();
        $pdo->prepare('DELETE FROM pages WHERE id = ?')->execute([$id]);

        header('Location: ' . $baseUrl . '/admin/pages?deleted=1');
        exit;
    }

    private function normalizeSlug(string $slug): string
    {
        $slug = mb_strtolower($slug, 'UTF-8');
        $tr = ['ş' => 's', 'ğ' => 'g', 'ü' => 'u', 'ö' => 'o', 'ç' => 'c', 'ı' => 'i', 'İ' => 'i'];
        $slug = strtr($slug, $tr);
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
