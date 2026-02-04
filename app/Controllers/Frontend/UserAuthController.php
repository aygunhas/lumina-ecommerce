<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use PDO;

/**
 * Mağaza: Üye kayıt, giriş, çıkış
 */
class UserAuthController
{
    public function registerForm(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->registerStore();
            return;
        }
        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . $this->baseUrl() . '/hesabim');
            exit;
        }
        $title = 'Kayıt ol - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $errors = $_SESSION['register_errors'] ?? [];
        $old = $_SESSION['register_old'] ?? [];
        unset($_SESSION['register_errors'], $_SESSION['register_old']);
        $this->render('frontend/auth/register', compact('title', 'baseUrl', 'errors', 'old'));
    }

    private function registerStore(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->baseUrl() . '/kayit');
            exit;
        }
        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . $this->baseUrl() . '/hesabim');
            exit;
        }
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        $errors = [];
        if ($email === '') {
            $errors['email'] = 'E-posta zorunludur.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Geçerli bir e-posta girin.';
        }
        if (strlen($password) < 6) {
            $errors['password'] = 'Şifre en az 6 karakter olmalıdır.';
        }
        if ($firstName === '') {
            $errors['first_name'] = 'Ad zorunludur.';
        }
        if ($lastName === '') {
            $errors['last_name'] = 'Soyad zorunludur.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            $_SESSION['register_errors'] = $errors;
            $_SESSION['register_old'] = $_POST;
            header('Location: ' . $baseUrl . '/kayit');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['register_errors'] = ['email' => 'Bu e-posta adresi zaten kayıtlı.'];
            $_SESSION['register_old'] = $_POST;
            header('Location: ' . $baseUrl . '/kayit');
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO users (email, password, first_name, last_name, phone, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())')
            ->execute([$email, $hash, $firstName, $lastName, $phone ?: null]);
        $userId = (int) $pdo->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = trim($firstName . ' ' . $lastName);
        header('Location: ' . $baseUrl . '/hesabim');
        exit;
    }

    public function loginForm(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->loginSubmit();
            return;
        }
        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . $this->baseUrl() . '/hesabim');
            exit;
        }
        $title = 'Giriş yap - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $errors = $_SESSION['login_errors'] ?? [];
        $old = $_SESSION['login_old'] ?? [];
        $redirect = $_GET['redirect'] ?? '';
        unset($_SESSION['login_errors'], $_SESSION['login_old']);
        $this->render('frontend/auth/login', compact('title', 'baseUrl', 'errors', 'old', 'redirect'));
    }

    private function loginSubmit(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->baseUrl() . '/giris');
            exit;
        }
        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . $this->baseUrl() . '/hesabim');
            exit;
        }
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $redirect = trim($_POST['redirect'] ?? '/hesabim');

        $errors = [];
        if ($email === '') {
            $errors['email'] = 'E-posta zorunludur.';
        }
        if ($password === '') {
            $errors['password'] = 'Şifre zorunludur.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            $_SESSION['login_errors'] = $errors;
            $_SESSION['login_old'] = ['email' => $email];
            header('Location: ' . $baseUrl . '/giris');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, email, password, first_name, last_name, is_active FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || (int) $user['is_active'] !== 1 || !password_verify($password, $user['password'])) {
            $_SESSION['login_errors'] = ['email' => 'E-posta veya şifre hatalı.'];
            $_SESSION['login_old'] = ['email' => $email];
            header('Location: ' . $baseUrl . '/giris');
            exit;
        }

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $target = $redirect !== '' && strpos($redirect, '/') === 0 ? $redirect : '/hesabim';
        header('Location: ' . $baseUrl . $target);
        exit;
    }

    public function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['user_name']);
        header('Location: ' . $this->baseUrl() . '/');
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
}
