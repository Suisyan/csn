<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;

final class AuthController
{
    public function showLogin(): void
    {
        echo render('layout', [
            'title' => 'ログイン',
            'content' => render('login', [
                'error' => null,
                'success' => false,
            ]),
        ]);
    }

    public function login(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $ok = (new AuthService())->attempt($email, $password);

        echo render('layout', [
            'title' => 'ログイン',
            'content' => render('login', [
                'error' => $ok ? null : 'メールアドレスまたはパスワードが一致しません。',
                'success' => $ok,
            ]),
        ]);
    }
}
