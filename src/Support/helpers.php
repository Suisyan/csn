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

    if ($path === '' || $path === '/') {
        return '/';
    }

    return rtrim($path, '/');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function old(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function current_user(): ?array
{
    $user = $_SESSION['auth_user'] ?? null;

    return is_array($user) ? $user : null;
}

function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        header('Location: /login');
        exit;
    }

    return $user;
}

function refresh_current_user(array $user): void
{
    $_SESSION['auth_user'] = $user;
}

function current_admin(): ?array
{
    $admin = $_SESSION['admin_auth'] ?? null;

    return is_array($admin) ? $admin : null;
}

function require_admin_login(): array
{
    $admin = current_admin();
    if ($admin === null) {
        $redirect = current_path();
        header('Location: /admin/login?redirect=' . urlencode($redirect));
        exit;
    }

    return $admin;
}

function storefront_home_url(): string
{
    return '/ra.php';
}

function account_label(?array $user): string
{
    if ($user === null) {
        return 'ゲスト';
    }

    $memberType = (string) ($user['member_type'] ?? '');
    $bizStatus = (string) ($user['biz_status'] ?? '');

    if ($memberType === 'biz' && $bizStatus === 'approved') {
        return '特別会員';
    }

    if ($bizStatus === 'docs_pending') {
        return '特別会員書類待ち';
    }

    if ($bizStatus === 'pending') {
        return '特別会員申請中';
    }

    if ($memberType === 'net') {
        return '会員';
    }

    return 'ゲスト';
}

function account_theme(?array $user): string
{
    if ($user === null) {
        return 'guest';
    }

    $memberType = (string) ($user['member_type'] ?? '');
    $bizStatus = (string) ($user['biz_status'] ?? '');

    if ($memberType === 'biz' && $bizStatus === 'approved') {
        return 'special';
    }

    if (in_array($bizStatus, ['docs_pending', 'pending'], true)) {
        return 'pending';
    }

    if ($memberType === 'net') {
        return 'member';
    }

    return 'guest';
}

function account_summary(?array $user): string
{
    if ($user === null) {
        return '非会員購入は利用できます。ログインは会員アカウントのみ利用できます。';
    }

    $memberType = (string) ($user['member_type'] ?? '');
    $bizStatus = (string) ($user['biz_status'] ?? '');

    if ($memberType === 'biz' && $bizStatus === 'approved') {
        return '特別会員価格が有効です。';
    }

    if ($bizStatus === 'docs_pending') {
        return '申請受付済みです。ログイン後に名刺画像をアップロードしてください。';
    }

    if ($bizStatus === 'pending') {
        return '特別会員申請を受付中です。審査待ちです。';
    }

    if ($memberType === 'net') {
        return '会員価格が有効です。特別会員申請を行えます。';
    }

    return '非会員購入は利用できますが、ログインは会員アカウントのみです。';
}
