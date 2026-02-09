<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;

/**
 * Mağaza: İletişim sayfası ve iletişim formu
 */
class ContactController extends FrontendBaseController
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
        $this->render('frontend/contact/contact', compact('title', 'baseUrl', 'errors', 'old', 'success'));
    }

    private function submit(): void
    {
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
            $this->redirect('/iletisim');
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$name, $email, $phone ?: null, $subject ?: null, $message]);
        $_SESSION['contact_success'] = true;
        $this->redirect('/iletisim?sent=1');
    }
}
