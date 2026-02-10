<?php

declare(strict_types=1);

namespace App\Database;

use App\Config\Database;
use PDO;
use ReflectionClass;

/**
 * Migration Runner - Migration'ları çalıştırır ve yönetir
 */
class MigrationRunner
{
    private PDO $pdo;
    private string $migrationsPath;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->migrationsPath = BASE_PATH . '/database/migrations';
        $this->ensureMigrationsTable();
    }

    /**
     * migrations tablosunu oluşturur (yoksa)
     */
    private function ensureMigrationsTable(): void
    {
        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT UNSIGNED NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_migration (migration),
                INDEX idx_batch (batch)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    /**
     * Çalıştırılmamış migration'ları bulur
     * 
     * @return array Migration dosya isimleri
     */
    public function getPendingMigrations(): array
    {
        $allMigrations = $this->getAllMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        
        return array_diff($allMigrations, $executedMigrations);
    }

    /**
     * Tüm migration dosyalarını getirir
     * 
     * @return array Migration dosya isimleri (sıralı)
     */
    private function getAllMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '/*.php');
        $migrations = [];
        
        foreach ($files as $file) {
            $filename = basename($file, '.php');
            // Dosya adı formatı: YYYY_MM_DD_HHMMSS_migration_name.php
            if (preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_/', $filename)) {
                $migrations[] = $filename;
            }
        }
        
        sort($migrations);
        return $migrations;
    }

    /**
     * Çalıştırılmış migration'ları getirir
     * 
     * @return array Migration isimleri
     */
    private function getExecutedMigrations(): array
    {
        $stmt = $this->pdo->query('SELECT migration FROM migrations ORDER BY id ASC');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Migration'ı çalıştırır
     * 
     * @param string $migrationName Migration dosya adı (uzantısız)
     * @param int $batch Batch numarası
     * @return bool Başarılı ise true
     */
    public function runMigration(string $migrationName, int $batch): bool
    {
        $filePath = $this->migrationsPath . '/' . $migrationName . '.php';
        
        if (!file_exists($filePath)) {
            echo "❌ Migration dosyası bulunamadı: $migrationName\n";
            return false;
        }

        require_once $filePath;
        
        // Sınıf adını bul (dosya adından)
        $className = $this->getClassNameFromFileName($migrationName);
        
        if (!class_exists($className)) {
            echo "❌ Migration sınıfı bulunamadı: $className\n";
            return false;
        }

        try {
            $reflection = new ReflectionClass($className);
            if (!$reflection->isSubclassOf('App\\Database\\Migration')) {
                echo "❌ $className Migration sınıfını extend etmiyor\n";
                return false;
            }

            $migration = $reflection->newInstance();
            
            echo "▶️  Çalıştırılıyor: {$migration->getDescription()}...\n";
            
            $this->pdo->beginTransaction();
            $migration->up();
            
            // Migration kaydını ekle
            $stmt = $this->pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
            $stmt->execute([$migrationName, $batch]);
            
            $this->pdo->commit();
            echo "✅ Tamamlandı: {$migration->getDescription()}\n";
            
            return true;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            echo "❌ Hata: {$e->getMessage()}\n";
            echo "   Dosya: {$e->getFile()}:{$e->getLine()}\n";
            return false;
        }
    }

    /**
     * Migration'ı geri alır (rollback)
     * 
     * @param string $migrationName Migration dosya adı
     * @return bool Başarılı ise true
     */
    public function rollbackMigration(string $migrationName): bool
    {
        $filePath = $this->migrationsPath . '/' . $migrationName . '.php';
        
        if (!file_exists($filePath)) {
            echo "❌ Migration dosyası bulunamadı: $migrationName\n";
            return false;
        }

        require_once $filePath;
        
        $className = $this->getClassNameFromFileName($migrationName);
        
        if (!class_exists($className)) {
            echo "❌ Migration sınıfı bulunamadı: $className\n";
            return false;
        }

        try {
            $reflection = new ReflectionClass($className);
            $migration = $reflection->newInstance();
            
            echo "▶️  Geri alınıyor: {$migration->getDescription()}...\n";
            
            $this->pdo->beginTransaction();
            $migration->down();
            
            // Migration kaydını sil
            $stmt = $this->pdo->prepare('DELETE FROM migrations WHERE migration = ?');
            $stmt->execute([$migrationName]);
            
            $this->pdo->commit();
            echo "✅ Geri alındı: {$migration->getDescription()}\n";
            
            return true;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            echo "❌ Hata: {$e->getMessage()}\n";
            echo "   Dosya: {$e->getFile()}:{$e->getLine()}\n";
            return false;
        }
    }

    /**
     * Dosya adından sınıf adını çıkarır
     * 
     * @param string $fileName Dosya adı (uzantısız)
     * @return string Tam sınıf adı (namespace ile)
     */
    private function getClassNameFromFileName(string $fileName): string
    {
        // Dosya adı formatı: 2024_01_01_120000_create_users_table
        // Sınıf adı: App\Database\Migrations\CreateUsersTable
        $parts = explode('_', $fileName);
        // İlk 4 kısmı tarih/saat, geri kalanı sınıf adı
        $nameParts = array_slice($parts, 4);
        $className = '';
        foreach ($nameParts as $part) {
            $className .= ucfirst($part);
        }
        return 'App\\Database\\Migrations\\' . $className;
    }

    /**
     * Tüm bekleyen migration'ları çalıştırır
     * 
     * @return int Çalıştırılan migration sayısı
     */
    public function run(): int
    {
        $pending = $this->getPendingMigrations();
        
        if (empty($pending)) {
            echo "✅ Çalıştırılacak migration yok.\n";
            return 0;
        }

        // Son batch numarasını al
        $stmt = $this->pdo->query('SELECT MAX(batch) FROM migrations');
        $lastBatch = (int) $stmt->fetchColumn();
        $currentBatch = $lastBatch + 1;

        echo "📦 " . count($pending) . " migration bulundu. Batch: $currentBatch\n\n";

        $count = 0;
        foreach ($pending as $migration) {
            if ($this->runMigration($migration, $currentBatch)) {
                $count++;
            } else {
                echo "\n⚠️  Migration durduruldu.\n";
                break;
            }
        }

        echo "\n✅ Toplam $count migration çalıştırıldı.\n";
        return $count;
    }

    /**
     * Son batch'i geri alır
     * 
     * @return int Geri alınan migration sayısı
     */
    public function rollback(): int
    {
        // Son batch'i bul
        $stmt = $this->pdo->query('SELECT MAX(batch) FROM migrations');
        $lastBatch = (int) $stmt->fetchColumn();
        
        if ($lastBatch === 0) {
            echo "✅ Geri alınacak migration yok.\n";
            return 0;
        }

        // Son batch'teki migration'ları getir (ters sırada)
        $stmt = $this->pdo->prepare('SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC');
        $stmt->execute([$lastBatch]);
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo "📦 Batch $lastBatch'teki " . count($migrations) . " migration geri alınacak.\n\n";

        $count = 0;
        foreach ($migrations as $migration) {
            if ($this->rollbackMigration($migration)) {
                $count++;
            } else {
                echo "\n⚠️  Rollback durduruldu.\n";
                break;
            }
        }

        echo "\n✅ Toplam $count migration geri alındı.\n";
        return $count;
    }

    /**
     * Migration durumunu gösterir
     */
    public function status(): void
    {
        $allMigrations = $this->getAllMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        $pendingMigrations = array_diff($allMigrations, $executedMigrations);

        echo "📊 Migration Durumu\n";
        echo str_repeat('=', 60) . "\n\n";
        echo "✅ Çalıştırılmış: " . count($executedMigrations) . "\n";
        echo "⏳ Bekleyen: " . count($pendingMigrations) . "\n";
        echo "📁 Toplam: " . count($allMigrations) . "\n\n";

        if (!empty($executedMigrations)) {
            echo "Çalıştırılmış Migration'lar:\n";
            foreach ($executedMigrations as $migration) {
                echo "  ✅ $migration\n";
            }
            echo "\n";
        }

        if (!empty($pendingMigrations)) {
            echo "Bekleyen Migration'lar:\n";
            foreach ($pendingMigrations as $migration) {
                echo "  ⏳ $migration\n";
            }
        }
    }
}
