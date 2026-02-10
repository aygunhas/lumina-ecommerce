<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreatePasswordResetTokensTable extends Migration
{
    public function getDescription(): string
    {
        return 'password_reset_tokens tablosunu oluşturur (şifre sıfırlama)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS password_reset_tokens (
                email VARCHAR(255) NOT NULL,
                token VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS password_reset_tokens');
    }
}
