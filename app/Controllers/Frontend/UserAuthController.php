<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use App\Models\User;
use PDO;

/**
 * Mağaza: Üye kayıt, giriş, çıkış
 */
class UserAuthController extends FrontendBaseController
{
    public function registerForm(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->registerStore();
            return;
        }
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/hesabim');
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
            $this->redirect('/kayit');
        }
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/hesabim');
        }
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $acceptTerms = !empty($_POST['accept_terms']) && $_POST['accept_terms'] === '1';

        $errors = [];
        if ($email === '') {
            $errors['email'] = 'E-posta zorunludur.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Geçerli bir e-posta girin.';
        }
        if (strlen($password) < 8) {
            $errors['password'] = 'Şifre en az 8 karakter olmalıdır.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Şifre en az bir büyük harf içermelidir.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Şifre en az bir rakam içermelidir.';
        } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors['password'] = 'Şifre en az bir özel karakter içermelidir (!@#$% vb.).';
        }
        if (!$acceptTerms) {
            $errors['accept_terms'] = 'Üyelik Sözleşmesi ve Gizlilik Politikası\'nı kabul etmeniz gerekmektedir.';
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
            $this->redirect('/kayit');
        }

        // E-posta kontrolü
        if (User::findByEmail($email)) {
            $_SESSION['register_errors'] = ['email' => 'Bu e-posta adresi zaten kayıtlı.'];
            $_SESSION['register_old'] = $_POST;
            $this->redirect('/kayit');
        }

        // Yeni kullanıcı oluştur
        $userId = User::create([
            'email' => $email,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone ?: null
        ]);
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = trim($firstName . ' ' . $lastName);
        $this->redirect('/hesabim');
    }

    public function loginForm(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->loginSubmit();
            return;
        }
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/hesabim');
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
            $this->redirect('/giris');
        }
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/hesabim');
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
            $this->redirect('/giris');
        }

        $user = User::findActiveByEmail($email);
        if (!$user || (int) $user['is_active'] !== 1 || !User::verifyPassword($password, $user['password'])) {
            $_SESSION['login_errors'] = ['email' => 'E-posta veya şifre hatalı.'];
            $_SESSION['login_old'] = ['email' => $email];
            $this->redirect('/giris');
        }

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $firstName = trim($user['first_name'] ?? '');
        $_SESSION['toast_message'] = $firstName !== '' ? 'Hoş geldiniz, ' . $firstName . '!' : 'Hoş geldiniz!';
        $_SESSION['toast_type'] = 'success';
        $this->redirect('/');
    }

    public function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['user_name']);
        $this->redirect('/');
    }

    /** Şifremi unuttum – e-posta girişi (split screen, Alpine state form/success) */
    public function forgotPasswordForm(): void
    {
        $title = 'Şifremi Unuttum - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $errors = $_SESSION['forgot_errors'] ?? [];
        $old = $_SESSION['forgot_old'] ?? [];
        unset($_SESSION['forgot_errors'], $_SESSION['forgot_old']);
        if (!empty($_GET['sent'])) {
            $this->render('frontend/auth/forgot-password', compact('title', 'baseUrl', 'errors', 'old') + ['showSuccess' => true]);
            return;
        }
        $this->render('frontend/auth/forgot-password', compact('title', 'baseUrl', 'errors', 'old'));
    }

    /** Şifre sıfırlama – yeni şifre formu (e-posta linkinden token ile) */
    public function resetPasswordForm(): void
    {
        $title = 'Yeni Şifre - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $token = trim($_GET['token'] ?? '');
        $errors = $_SESSION['reset_errors'] ?? [];
        $old = $_SESSION['reset_old'] ?? [];
        unset($_SESSION['reset_errors'], $_SESSION['reset_old']);
        $this->render('frontend/auth/reset-password', compact('title', 'baseUrl', 'token', 'errors', 'old'));
    }

}
