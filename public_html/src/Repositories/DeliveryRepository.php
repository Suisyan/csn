<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;
use PDOException;

final class DeliveryRepository
{
    private ?array $columns = null;

    public function listByUserId(int $userId): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || !$this->isAvailable()) {
            return [];
        }

        $statement = $pdo->prepare(
            'SELECT *
             FROM delivery
             WHERE acc_id = :acc_id
               AND COALESCE(deliv_flag, 1) = 1
             ORDER BY deliv_id DESC'
        );
        $statement->execute([':acc_id' => $userId]);

        return $statement->fetchAll() ?: [];
    }

    public function saveForUser(int $userId, array $data): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || !$this->isAvailable()) {
            return false;
        }

        $deliveryId = (int) ($data['deliv_id'] ?? 0);
        $payload = [
            ':acc_id' => $userId,
            ':deliv_shop' => trim((string) ($data['deliv_shop'] ?? '')),
            ':deliv_person' => trim((string) ($data['deliv_person'] ?? '')),
            ':deliv_zip' => trim((string) ($data['deliv_zip'] ?? '')),
            ':deliv_add' => trim((string) ($data['deliv_add'] ?? '')),
            ':deliv_tel' => preg_replace('/[^0-9]/', '', (string) ($data['deliv_tel'] ?? '')) ?? '',
        ];

        if ($deliveryId > 0) {
            $sql = <<<SQL
                UPDATE delivery
                SET deliv_shop = :deliv_shop,
                    deliv_person = :deliv_person,
                    deliv_zip = :deliv_zip,
                    deliv_add = :deliv_add,
                    deliv_tel = :deliv_tel
                WHERE deliv_id = :deliv_id
                  AND acc_id = :acc_id
                LIMIT 1
            SQL;
            $payload[':deliv_id'] = $deliveryId;

            $statement = $pdo->prepare($sql);

            return $statement->execute($payload);
        }

        $sql = <<<SQL
            INSERT INTO delivery (
                acc_id,
                deliv_shop,
                deliv_person,
                deliv_zip,
                deliv_add,
                deliv_tel,
                deliv_flag
            ) VALUES (
                :acc_id,
                :deliv_shop,
                :deliv_person,
                :deliv_zip,
                :deliv_add,
                :deliv_tel,
                1
            )
        SQL;

        $statement = $pdo->prepare($sql);

        return $statement->execute($payload);
    }

    public function deleteForUser(int $userId, int $deliveryId): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || !$this->isAvailable()) {
            return false;
        }

        $statement = $pdo->prepare(
            'DELETE FROM delivery
             WHERE deliv_id = :deliv_id
               AND acc_id = :acc_id
             LIMIT 1'
        );

        return $statement->execute([
            ':deliv_id' => $deliveryId,
            ':acc_id' => $userId,
        ]);
    }

    public function isAvailable(): bool
    {
        return $this->hasTable('delivery')
            && $this->hasColumn('deliv_id')
            && $this->hasColumn('acc_id')
            && $this->hasColumn('deliv_shop')
            && $this->hasColumn('deliv_person')
            && $this->hasColumn('deliv_zip')
            && $this->hasColumn('deliv_add')
            && $this->hasColumn('deliv_tel');
    }

    private function hasTable(string $table): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $statement = $pdo->prepare('SHOW TABLES LIKE :table_name');
            $statement->execute([':table_name' => $table]);
        } catch (PDOException) {
            return false;
        }

        return (bool) $statement->fetchColumn();
    }

    private function hasColumn(string $column): bool
    {
        return in_array($column, $this->columns(), true);
    }

    private function columns(): array
    {
        if (is_array($this->columns)) {
            return $this->columns;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            $this->columns = [];

            return $this->columns;
        }

        try {
            $rows = $pdo->query('SHOW COLUMNS FROM delivery')->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $this->columns = [];

            return $this->columns;
        }

        $this->columns = array_map(
            static fn (array $row): string => (string) ($row['Field'] ?? ''),
            $rows
        );

        return $this->columns;
    }
}
