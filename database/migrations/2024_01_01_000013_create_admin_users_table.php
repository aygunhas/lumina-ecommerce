<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateAdminUsersTable extends Migration
{
    public function getDescription(): string
    {
        return 'admin_users tablosunu oluşturur';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                role_id TINYINT UNSIGNED DEFAULT NULL,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                name VARCHAR(100) NOT NULL,
                remember_token VARCHAR(100) DEFAULT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                last_login_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY email (email),
                KEY role_id (role_id),
                KEY is_active (is_active),
                CONSTRAINT admin_users_role FOREIGN KEY (role_id) REFERENCES admin_roles (id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS admin_users');
    }
}
