<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;
use PDO;

/**
 * Admin: Slider CRUD (A1, B33)
 */
class SlidersController extends AdminBaseController
{
    private const UPLOAD_DIR = 'uploads/sliders';
    private const MAX_IMAGE_SIZE = 3 * 1024 * 1024; // 3 MB
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function index(): void
    {
        $pdo = Database::getConnection();
        $sliders = $pdo->query('
            SELECT id, title, subtitle, image, link, link_text, sort_order, is_active, created_at
            FROM sliders
            ORDER BY sort_order ASC, id ASC
        ')->fetchAll(PDO::FETCH_ASSOC);
        $baseUrl = $this->baseUrl();
        $this->render('admin/sliders/index', [
            'pageTitle' => 'Slider',
            'baseUrl' => $baseUrl,
            'sliders' => $sliders,
        ]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }
        $baseUrl = $this->baseUrl();
        $errors = $_SESSION['slider_errors'] ?? [];
        $old = $_SESSION['slider_old'] ?? [];
        unset($_SESSION['slider_errors'], $_SESSION['slider_old']);
        $this->render('admin/sliders/form', [
            'pageTitle' => 'Yeni slider',
            'baseUrl' => $baseUrl,
            'slider' => null,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function store(): void
    {
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $link = trim($_POST['link'] ?? '');
        $linkText = trim($_POST['link_text'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $baseUrl = $this->baseUrl();
        $imagePath = $this->handleImageUpload(null);
        if ($imagePath === null && empty($_FILES['image']['tmp_name'])) {
            $_SESSION['slider_errors'] = ['image' => 'Slider görseli zorunludur.'];
            $_SESSION['slider_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/sliders/create');
            exit;
        }
        if ($imagePath === null) {
            $_SESSION['slider_errors'] = ['image' => 'Geçersiz veya çok büyük görsel. JPG/PNG/WebP, max 3 MB.'];
            $_SESSION['slider_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/sliders/create');
            exit;
        }

        $pdo = Database::getConnection();
        $pdo->prepare('
            INSERT INTO sliders (title, subtitle, image, link, link_text, sort_order, is_active, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ')->execute([$title ?: null, $subtitle ?: null, $imagePath, $link ?: null, $linkText ?: null, $sortOrder, $isActive]);

        header('Location: ' . $baseUrl . '/admin/sliders?created=1');
        exit;
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $baseUrl = $this->baseUrl();
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/admin/sliders');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM sliders WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $slider = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$slider) {
            header('Location: ' . $baseUrl . '/admin/sliders');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id, $slider['image']);
            return;
        }

        $errors = $_SESSION['slider_errors'] ?? [];
        $old = $_SESSION['slider_old'] ?? [];
        unset($_SESSION['slider_errors'], $_SESSION['slider_old']);
        if (empty($old)) {
            $old = [
                'title' => $slider['title'] ?? '',
                'subtitle' => $slider['subtitle'] ?? '',
                'link' => $slider['link'] ?? '',
                'link_text' => $slider['link_text'] ?? '',
                'sort_order' => (int) $slider['sort_order'],
                'is_active' => (int) $slider['is_active'],
            ];
        }

        $this->render('admin/sliders/form', [
            'pageTitle' => 'Slider düzenle',
            'baseUrl' => $baseUrl,
            'slider' => $slider,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function update(int $id, string $currentImage): void
    {
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $link = trim($_POST['link'] ?? '');
        $linkText = trim($_POST['link_text'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $baseUrl = $this->baseUrl();
        $imagePath = $this->handleImageUpload($id);
        if ($imagePath === null) {
            $imagePath = $currentImage;
        }

        $pdo = Database::getConnection();
        $pdo->prepare('
            UPDATE sliders SET title = ?, subtitle = ?, image = ?, link = ?, link_text = ?, sort_order = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ')->execute([$title ?: null, $subtitle ?: null, $imagePath, $link ?: null, $linkText ?: null, $sortOrder, $isActive, $id]);

        header('Location: ' . $baseUrl . '/admin/sliders?updated=1');
        exit;
    }

    public function delete(): void
    {
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
        $baseUrl = $this->baseUrl();
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/admin/sliders');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT image FROM sliders WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['image']) {
            $path = BASE_PATH . '/public/' . $row['image'];
            if (is_file($path)) {
                @unlink($path);
            }
        }
        $pdo->prepare('DELETE FROM sliders WHERE id = ?')->execute([$id]);

        header('Location: ' . $baseUrl . '/admin/sliders?deleted=1');
        exit;
    }

    /** Returns new image path or null; if no upload, returns null (caller keeps current). */
    private function handleImageUpload(?int $sliderId): ?string
    {
        if (empty($_FILES['image']['tmp_name']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
            return null;
        }
        $file = $_FILES['image'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, self::ALLOWED_TYPES, true) || $file['size'] > self::MAX_IMAGE_SIZE) {
            return null;
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
        $filename = ($sliderId ?? 'new') . '_' . preg_replace('/[^a-z0-9]/', '', (string) microtime(true)) . '.' . $ext;
        $path = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $path)) {
            return null;
        }
        return self::UPLOAD_DIR . '/' . $filename;
    }
}
