<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require $path;
    }
});

require __DIR__ . '/Support/helpers.php';

App\Config::boot(dirname(__DIR__));
date_default_timezone_set('Asia/Tokyo');
mb_internal_encoding('UTF-8');

function app_router(): App\Router
{
    static $router = null;

    if ($router instanceof App\Router) {
        return $router;
    }

    $router = new App\Router();

    return $router;
}
