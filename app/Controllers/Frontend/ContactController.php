<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;

/**
 * Mağaza: İletişim sayfası ve iletişim formu
 */
class ContactController
{
    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->submit();
            return;
        }
        $title = 'İletişim - ' . (function_exists('env') ? env('APP_NAME', 'Lumina Boutique') : 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $errors = $_SESSION['contact_errors'] ?? [];
        $old = $_SESSION['contact_old'] ?? [];
        $success = !empty($_SESSION['contact_success']);
        unset($_SESSION['contact_errors'], $_SESSION['contact_old'], $_SESSION['contact_success']);
        $this->renderWithIncludesLayout('frontend/contact/contact', compact('title', 'baseUrl', 'errors', 'old', 'success'));
    }

    private function submit(): void
    {
        $baseUrl = $this->baseUrl();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Ad soyad zorunludur.';
        }
        if ($email === '') {
            $errors['email'] = 'E-posta zorunludur.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Geçerli bir e-posta adresi girin.';
        }
        if ($message === '') {
            $errors['message'] = 'Mesaj zorunludur.';
        }
        if (!empty($errors)) {
            $_SESSION['contact_old'] = $_POST;
            $_SESSION['contact_errors'] = $errors;
            header('Location: ' . $baseUrl . '/iletisim');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$name, $email, $phone ?: null, $subject ?: null, $message]);
        $_SESSION['contact_success'] = true;
        header('Location: ' . $baseUrl . '/iletisim?sent=1');
        exit;
    }

    private function baseUrl(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = dirname($script);
        return ($base === '/' || $base === '\\') ? '' : $base;
    }

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
