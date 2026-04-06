<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;
use PDOException;

final class AdminMemberRepository
{
    private ?array $accColumns = null;
    private ?array $coolpointColumns = null;

    public function search(string $key = ''): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || !$this->hasTable('acc') || !$this->hasAccColumn('acc_id')) {
            return [];
        }

        $key = trim($key);
        $hasCoolpoint = $this->hasTable('coolpoint')
            && $this->hasCoolpointColumn('cp_acc_id')
            && $this->hasCoolpointColumn('cp_point');

        $select = [
            'acc.acc_id',
            $this->accSelect('u_name'),
            $this->accSelect('u_shop'),
            $this->accSelect('b_name'),
            $this->accSelect('zip'),
            $this->accSelect('add1'),
            $this->accSelect('add2'),
            $this->accSelect('add3'),
            $this->accSelect('tel'),
            $this->accSelect('e_mail'),
            $this->accSelect('state'),
            $this->accSelect('member_type'),
            $this->accSelect('biz_status'),
            $hasCoolpoint ? 'COALESCE(SUM(coolpoint.cp_point), 0) AS coolpoint_total' : '0 AS coolpoint_total',
        ];

        $sql = 'SELECT ' . implode(",\n                ", $select)
            . "\n            FROM acc";

        if ($hasCoolpoint) {
            $sql .= "\n            LEFT JOIN coolpoint ON coolpoint.cp_acc_id = acc.acc_id";
        }

        $baseConditions = [];
        $searchConditions = [];
        $params = [];

        if ($this->hasAccColumn('state')) {
            $baseConditions[] = 'acc.state IN (0, 1, 2)';
        }

        if ($key !== '') {
            $params[':like_key'] = '%' . $key . '%';

            foreach (['b_name', 'tel', 'u_name', 'u_shop', 'e_mail'] as $column) {
                if ($this->hasAccColumn($column)) {
                    $searchConditions[] = "acc.{$column} LIKE :like_key";
                }
            }

            $searchConditions[] = 'CAST(acc.acc_id AS CHAR) LIKE :like_key';
        }

        $where = [];
        if ($baseConditions !== []) {
            $where[] = implode(' AND ', $baseConditions);
        }
        if ($searchConditions !== []) {
            $where[] = '(' . implode(' OR ', $searchConditions) . ')';
        }

        if ($where !== []) {
            $sql .= "\n            WHERE " . implode(' AND ', $where);
        }

        $sql .= "\n            GROUP BY acc.acc_id\n            ORDER BY acc.acc_id DESC\n            LIMIT 100";

        try {
            $statement = $pdo->prepare($sql);
            $statement->execute($params);
        } catch (PDOException) {
            return [];
        }

        $rows = $statement->fetchAll() ?: [];

        return array_map(function (array $row): array {
            $state = (string) ($row['state'] ?? '');
            $row['member_label'] = match ($state) {
                '2' => 'BIZ会員',
                '1' => 'NET会員',
                '0' => 'ゲスト',
                '9' => '停止',
                default => '-',
            };
            $row['coolpoint_total'] = (int) ($row['coolpoint_total'] ?? 0);
            $row['address'] = trim(
                (string) ($row['add1'] ?? '')
                . ' '
                . (string) ($row['add2'] ?? '')
                . ' '
                . (string) ($row['add3'] ?? '')
            );

            return $row;
        }, $rows);
    }

    private function accSelect(string $column): string
    {
        return $this->hasAccColumn($column) ? "acc.{$column}" : "'' AS {$column}";
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

    private function hasAccColumn(string $column): bool
    {
        return in_array($column, $this->accColumns(), true);
    }

    private function hasCoolpointColumn(string $column): bool
    {
        return in_array($column, $this->coolpointColumns(), true);
    }

    private function accColumns(): array
    {
        if (is_array($this->accColumns)) {
            return $this->accColumns;
        }

        $this->accColumns = $this->fetchColumns('acc');

        return $this->accColumns;
    }

    private function coolpointColumns(): array
    {
        if (is_array($this->coolpointColumns)) {
            return $this->coolpointColumns;
        }

        $this->coolpointColumns = $this->fetchColumns('coolpoint');

        return $this->coolpointColumns;
    }

    private function fetchColumns(string $table): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $rows = $pdo->query('SHOW COLUMNS FROM ' . $table)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }

        return array_map(
            static fn (array $row): string => (string) ($row['Field'] ?? ''),
            $rows
        );
    }
}
