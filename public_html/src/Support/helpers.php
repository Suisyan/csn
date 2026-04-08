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

function special_member_status_label(string $status): string
{
    return match ($status) {
        'docs_pending' => '名刺アップ待',
        'pending' => '申請中',
        'approved' => '承認',
        'rejected' => '却下',
        default => $status !== '' ? $status : '未申請',
    };
}

function account_special_status(?array $user): string
{
    if ($user === null) {
        return '未申請';
    }

    $memberType = (string) ($user['member_type'] ?? '');
    $bizStatus = (string) ($user['biz_status'] ?? '');

    if ($memberType === 'biz' && $bizStatus === 'approved') {
        return special_member_status_label('approved');
    }

    if (in_array($bizStatus, ['docs_pending', 'pending', 'rejected'], true)) {
        return special_member_status_label($bizStatus);
    }

    return '未申請';
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
        return '特別会員状態: 承認。特別会員価格が有効です。';
    }

    if ($bizStatus === 'docs_pending') {
        return '特別会員状態: 名刺アップ待。ログイン後に名刺画像をアップロードしてください。';
    }

    if ($bizStatus === 'pending') {
        return '特別会員状態: 申請中。審査待ちです。';
    }

    if ($bizStatus === 'rejected') {
        return '特別会員状態: 却下。名刺画像を再アップロードしてください。';
    }

    if ($memberType === 'net') {
        return '特別会員状態: 未申請。会員価格が有効です。必要に応じて特別会員申請を行えます。';
    }

    return '非会員購入は利用できますが、ログインは会員アカウントのみです。';
}

function account_summary_html(?array $user): string
{
    if ($user === null) {
        return e(account_summary($user));
    }

    $memberType = (string) ($user['member_type'] ?? '');
    $bizStatus = (string) ($user['biz_status'] ?? '');

    if ($memberType === 'biz' && $bizStatus === 'approved') {
        return e('特別会員状態: 承認。特別会員価格が有効です。');
    }

    if ($bizStatus === 'docs_pending') {
        return '特別会員状態: 名刺アップ待。ログイン後に<a href="/special-member/upload">名刺画像をアップロードしてください</a>。';
    }

    if ($bizStatus === 'rejected') {
        return '特別会員状態: 却下。<a href="/special-member/upload">名刺画像を再アップロードしてください</a>。';
    }

    return e(account_summary($user));
}
