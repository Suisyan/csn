<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;
use PDOException;

final class AdminProductRepository
{
    private ?array $partsColumns = null;
    private ?array $modelColumns = null;
    private ?array $discountColumns = null;

    public function search(string $key = ''): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || !$this->hasTable('PARTS')) {
            return [];
        }

        $key = trim($key);
        $partsFields = $this->partsColumns();
        $modelFields = $this->modelColumns();
        $hasModel = $this->hasTable('MODEL');
        $hasDiscount = $this->hasTable('DISCOUNT') && $this->hasDiscountColumn('d_rate');

        $select = [
            $this->columnOrNull('PARTS', 'p_id') . ' AS p_id',
            $this->columnOrNull('PARTS', 'parts_num') . ' AS parts_num',
            $this->columnOrNull('PARTS', 'web_num') . ' AS web_num',
            $this->columnOrNull('PARTS', 'supp_num') . ' AS supp_num',
            $this->columnOrNull('PARTS', 'daiwa_num') . ' AS daiwa_num',
            $this->columnOrNull('PARTS', 'priceA') . ' AS price',
            $this->columnOrNull('PARTS', 'stock') . ' AS stock',
            $this->columnOrNull('PARTS', 'category') . ' AS category',
            $hasModel && in_array('katasiki', $modelFields, true) ? 'MIN(MODEL.katasiki) AS katasiki' : "'' AS katasiki",
            $hasModel && in_array('syamei1', $modelFields, true) ? 'MIN(MODEL.syamei1) AS make' : "'' AS make",
            $hasModel && in_array('syamei2', $modelFields, true) ? 'MIN(MODEL.syamei2) AS name' : "'' AS name",
            $hasModel && in_array('m_id', $modelFields, true) ? 'COUNT(DISTINCT MODEL.m_id) AS model_count' : '0 AS model_count',
            $hasDiscount ? 'FLOOR(COALESCE(PARTS.priceA, 0) * (1 - COALESCE(D1.d_rate, 0)) / 10) * 10 AS member_price' : 'COALESCE(PARTS.priceA, 0) AS member_price',
            $hasDiscount ? 'FLOOR(COALESCE(PARTS.priceA, 0) * (1 - COALESCE(D2.d_rate, 0)) / 10) * 10 AS special_price' : 'COALESCE(PARTS.priceA, 0) AS special_price',
        ];

        $joins = [];
        if ($hasModel && in_array('parts_num', $partsFields, true) && in_array('parts_num', $modelFields, true)) {
            $joins[] = 'LEFT JOIN MODEL ON MODEL.parts_num = PARTS.parts_num';
        }
        if ($hasDiscount) {
            $joins[] = 'LEFT JOIN DISCOUNT D1 ON D1.d_id = 1';
            $joins[] = 'LEFT JOIN DISCOUNT D2 ON D2.d_id = 2';
        }

        $conditions = [];
        $params = [];
        if ($key !== '') {
            $params[':like_key'] = '%' . $key . '%';
            $params[':key'] = $key;

            foreach (['parts_num', 'web_num', 'supp_num', 'daiwa_num'] as $column) {
                if (in_array($column, $partsFields, true)) {
                    $conditions[] = "PARTS.{$column} LIKE :like_key";
                }
            }

            if ($hasModel) {
                foreach (['katasiki', 'syamei1', 'syamei2'] as $column) {
                    if (in_array($column, $modelFields, true)) {
                        $conditions[] = "MODEL.{$column} LIKE :like_key";
                    }
                }
            }
        }

        $sql = 'SELECT ' . implode(",\n                ", $select)
            . "\n            FROM PARTS\n            "
            . implode("\n            ", $joins);

        if ($conditions !== []) {
            $sql .= "\n            WHERE (" . implode(' OR ', $conditions) . ')';
        }

        $sql .= "\n            GROUP BY "
            . implode(', ', [
                $this->columnOrNull('PARTS', 'p_id'),
                $this->columnOrNull('PARTS', 'parts_num'),
                $this->columnOrNull('PARTS', 'web_num'),
                $this->columnOrNull('PARTS', 'supp_num'),
                $this->columnOrNull('PARTS', 'daiwa_num'),
                $this->columnOrNull('PARTS', 'priceA'),
                $this->columnOrNull('PARTS', 'stock'),
                $this->columnOrNull('PARTS', 'category'),
            ])
            . "\n            ORDER BY COALESCE(PARTS.p_id, 0) DESC, PARTS.parts_num ASC\n            LIMIT 100";

        try {
            $statement = $pdo->prepare($sql);
            $statement->execute($params);
        } catch (PDOException) {
            return [];
        }

        $rows = $statement->fetchAll() ?: [];

        return array_map(function (array $row): array {
            $row['price'] = (int) ($row['price'] ?? 0);
            $row['member_price'] = (int) ($row['member_price'] ?? 0);
            $row['special_price'] = (int) ($row['special_price'] ?? 0);
            $row['stock'] = is_numeric((string) ($row['stock'] ?? '')) ? (int) $row['stock'] : null;
            $row['model_count'] = (int) ($row['model_count'] ?? 0);

            return $row;
        }, $rows);
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

    private function hasDiscountColumn(string $column): bool
    {
        return in_array($column, $this->discountColumns(), true);
    }

    private function partsColumns(): array
    {
        if (is_array($this->partsColumns)) {
            return $this->partsColumns;
        }

        $this->partsColumns = $this->fetchColumns('PARTS');

        return $this->partsColumns;
    }

    private function modelColumns(): array
    {
        if (is_array($this->modelColumns)) {
            return $this->modelColumns;
        }

        $this->modelColumns = $this->fetchColumns('MODEL');

        return $this->modelColumns;
    }

    private function discountColumns(): array
    {
        if (is_array($this->discountColumns)) {
            return $this->discountColumns;
        }

        $this->discountColumns = $this->fetchColumns('DISCOUNT');

        return $this->discountColumns;
    }

    private function columnOrNull(string $table, string $column): string
    {
        $fields = match ($table) {
            'PARTS' => $this->partsColumns(),
            'MODEL' => $this->modelColumns(),
            'DISCOUNT' => $this->discountColumns(),
            default => [],
        };

        return in_array($column, $fields, true) ? "{$table}.{$column}" : 'NULL';
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
