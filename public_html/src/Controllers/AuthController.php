<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;

final class AuthController
{
    public function showLogin(): void
    {
        echo render('layout', [
            'title' => 'Login',
            'content' => render('login', [
                'error' => null,
                'success' => false,
                'user' => current_user(),
            ]),
        ]);
    }

    public function login(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $result = (new AuthService())->attempt($email, $password);
        $user = is_array($result['user'] ?? null) ? $result['user'] : null;

        if (($result['success'] ?? false) === true && $user !== null) {
            session_regenerate_id(true);
            $_SESSION['auth_user'] = $user;
        }

        echo render('layout', [
            'title' => 'Login',
            'content' => render('login', [
                'error' => $this->messageForError((string) ($result['error_code'] ?? '')),
                'success' => ($result['success'] ?? false) === true,
                'user' => $user ?? current_user(),
            ]),
        ]);
    }

    public function logout(): void
    {
        unset($_SESSION['auth_user']);
        session_regenerate_id(true);

        header('Location: /');
        exit;
    }

    private function messageForError(string $errorCode): ?string
    {
        return match ($errorCode) {
            'account_unavailable' => 'This account is not available right now.',
            'guest_login_disabled' => 'Guest records cannot log in. Please use a member account.',
            'missing_credentials' => 'Please enter both email and password.',
            'invalid_credentials' => 'Email or password is incorrect.',
            default => null,
        };
    }
}
