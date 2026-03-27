<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): ?PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = Config::get('DB_HOST');
        $name = Config::get('DB_NAME');
        $user = Config::get('DB_USER');
        $pass = Config::get('DB_PASS');
        $port = Config::get('DB_PORT', '3306');
        $charset = Config::get('DB_CHARSET', 'utf8mb4');

        if (!$host || !$name || !$user) {
            return null;
        }

        try {
            self::$connection = new PDO(
                "mysql:host={$host};port={$port};dbname={$name};charset={$charset}",
                (string) $user,
                (string) $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException) {
            return null;
        }

        return self::$connection;
    }
}
