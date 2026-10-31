<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
$checks = \Sebook\Environment::check();
if ($checks['result'] !== 2 || $checks['text'] !== 'AÇÃO') {
    fwrite(STDERR, "Falha na consulta SQLite ou na conversão UTF-8.\n");
    exit(1);
}
echo 'OK: PHP ' . $checks['php'] . ', PDO_SQLite ' . $checks['sqlite'] . ", mbstring e autoload PSR-4.\n";
