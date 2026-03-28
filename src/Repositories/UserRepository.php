<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;
use PDOException;

final class UserRepository
{
    private ?array $accColumns = null;

    public function findActiveCandidatesByEmail(string $email): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $statement = $pdo->prepare($this->buildFindByEmailSql());
        $statement->execute([':email' => $email]);

        return $statement->fetchAll() ?: [];
    }

    public function createApplicant(array $data, string $passwordHash): ?int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = <<<SQL
            INSERT INTO acc (
                e_mail,
                pass,
                password_hash,
                u_name,
                u_shop,
                b_name,
                zip,
                add1,
                add2,
                add3,
                tel,
                state,
                member_type,
                biz_status
            ) VALUES (
                :email,
                '',
                :password_hash,
                :contact_name,
                :shop_name,
                :company_name,
                :zip,
                :address_line1,
                :address_line2,
                :address_line3,
                :tel,
                1,
                'net',
                'docs_pending'
            )
        SQL;

        $statement = $pdo->prepare($sql);
        $saved = $statement->execute([
            ':email' => $data['email'],
            ':password_hash' => $passwordHash,
            ':contact_name' => $data['contact_name'],
            ':shop_name' => $data['shop_name'],
            ':company_name' => $data['company_name'],
            ':zip' => $data['zip'],
            ':address_line1' => $data['address_line1'],
            ':address_line2' => $data['address_line2'],
            ':address_line3' => $data['address_line3'],
            ':tel' => $data['tel'],
        ]);

        if (!$saved) {
            return null;
        }

        return (int) $pdo->lastInsertId();
    }

    public function findById(int $userId): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $select = [
            'acc_id AS id',
            "COALESCE(NULLIF(u_name, ''), NULLIF(u_shop, ''), e_mail) AS name",
            'u_name',
            'u_shop',
            'b_name',
            'e_mail AS email',
            'zip',
            'add1',
            'add2',
            'add3',
            'tel',
            'state',
        ];

        $select[] = $this->hasAccColumn('password_hash') ? 'password_hash' : 'NULL AS password_hash';
        $select[] = $this->hasAccColumn('member_type') ? 'member_type' : 'NULL AS member_type';
        $select[] = $this->hasAccColumn('biz_status') ? 'biz_status' : 'NULL AS biz_status';

        $statement = $pdo->prepare(
            'SELECT ' . implode(', ', $select) . ' FROM acc WHERE acc_id = :acc_id LIMIT 1'
        );
        $statement->execute([':acc_id' => $userId]);
        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    public function upgradeCredentials(int $userId, string $passwordHash, string $memberType, string $bizStatus): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || !$this->hasAccColumn('password_hash')) {
            return;
        }

        $sets = ['password_hash = :password_hash'];
        $params = [
            ':password_hash' => $passwordHash,
            ':acc_id' => $userId,
        ];

        if ($this->hasAccColumn('member_type')) {
            $sets[] = 'member_type = :member_type';
            $params[':member_type'] = $memberType;
        }

        if ($this->hasAccColumn('biz_status')) {
            $sets[] = 'biz_status = :biz_status';
            $params[':biz_status'] = $bizStatus;
        }

        $statement = $pdo->prepare(
            'UPDATE acc SET ' . implode(', ', $sets) . ' WHERE acc_id = :acc_id LIMIT 1'
        );
        $statement->execute($params);
    }

    public function updateBizStatus(int $userId, string $bizStatus): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || !$this->hasAccColumn('biz_status')) {
            return;
        }

        $statement = $pdo->prepare(
            'UPDATE acc SET biz_status = :biz_status WHERE acc_id = :acc_id LIMIT 1'
        );
        $statement->execute([
            ':biz_status' => $bizStatus,
            ':acc_id' => $userId,
        ]);
    }

    public function promoteToSpecialMember(int $userId): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $statement = $pdo->prepare(
            'UPDATE acc
             SET state = 2,
                 member_type = :member_type,
                 biz_status = :biz_status
             WHERE acc_id = :acc_id
             LIMIT 1'
        );
        $statement->execute([
            ':member_type' => 'biz',
            ':biz_status' => 'approved',
            ':acc_id' => $userId,
        ]);
    }

    public function markSpecialMemberRejected(int $userId): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $statement = $pdo->prepare(
            'UPDATE acc
             SET state = 1,
                 member_type = :member_type,
                 biz_status = :biz_status
             WHERE acc_id = :acc_id
             LIMIT 1'
        );
        $statement->execute([
            ':member_type' => 'net',
            ':biz_status' => 'rejected',
            ':acc_id' => $userId,
        ]);
    }

    private function buildFindByEmailSql(): string
    {
        $select = [
            'acc_id AS id',
            "COALESCE(NULLIF(u_name, ''), NULLIF(u_shop, ''), e_mail) AS name",
            'e_mail AS email',
            'pass AS legacy_password',
            'state',
        ];

        $select[] = $this->hasAccColumn('password_hash') ? 'password_hash' : 'NULL AS password_hash';
        $select[] = $this->hasAccColumn('member_type') ? 'member_type' : 'NULL AS member_type';
        $select[] = $this->hasAccColumn('biz_status') ? 'biz_status' : 'NULL AS biz_status';

        return 'SELECT ' . implode(', ', $select)
            . ' FROM acc'
            . ' WHERE e_mail = :email'
            . ' AND state IN (1, 2)'
            . ' ORDER BY CASE state WHEN 2 THEN 0 WHEN 1 THEN 1 ELSE 9 END, acc_id DESC';
    }

    private function hasAccColumn(string $column): bool
    {
        return in_array($column, $this->accColumns(), true);
    }

    private function accColumns(): array
    {
        if (is_array($this->accColumns)) {
            return $this->accColumns;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            $this->accColumns = [];

            return $this->accColumns;
        }

        try {
            $rows = $pdo->query('SHOW COLUMNS FROM acc')->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $this->accColumns = [];

            return $this->accColumns;
        }

        $this->accColumns = array_map(
            static fn (array $row): string => (string) ($row['Field'] ?? ''),
            $rows
        );

        return $this->accColumns;
    }
}
