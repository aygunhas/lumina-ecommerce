<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateAdminPasswordResetTokensTable extends Migration
{
    public function getDescription(): string
    {
        return 'admin_password_reset_tokens tablosunu oluşturur';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS admin_password_reset_tokens (
                email VARCHAR(255) NOT NULL,
                token VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS admin_password_reset_tokens');
    }
}
