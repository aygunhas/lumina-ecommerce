<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateAddressesTable extends Migration
{
    public function getDescription(): string
    {
        return 'addresses tablosunu oluşturur (müşteri adresleri)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS addresses (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id INT UNSIGNED NOT NULL,
                title VARCHAR(50) DEFAULT NULL COMMENT "Ev, İş vb.",
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                city VARCHAR(100) NOT NULL,
                district VARCHAR(100) NOT NULL,
                address_line VARCHAR(255) NOT NULL,
                postal_code VARCHAR(20) DEFAULT NULL,
                is_default TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                CONSTRAINT addresses_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS addresses');
    }
}
