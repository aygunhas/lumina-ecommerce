<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * User Model - Kullanıcı işlemleri için Model sınıfı
 */
class User extends BaseModel
{
    protected static function getTableName(): string
    {
        return 'users';
    }

    /**
     * E-posta ile kullanıcı bulur
     * 
     * @param string $email E-posta adresi
     * @return array|null Bulunan kullanıcı veya null
     */
    public static function findByEmail(string $email): ?array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * E-posta ile aktif kullanıcı bulur (giriş için)
     * 
     * @param string $email E-posta adresi
     * @return array|null Bulunan kullanıcı veya null
     */
    public static function findActiveByEmail(string $email): ?array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('SELECT id, email, password, first_name, last_name, is_active FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Yeni kullanıcı oluşturur
     * 
     * @param array $data Kullanıcı verileri (email, password, first_name, last_name, phone)
     * @return int Oluşturulan kullanıcının ID'si
     */
    public static function create(array $data): int
    {
        $pdo = self::getConnection();
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare('
            INSERT INTO users (email, password, first_name, last_name, phone, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())
        ');
        $stmt->execute([
            $data['email'],
            $hash,
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null
        ]);
        
        return (int) $pdo->lastInsertId();
    }

    /**
     * Şifre doğrulama
     * 
     * @param string $password Düz metin şifre
     * @param string $hash Hash'lenmiş şifre
     * @return bool Doğru ise true
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
