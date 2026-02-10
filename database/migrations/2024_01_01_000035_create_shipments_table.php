<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateShipmentsTable extends Migration
{
    public function getDescription(): string
    {
        return 'shipments tablosunu oluşturur (kargo takip)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS shipments (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                order_id INT UNSIGNED NOT NULL,
                carrier VARCHAR(100) DEFAULT NULL,
                tracking_number VARCHAR(255) DEFAULT NULL,
                shipped_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY order_id (order_id),
                CONSTRAINT shipments_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS shipments');
    }
}
