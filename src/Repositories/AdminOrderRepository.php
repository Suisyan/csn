<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;
use PDOException;

final class AdminOrderRepository
{
    private ?array $salesMasterColumns = null;
    private ?array $salesSubColumns = null;
    private ?array $deliveryColumns = null;
    private ?array $accColumns = null;

    public function countPending(): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || !$this->canReadOrders()) {
            return 0;
        }

        $statement = $pdo->query("SELECT COUNT(*) FROM sales_master WHERE COALESCE(mail_flag, '') = '0'");

        return (int) $statement->fetchColumn();
    }

    public function listPending(int $limit = 10): array
    {
        return $this->listByStatus('pending', $limit);
    }

    public function findOrderDetail(int $orderId): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $orderId <= 0 || !$this->hasTable('sales_master') || !$this->hasSalesMasterColumn('s_id')) {
            return null;
        }

        $memberSelect = 'NULL AS member_state';
        $memberJoin = '';

        if ($this->hasTable('acc') && $this->hasSalesMasterColumn('acc_id') && $this->hasAccColumn('acc_id') && $this->hasAccColumn('state')) {
            $memberSelect = 'acc.state AS member_state';
            $memberJoin = 'LEFT JOIN acc ON acc.acc_id = sales_master.acc_id';
        }

        $totalSelect = '0 AS total_amount';
        $totalJoin = '';

        if ($this->hasTable('sales_sub') && $this->hasSalesSubColumn('s_id') && $this->hasSalesSubColumn('ss_total')) {
            $totalSelect = 'COALESCE(totals.total_amount, 0) AS total_amount';
            $totalJoin = <<<SQL
                LEFT JOIN (
                    SELECT s_id, SUM(ss_total) AS total_amount
                    FROM sales_sub
                    GROUP BY s_id
                ) AS totals ON totals.s_id = sales_master.s_id
            SQL;
        }

        $sql = <<<SQL
            SELECT
                sales_master.*,
                {$memberSelect},
                {$totalSelect}
            FROM sales_master
            {$totalJoin}
            {$memberJoin}
            WHERE sales_master.s_id = :order_id
            LIMIT 1
        SQL;

        $statement = $pdo->prepare($sql);
        $statement->execute([':order_id' => $orderId]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            return null;
        }

        $row = $this->decorateRow($row);
        $row['member_label'] = match ((string) ($row['member_state'] ?? '')) {
            '2' => 'BIZ会員',
            '1' => 'NET会員',
            '0' => 'ゲスト',
            default => '-',
        };

        return $row;
    }

    public function findOrderLines(int $orderId): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $orderId <= 0 || !$this->hasSalesSubColumn('s_id')) {
            return [];
        }

        $statement = $pdo->prepare(
            'SELECT ss_id, parts_num, syamei2, qty, ss_price, ss_total
             FROM sales_sub
             WHERE s_id = :order_id
             ORDER BY ss_id ASC'
        );
        $statement->execute([':order_id' => $orderId]);

        return $statement->fetchAll() ?: [];
    }

    public function findOrderDelivery(int $orderId): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $orderId <= 0 || !$this->hasTable('delivery') || !$this->hasDeliveryColumn('s_id')) {
            return null;
        }

        $statement = $pdo->prepare(
            'SELECT deliv_person, deliv_shop, deliv_zip, deliv_add, deliv_tel
             FROM delivery
             WHERE s_id = :order_id
             LIMIT 1'
        );
        $statement->execute([':order_id' => $orderId]);
        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    private function listByStatus(string $status, int $limit): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || !$this->canReadOrders()) {
            return [];
        }

        $limit = max(1, min($limit, 50));

        $where = match ($status) {
            'pending' => "WHERE COALESCE(sales_master.mail_flag, '') = '0'",
            default => "WHERE COALESCE(sales_master.mail_flag, '') <> '9'",
        };

        $sql = <<<SQL
            SELECT
                sales_master.s_id,
                sales_master.acc_id,
                sales_master.s_date,
                sales_master.su_name,
                sales_master.su_shop,
                sales_master.u_email,
                sales_master.payment,
                sales_master.mail_flag,
                sales_master.delivery_time,
                sales_master.unsou,
                sales_master.yamato_num,
                sales_master.bank_dep,
                sales_master.paypal_state,
                sales_master.trance_id,
                COALESCE(totals.total_amount, 0) AS total_amount
            FROM sales_master
            LEFT JOIN (
                SELECT s_id, SUM(ss_total) AS total_amount
                FROM sales_sub
                GROUP BY s_id
            ) AS totals ON totals.s_id = sales_master.s_id
            {$where}
            ORDER BY sales_master.s_date DESC, sales_master.s_id DESC
            LIMIT :limit
        SQL;

        $statement = $pdo->prepare($sql);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        $rows = $statement->fetchAll() ?: [];

        return array_map(fn (array $row): array => $this->decorateRow($row), $rows);
    }

    private function decorateRow(array $row): array
    {
        $payment = (string) ($row['payment'] ?? '');
        $mailFlag = (string) ($row['mail_flag'] ?? '');
        $bankDep = (string) ($row['bank_dep'] ?? '');
        $paypalState = (string) ($row['paypal_state'] ?? '');

        $row['ordered_at_label'] = $this->formatDateTime((string) ($row['s_date'] ?? ''));
        $row['total_amount'] = (int) ($row['total_amount'] ?? 0);
        $row['payment_label'] = match ($payment) {
            'yamato' => '代引',
            'bank' => $bankDep === '1' ? '銀行振込 入金済' : ($bankDep === '2' ? '銀行振込 要確認' : '銀行振込 未確認'),
            'card' => $paypalState !== '' ? 'カード決済 ' . $paypalState : 'カード決済',
            default => $payment !== '' ? $payment : '未設定',
        };
        $row['shipment_label'] = match ($mailFlag) {
            '0', '' => '未完了',
            '9' => 'キャンセル',
            default => '発送済み',
        };
        $row['transport_label'] = (string) ($row['unsou'] ?? '') !== '' ? (string) $row['unsou'] : '未設定';
        $row['tracking_label'] = (string) ($row['yamato_num'] ?? '') !== '' ? (string) $row['yamato_num'] : '-';

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

    private function canReadOrders(): bool
    {
        return $this->hasTable('sales_master')
            && $this->hasTable('sales_sub')
            && $this->hasSalesMasterColumn('s_id')
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

    private function hasDeliveryColumn(string $column): bool
    {
        return in_array($column, $this->deliveryColumns(), true);
    }

    private function hasAccColumn(string $column): bool
    {
        return in_array($column, $this->accColumns(), true);
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

    private function deliveryColumns(): array
    {
        if (is_array($this->deliveryColumns)) {
            return $this->deliveryColumns;
        }

        $this->deliveryColumns = $this->fetchColumns('delivery');

        return $this->deliveryColumns;
    }

    private function accColumns(): array
    {
        if (is_array($this->accColumns)) {
            return $this->accColumns;
        }

        $this->accColumns = $this->fetchColumns('acc');

        return $this->accColumns;
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
