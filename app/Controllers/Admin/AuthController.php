<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;

/**
 * Admin giriş / çıkış
 */
class AuthController
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->loginPost();
            return;
        }
        $this->loginForm();
    }

    private function loginForm(): void
    {
        $error = $_SESSION['admin_login_error'] ?? null;
        unset($_SESSION['admin_login_error']);
        $baseUrl = $this->baseUrl();
        require BASE_PATH . '/app/Views/admin/login.php';
    }

    private function loginPost(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $_SESSION['admin_login_error'] = 'E-posta ve şifre girin.';
            header('Location: ' . $this->baseUrl() . '/admin/login');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, password FROM admin_users WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row || !password_verify($password, $row['password'])) {
            $_SESSION['admin_login_error'] = 'E-posta veya şifre hatalı.';
            header('Location: ' . $this->baseUrl() . '/admin/login');
            exit;
        }

        $_SESSION['admin_id'] = (int) $row['id'];
        $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?')->execute([$row['id']]);
        header('Location: ' . $this->baseUrl() . '/admin');
        exit;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: ' . $this->baseUrl() . '/admin/login');
        exit;
    }

    private function baseUrl(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = dirname($script);
        return ($base === '/' || $base === '\\') ? '' : $base;
    }
}
