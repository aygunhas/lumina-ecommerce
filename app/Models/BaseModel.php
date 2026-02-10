<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Tüm Model sınıflarının extend edeceği base sınıf
 * PDO bağlantısı ve temel CRUD metodları sağlar
 */
abstract class BaseModel
{
    /**
     * PDO bağlantısını döndürür
     */
    protected static function getConnection(): PDO
    {
        return Database::getConnection();
    }

    /**
     * Tablo adını döndürür (her model kendi tablosunu tanımlar)
     */
    abstract protected static function getTableName(): string;

    /**
     * Tekil kayıt bulma (ID ile)
     * 
     * @param int $id Kayıt ID'si
     * @return array|null Bulunan kayıt veya null
     */
    public static function find(int $id): ?array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM ' . static::getTableName() . ' WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Tüm kayıtları getirir
     * 
     * @param array $conditions WHERE koşulları (örn: ['is_active' => 1])
     * @param string $orderBy Sıralama (örn: 'created_at DESC')
     * @param int|null $limit Limit
     * @return array Kayıtlar
     */
    public static function all(array $conditions = [], string $orderBy = '', ?int $limit = null): array
    {
        $pdo = self::getConnection();
        $sql = 'SELECT * FROM ' . static::getTableName();
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = $key . ' = ?';
                $params[] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if ($orderBy !== '') {
            $sql .= ' ORDER BY ' . $orderBy;
        }

        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int) $limit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Yeni kayıt oluşturur
     * 
     * @param array $data Kayıt verileri
     * @return int Oluşturulan kaydın ID'si
     */
    public static function create(array $data): int
    {
        $pdo = self::getConnection();
        $fields = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $fieldNames = implode(', ', $fields);

        $sql = 'INSERT INTO ' . static::getTableName() . ' (' . $fieldNames . ') VALUES (' . $placeholders . ')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) $pdo->lastInsertId();
    }

    /**
     * Kayıt günceller
     * 
     * @param int $id Kayıt ID'si
     * @param array $data Güncellenecek veriler
     * @return bool Başarılı ise true
     */
    public static function update(int $id, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $pdo = self::getConnection();
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = $key . ' = ?';
            $params[] = $value;
        }

        $params[] = $id;
        $sql = 'UPDATE ' . static::getTableName() . ' SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Kayıt siler (soft delete için is_active = 0 yapabilirsiniz)
     * 
     * @param int $id Kayıt ID'si
     * @return bool Başarılı ise true
     */
    public static function delete(int $id): bool
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('DELETE FROM ' . static::getTableName() . ' WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Koşula göre tek kayıt bulur
     * 
     * @param array $conditions WHERE koşulları (örn: ['slug' => 'test-urun'])
     * @return array|null Bulunan kayıt veya null
     */
    public static function findBy(array $conditions): ?array
    {
        $pdo = self::getConnection();
        $where = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            $where[] = $key . ' = ?';
            $params[] = $value;
        }

        $sql = 'SELECT * FROM ' . static::getTableName() . ' WHERE ' . implode(' AND ', $where) . ' LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Koşula göre kayıt sayısını döndürür
     * 
     * @param array $conditions WHERE koşulları
     * @return int Kayıt sayısı
     */
    public static function count(array $conditions = []): int
    {
        $pdo = self::getConnection();
        $sql = 'SELECT COUNT(*) FROM ' . static::getTableName();
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = $key . ' = ?';
                $params[] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
