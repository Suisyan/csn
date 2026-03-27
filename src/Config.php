<?php

declare(strict_types=1);

namespace App;

final class Config
{
    private static array $values = [];

    public static function boot(string $basePath): void
    {
        self::$values = [
            'base_path' => $basePath,
        ];

        $envPath = $basePath . '/.env';
        if (!is_file($envPath)) {
            return;
        }

        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if ($line === '' || str_starts_with(trim($line), '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            self::$values[trim($key)] = trim(trim($value), "\"'");
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$values[$key] ?? $default;
    }
}
