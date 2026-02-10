<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateAdminNotificationsTable extends Migration
{
    public function getDescription(): string
    {
        return 'admin_notifications tablosunu oluşturur (admin bildirim sistemi)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS admin_notifications (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                message TEXT,
                link VARCHAR(255) DEFAULT NULL COMMENT "Tıklayınca gideceği yer (örn: /admin/orders/show?id=1)",
                is_read TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY is_read (is_read),
                KEY created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS admin_notifications');
    }
}
