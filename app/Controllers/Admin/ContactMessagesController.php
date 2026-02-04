<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;
use PDO;

/**
 * Admin: İletişim formundan gelen mesajları listeleme ve görüntüleme
 */
class ContactMessagesController extends AdminBaseController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('
            SELECT id, name, email, subject, is_read, created_at
            FROM contact_messages
            ORDER BY created_at DESC
            LIMIT 200
        ');
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $baseUrl = $this->baseUrl();
        $this->render('admin/contact_messages/index', [
            'pageTitle' => 'İletişim mesajları',
            'baseUrl' => $baseUrl,
            'messages' => $messages,
        ]);
    }

    public function show(): void
    {
        $baseUrl = $this->baseUrl();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/admin/contact-messages');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM contact_messages WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$message) {
            header('Location: ' . $baseUrl . '/admin/contact-messages');
            exit;
        }
        if (!(int) ($message['is_read'] ?? 0)) {
            $pdo->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$id]);
            $message['is_read'] = 1;
        }
        $this->render('admin/contact_messages/show', [
            'pageTitle' => 'Mesaj #' . $id,
            'baseUrl' => $baseUrl,
            'message' => $message,
        ]);
    }
}
