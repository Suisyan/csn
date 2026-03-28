<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "BOOTSTRAP_START\n";

require dirname(__DIR__) . '/src/bootstrap.php';

echo "BOOTSTRAP_OK\n";
echo 'APP_NAME=' . (string) config('APP_NAME', '') . "\n";
echo 'CURRENT_PATH=' . current_path() . "\n";
echo 'ROUTER_OK' . "\n";
