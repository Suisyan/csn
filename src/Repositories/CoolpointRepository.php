<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;
use PDOException;

final class CoolpointRepository
{
    private ?array $columns = null;

    private const STATE_LABELS = [
        'move' => '移行ポイント',
        'plus' => '加点ポイント',
        'minus' => '利用ポイント',
        'back' => '返却ポイント',
        'bonus' => 'ボーナスポイント',
        'del' => '失効ポイント',
        'can' => '注文キャンセル',
        'cancel' => '注文キャンセル',
    ];

    public function currentBalanceByUserId(int $userId): ?int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || !$this->isAvailable()) {
            return null;
        }

        $statement = $pdo->prepare(
            'SELECT SUM(cp_point) AS cp_total
             FROM coolpoint
             WHERE cp_acc_id = :acc_id'
        );
        $statement->execute([':acc_id' => $userId]);
        $row = $statement->fetch();

        return is_array($row) ? (int) ($row['cp_total'] ?? 0) : 0;
    }

    public function listRecentByUserId(int $userId, int $limit = 20): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || !$this->isAvailable()) {
            return [];
        }

        $statement = $pdo->prepare(
            'SELECT *
             FROM coolpoint
             WHERE cp_acc_id = :acc_id
               AND cp_state NOT IN ("can", "del", "cancel")
             ORDER BY cp_date DESC
             LIMIT :limit'
        );
        $statement->bindValue(':acc_id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':limit', max(1, min($limit, 50)), PDO::PARAM_INT);
        $statement->execute();

        $rows = $statement->fetchAll() ?: [];

        return array_map(function (array $row): array {
            $state = (string) ($row['cp_state'] ?? '');
            $row['state_label'] = self::STATE_LABELS[$state] ?? ($state !== '' ? $state : '未設定');
            $row['cp_point'] = (int) ($row['cp_point'] ?? 0);

            return $row;
        }, $rows);
    }

    public function isAvailable(): bool
    {
        return $this->hasTable('coolpoint')
            && $this->hasColumn('cp_acc_id')
            && $this->hasColumn('cp_point')
            && $this->hasColumn('cp_state')
            && $this->hasColumn('cp_date');
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
            $rows = $pdo->query('SHOW COLUMNS FROM coolpoint')->fetchAll(PDO::FETCH_ASSOC);
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
