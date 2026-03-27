<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class UserRepository
{
    public function findByEmail(string $email): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $statement = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1');
        $statement->execute([':email' => $email]);
        $row = $statement->fetch();

        return $row ?: null;
    }
}
