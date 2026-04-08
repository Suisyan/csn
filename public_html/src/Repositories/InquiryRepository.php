<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class InquiryRepository
{
    public function store(array $data): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        $sql = <<<SQL
            INSERT INTO inquiries (
                inquiry_date,
                name,
                email,
                tel,
                category,
                katasiki,
                parts_num,
                toc,
                message
            ) VALUES (
                NOW(),
                :name,
                :email,
                :tel,
                :category,
                :katasiki,
                :parts_num,
                :toc,
                :message
            )
        SQL;

        $statement = $pdo->prepare($sql);

        return $statement->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':tel' => $data['tel'],
            ':category' => $data['category'],
            ':katasiki' => $data['katasiki'],
            ':parts_num' => $data['parts_num'],
            ':toc' => $data['toc'],
            ':message' => $data['message'],
        ]);
    }
}
