<?php

declare(strict_types=1);

namespace App\Controllers;

final class AdminAuthController
{
    public function showLogin(): void
    {
        if (current_admin() !== null) {
            header('Location: /admin');
            exit;
        }

        echo render('layout', [
            'title' => '管理画面ログイン',
            'content' => render('admin_login', [
                'error' => null,
                'redirectTo' => $this->sanitizeRedirect((string) ($_GET['redirect'] ?? '/admin')),
            ]),
        ]);
    }

    public function login(): void
    {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $redirectTo = $this->sanitizeRedirect((string) ($_POST['redirect_to'] ?? '/admin'));

        $expectedUser = trim((string) config('ADMIN_USER', ''));
        $expectedPass = (string) config('ADMIN_PASS', '');

        $error = null;
        if ($expectedUser === '' || $expectedPass === '') {
            $error = '管理画面の認証情報が未設定です。';
        } elseif ($username === '' || $password === '') {
            $error = 'ユーザー名とパスワードを入力してください。';
        } elseif (!hash_equals($expectedUser, $username) || !hash_equals($expectedPass, $password)) {
            $error = 'ユーザー名またはパスワードが正しくありません。';
        }

        if ($error !== null) {
            echo render('layout', [
                'title' => '管理画面ログイン',
                'content' => render('admin_login', [
                    'error' => $error,
                    'redirectTo' => $redirectTo,
                ]),
            ]);

            return;
        }

        session_regenerate_id(true);
        $_SESSION['admin_auth'] = [
            'username' => $username,
            'logged_in_at' => date('Y-m-d H:i:s'),
        ];

        header('Location: ' . $redirectTo);
        exit;
    }

    public function logout(): void
    {
        unset($_SESSION['admin_auth']);
        session_regenerate_id(true);

        header('Location: /admin/login');
        exit;
    }

    private function sanitizeRedirect(string $path): string
    {
        if ($path === '' || !str_starts_with($path, '/admin')) {
            return '/admin';
        }

        return $path;
    }
}
