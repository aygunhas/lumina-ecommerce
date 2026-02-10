<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateSettingsTable extends Migration
{
    public function getDescription(): string
    {
        return 'settings tablosunu oluşturur (site ayarları)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS settings (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                group_name VARCHAR(50) DEFAULT NULL COMMENT "general, payment, email, stripe vb.",
                `key` VARCHAR(100) NOT NULL,
                value TEXT,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY group_key (group_name, `key`),
                KEY group_name (group_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS settings');
    }
}
