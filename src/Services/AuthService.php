<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;

final class AuthService
{
    public function attempt(string $email, string $password): bool
    {
        if ($email === '' || $password === '') {
            return false;
        }

        $user = (new UserRepository())->findByEmail($email);
        if ($user === null) {
            return false;
        }

        return password_verify($password, (string) $user['password_hash']);
    }

    public static function makePasswordHash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
