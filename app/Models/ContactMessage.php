<?php

declare(strict_types=1);

namespace App\Models;

/**
 * ContactMessage Model - İletişim mesajları için Model sınıfı
 */
class ContactMessage extends BaseModel
{
    protected static function getTableName(): string
    {
        return 'contact_messages';
    }

    /**
     * Yeni iletişim mesajı oluşturur
     * 
     * @param array $data Mesaj verileri (name, email, phone, subject, message)
     * @return int Oluşturulan mesajın ID'si
     */
    public static function create(array $data): int
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO contact_messages (name, email, phone, subject, message, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ');
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'] ?? null,
            $data['subject'] ?? null,
            $data['message']
        ]);
        
        return (int) $pdo->lastInsertId();
    }
}
