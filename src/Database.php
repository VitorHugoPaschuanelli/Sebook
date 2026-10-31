<?php

declare(strict_types=1);

namespace Sebook;

use PDO;
use RuntimeException;

final class Database
{
    public static function connect(?string $path = null): PDO
    {
        $path ??= getenv('SEBOOK_DB_PATH') ?: dirname(__DIR__) . '/database/sebook.sqlite';
        $db = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $db->exec('PRAGMA foreign_keys = ON; PRAGMA busy_timeout = 5000;');
        $db->sqliteCreateFunction('fold', static fn(string $value): string => mb_strtolower($value, 'UTF-8'), 1);
        $version = (int) $db->query('PRAGMA user_version')->fetchColumn();
        if ($version > 1) {
            throw new RuntimeException('O banco foi criado por uma versão mais recente do Sebook.');
        }
        if ($version === 0) {
            $db->beginTransaction();
            try {
                $db->exec(file_get_contents(dirname(__DIR__) . '/database/schema.sql'));
                $db->commit();
            } catch (\Throwable $error) {
                $db->rollBack();
                throw $error;
            }
        }
        return $db;
    }
}
