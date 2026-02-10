<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateAdminRolesTable extends Migration
{
    public function getDescription(): string
    {
        return 'admin_roles tablosunu oluşturur';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS admin_roles (
                id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(50) NOT NULL,
                slug VARCHAR(50) NOT NULL,
                description VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS admin_roles');
    }
}
