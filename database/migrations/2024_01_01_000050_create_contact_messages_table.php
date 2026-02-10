<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateContactMessagesTable extends Migration
{
    public function getDescription(): string
    {
        return 'contact_messages tablosunu oluşturur (iletişim formu mesajları)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS contact_messages (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(20) DEFAULT NULL,
                subject VARCHAR(255) DEFAULT NULL,
                message TEXT NOT NULL,
                is_read TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY is_read (is_read),
                KEY created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS contact_messages');
    }
}
