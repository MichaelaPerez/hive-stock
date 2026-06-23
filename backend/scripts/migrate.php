<?php

use HiveStock\Config\Database;
use HiveStock\Config\Env;

require_once __DIR__ . '/../bootstrap.php';

Env::load(__DIR__ . '/../.env');

$pdo = Database::connection();
$migrationFiles = glob(__DIR__ . '/../migrations/*.sql') ?: [];
sort($migrationFiles);

foreach ($migrationFiles as $file) {
    echo 'Running ' . basename($file) . PHP_EOL;
    $sql = file_get_contents($file);

    if ($sql === false) {
        throw new RuntimeException('Unable to read migration: ' . $file);
    }

    $pdo->exec($sql);
}

echo 'Migrations complete.' . PHP_EOL;
