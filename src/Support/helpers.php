<?php

declare(strict_types=1);

use App\Config;
use App\View;

function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}

function render(string $template, array $data = []): string
{
    return View::render($template, $data);
}

function current_path(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = (string) parse_url($uri, PHP_URL_PATH);

    return $path === '' ? '/' : $path;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function old(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}
