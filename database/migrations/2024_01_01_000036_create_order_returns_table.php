<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateOrderReturnsTable extends Migration
{
    public function getDescription(): string
    {
        return 'order_returns tablosunu oluşturur (iade)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS order_returns (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                order_id INT UNSIGNED NOT NULL,
                reason VARCHAR(255) DEFAULT NULL,
                status ENUM("pending","approved","rejected","completed") NOT NULL DEFAULT "pending",
                admin_note TEXT,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY order_id (order_id),
                CONSTRAINT order_returns_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS order_returns');
    }
}
