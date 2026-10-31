<?php

declare(strict_types=1);

namespace Sebook;

use PDO;
use RuntimeException;

final class Environment
{
    /** @return array{php: string, sqlite: string, result: int, text: string} */
    public static function check(): array
    {
        if (!extension_loaded('pdo_sqlite') || !extension_loaded('mbstring')) {
            throw new RuntimeException('Habilite PDO_SQLite e mbstring no php.ini.');
        }
        $db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $row = $db->query('SELECT sqlite_version() AS version, 1 + 1 AS result')->fetch(PDO::FETCH_ASSOC);
        return [
            'php' => PHP_VERSION,
            'sqlite' => (string) $row['version'],
            'result' => (int) $row['result'],
            'text' => mb_strtoupper('ação', 'UTF-8'),
        ];
    }
}
