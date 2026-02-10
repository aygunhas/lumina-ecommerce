#!/usr/bin/env php
<?php

/**
 * Migration CLI Aracı
 * 
 * Kullanım:
 *   php bin/migrate.php              - Bekleyen migration'ları çalıştır
 *   php bin/migrate.php status       - Migration durumunu göster
 *   php bin/migrate.php rollback     - Son batch'i geri al
 *   php bin/migrate.php run          - Bekleyen migration'ları çalıştır (varsayılan)
 */

require __DIR__ . '/../config/bootstrap.php';

use App\Database\MigrationRunner;

$command = $argv[1] ?? 'run';
$runner = new MigrationRunner();

switch ($command) {
    case 'run':
    case '':
        echo "🚀 Migration'lar çalıştırılıyor...\n\n";
        $runner->run();
        break;

    case 'rollback':
        echo "⏪ Son batch geri alınıyor...\n\n";
        $runner->rollback();
        break;

    case 'status':
        $runner->status();
        break;

    default:
        echo "❌ Geçersiz komut: $command\n\n";
        echo "Kullanım:\n";
        echo "  php bin/migrate.php              - Bekleyen migration'ları çalıştır\n";
        echo "  php bin/migrate.php status       - Migration durumunu göster\n";
        echo "  php bin/migrate.php rollback     - Son batch'i geri al\n";
        exit(1);
}
