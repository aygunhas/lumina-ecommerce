<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateOrderStatusHistoryTable extends Migration
{
    public function getDescription(): string
    {
        return 'order_status_history tablosunu oluşturur';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS order_status_history (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                order_id INT UNSIGNED NOT NULL,
                status VARCHAR(50) NOT NULL,
                note TEXT,
                created_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY order_id (order_id),
                CONSTRAINT order_status_history_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS order_status_history');
    }
}
