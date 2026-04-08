<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;
use PDOException;

final class PurchaseHistoryRepository
{
    private ?array $salesMasterColumns = null;
    private ?array $salesSubColumns = null;

    public function listRecentByUserId(int $userId, int $limit = 10): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || !$this->canReadPurchaseHistory()) {
            return [];
        }

        $limit = max(1, min($limit, 30));

        $sql = <<<SQL
            SELECT
                sales_master.s_id AS order_id,
                sales_master.s_date AS ordered_at,
                totals.total_amount,
                sales_master.payment,
                sales_master.paypal_state,
                sales_master.bank_dep,
                sales_master.mail_flag
            FROM sales_master
            INNER JOIN (
                SELECT s_id, SUM(ss_total) AS total_amount
                FROM sales_sub
                GROUP BY s_id
            ) AS totals ON totals.s_id = sales_master.s_id
            WHERE sales_master.acc_id = :acc_id
              AND COALESCE(sales_master.mail_flag, '') <> '9'
            ORDER BY sales_master.s_date DESC, sales_master.s_id DESC
            LIMIT :limit
        SQL;

        $statement = $pdo->prepare($sql);
        $statement->bindValue(':acc_id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        $rows = $statement->fetchAll() ?: [];

        return array_map(fn (array $row): array => $this->decorateRow($row), $rows);
    }

    private function decorateRow(array $row): array
    {
        $payment = (string) ($row['payment'] ?? '');
        $paypalState = (string) ($row['paypal_state'] ?? '');
        $bankDep = (string) ($row['bank_dep'] ?? '');
        $mailFlag = (string) ($row['mail_flag'] ?? '');

        $paymentLabel = match ($payment) {
            'yamato' => '代金引換',
            'bank' => $bankDep === '0' ? '銀行振込待ち' : '銀行振込',
            'card' => $paypalState === '' ? 'カード決済処理中' : 'カード決済',
            default => $payment !== '' ? $payment : '未設定',
        };

        $shipmentLabel = match ($mailFlag) {
            '0' => '手配中',
            '' => '手配中',
            default => '発送済み',
        };

        $orderedAt = (string) ($row['ordered_at'] ?? '');

        $row['payment_label'] = $paymentLabel;
        $row['shipment_label'] = $shipmentLabel;
        $row['ordered_at_label'] = $this->formatDateTime($orderedAt);
        $row['total_amount'] = (int) ($row['total_amount'] ?? 0);

        return $row;
    }

    private function formatDateTime(string $value): string
    {
        if ($value === '') {
            return '日時未登録';
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $value;
        }

        return date('Y-m-d H:i', $timestamp);
    }

    private function canReadPurchaseHistory(): bool
    {
        return $this->hasTable('sales_master')
            && $this->hasTable('sales_sub')
            && $this->hasSalesMasterColumn('s_id')
            && $this->hasSalesMasterColumn('acc_id')
            && $this->hasSalesMasterColumn('s_date')
            && $this->hasSalesMasterColumn('payment')
            && $this->hasSalesMasterColumn('mail_flag')
            && $this->hasSalesSubColumn('s_id')
            && $this->hasSalesSubColumn('ss_total');
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

    private function hasSalesMasterColumn(string $column): bool
    {
        return in_array($column, $this->salesMasterColumns(), true);
    }

    private function hasSalesSubColumn(string $column): bool
    {
        return in_array($column, $this->salesSubColumns(), true);
    }

    private function salesMasterColumns(): array
    {
        if (is_array($this->salesMasterColumns)) {
            return $this->salesMasterColumns;
        }

        $this->salesMasterColumns = $this->fetchColumns('sales_master');

        return $this->salesMasterColumns;
    }

    private function salesSubColumns(): array
    {
        if (is_array($this->salesSubColumns)) {
            return $this->salesSubColumns;
        }

        $this->salesSubColumns = $this->fetchColumns('sales_sub');

        return $this->salesSubColumns;
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
