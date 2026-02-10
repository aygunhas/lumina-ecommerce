<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

/**
 * Migration: migrations tablosunu oluşturur
 * 
 * Bu migration otomatik olarak MigrationRunner tarafından oluşturulur
 * Ancak manuel olarak da çalıştırılabilir
 */
class CreateMigrationsTable extends Migration
{
    public function getDescription(): string
    {
        return 'migrations tablosunu oluşturur';
    }

    public function up(): void
    {
        $this->execute('
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

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS migrations');
    }
}
