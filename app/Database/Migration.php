<?php

declare(strict_types=1);

namespace App\Database;

use App\Config\Database;
use PDO;

/**
 * Migration Base Sınıfı
 * Tüm migration'lar bu sınıfı extend eder
 */
abstract class Migration
{
    /**
     * Migration'ı çalıştırır (up)
     * Her migration bu metodu implement etmelidir
     */
    abstract public function up(): void;

    /**
     * Migration'ı geri alır (down/rollback)
     * Her migration bu metodu implement etmelidir
     */
    abstract public function down(): void;

    /**
     * Migration'ın açıklaması
     * 
     * @return string Açıklama
     */
    abstract public function getDescription(): string;

    /**
     * PDO bağlantısını döndürür
     */
    protected function getConnection(): PDO
    {
        return Database::getConnection();
    }

    /**
     * SQL sorgusu çalıştırır
     * 
     * @param string $sql SQL sorgusu
     */
    protected function execute(string $sql): void
    {
        $pdo = $this->getConnection();
        $pdo->exec($sql);
    }

    /**
     * Birden fazla SQL sorgusu çalıştırır
     * 
     * @param array $sqls SQL sorguları
     */
    protected function executeMultiple(array $sqls): void
    {
        $pdo = $this->getConnection();
        foreach ($sqls as $sql) {
            $pdo->exec($sql);
        }
    }
}
