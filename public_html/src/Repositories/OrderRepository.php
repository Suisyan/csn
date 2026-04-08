<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;
use PDOException;
use RuntimeException;

final class OrderRepository
{
    private ?array $salesMasterColumns = null;
    private ?array $salesSubColumns = null;
    private ?array $coolpointColumns = null;

    public function findOrderWithTotals(int $orderId): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $orderId <= 0 || !$this->hasTable('sales_master') || !$this->hasTable('sales_sub')) {
            return null;
        }

        $sql = <<<SQL
            SELECT
                sales_master.*,
                totals.total_amount
            FROM sales_master
            INNER JOIN (
                SELECT s_id, SUM(ss_total) AS total_amount
                FROM sales_sub
                GROUP BY s_id
            ) AS totals ON totals.s_id = sales_master.s_id
            WHERE sales_master.s_id = :order_id
            LIMIT 1
        SQL;

        $statement = $pdo->prepare($sql);
        $statement->execute([':order_id' => $orderId]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            return null;
        }

        $row['total_amount'] = (int) ($row['total_amount'] ?? 0);
        $row['shipto_zip'] = (string) ($row['u_zip'] ?? '');
        $row['shipto_state'] = '';
        $row['shipto_city'] = '';
        $row['shipto_street'] = (string) ($row['u_add'] ?? '');

        return $row;
    }

    public function findByPayPalToken(string $token): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $token === '' || !$this->hasSalesMasterColumn('token')) {
            return null;
        }

        $statement = $pdo->prepare('SELECT * FROM sales_master WHERE token = :token LIMIT 1');
        $statement->execute([':token' => $token]);
        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    public function validateStock(array $items, ?array $viewer = null): array
    {
        $products = new ProductRepository();
        $errors = [];
        $validated = [];

        foreach ($items as $productId => $qty) {
            $productId = (int) $productId;
            $requestedQty = max(0, (int) $qty);
            if ($productId <= 0 || $requestedQty <= 0) {
                continue;
            }

            $product = $products->findById($productId, $viewer);
            if ($product === null) {
                $errors[] = '商品データの取得に失敗したため、注文内容を確認してください。';
                continue;
            }

            $stock = max(0, (int) ($product['stock'] ?? 0));
            if ($stock <= 0) {
                $errors[] = (string) ($product['parts_num'] ?? '商品') . ' は現在ご注文いただけません。';
                continue;
            }

            if ($requestedQty > $stock) {
                $errors[] = (string) ($product['parts_num'] ?? '商品') . ' は在庫数を超えています。';
                continue;
            }

            $validated[] = [
                'product' => $product,
                'qty' => $requestedQty,
                'unit_price' => (int) ($product['display_price'] ?? 0),
                'subtotal' => (int) ($product['display_price'] ?? 0) * $requestedQty,
            ];
        }

        return [
            'errors' => $errors,
            'lines' => $validated,
        ];
    }

    public function create(array $order, array $lines): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            throw new RuntimeException('注文保存に必要なDB接続がありません。');
        }

        if (!$this->hasSalesMasterColumn('s_id') || !$this->hasSalesSubColumn('s_id')) {
            throw new RuntimeException('注文テーブル構成が不足しているため、注文を保存できません。');
        }

        $pdo->beginTransaction();

        try {
            $orderId = $this->insertSalesMaster($pdo, $order);
            foreach ($lines as $line) {
                $this->insertSalesLine($pdo, $orderId, $line, $order);
                $this->decrementStock($pdo, (string) ($line['product']['parts_num'] ?? ''), (int) ($line['qty'] ?? 0));
            }

            if ((int) ($order['shipping_fee'] ?? 0) > 0) {
                $this->insertFeeLine(
                    $pdo,
                    $orderId,
                    '999997',
                    '送料',
                    (int) $order['shipping_fee'],
                    $order
                );
            }

            if ((int) ($order['payment_fee'] ?? 0) > 0) {
                $label = ($order['payment'] ?? '') === 'yamato' ? '代引手数料' : '決済手数料';
                $code = ($order['payment'] ?? '') === 'yamato' ? '999998' : '999992';
                $this->insertFeeLine($pdo, $orderId, $code, $label, (int) $order['payment_fee'], $order);
            }

            if ((int) ($order['coolpoint_use'] ?? 0) > 0) {
                $this->insertFeeLine(
                    $pdo,
                    $orderId,
                    '999999',
                    'クールポイント利用分',
                    -(int) $order['coolpoint_use'],
                    $order
                );
            }

            $this->syncCoolpoints($pdo, $orderId, $order);

            $pdo->commit();

            return [
                'order_id' => $orderId,
                'subtotal' => (int) ($order['subtotal'] ?? 0),
                'shipping_fee' => (int) ($order['shipping_fee'] ?? 0),
                'payment_fee' => (int) ($order['payment_fee'] ?? 0),
                'total' => (int) ($order['total'] ?? 0),
            ];
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public function updatePayPalToken(int $orderId, string $token): void
    {
        if ($orderId <= 0 || $token === '' || !$this->hasSalesMasterColumn('token')) {
            return;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $statement = $pdo->prepare('UPDATE sales_master SET token = :token WHERE s_id = :order_id');
        $statement->execute([
            ':token' => $token,
            ':order_id' => $orderId,
        ]);
    }

    public function completePayPalPayment(int $orderId, string $token, string $paymentStatus, string $transactionId): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $orderId <= 0) {
            return;
        }

        $assignments = [];
        $params = [':order_id' => $orderId];

        if ($this->hasSalesMasterColumn('token') && $token !== '') {
            $assignments[] = 'token = :token';
            $params[':token'] = $token;
        }

        if ($this->hasSalesMasterColumn('paypal_state')) {
            $assignments[] = 'paypal_state = :paypal_state';
            $params[':paypal_state'] = $paymentStatus;
        }

        if ($this->hasSalesMasterColumn('trance_id')) {
            $assignments[] = 'trance_id = :trance_id';
            $params[':trance_id'] = $transactionId;
        }

        if ($assignments === []) {
            return;
        }

        $statement = $pdo->prepare(
            'UPDATE sales_master SET ' . implode(', ', $assignments) . ' WHERE s_id = :order_id'
        );
        $statement->execute($params);
    }

    public function updatePayPalError(int $orderId, string $errorCode, string $errorShortMessage): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $orderId <= 0) {
            return;
        }

        $assignments = [];
        $params = [':order_id' => $orderId];

        if ($this->hasSalesMasterColumn('paypal_state')) {
            $assignments[] = 'paypal_state = :paypal_state';
            $params[':paypal_state'] = 'thanks_pp_error';
        }

        if ($this->hasSalesMasterColumn('ErrorCode')) {
            $assignments[] = 'ErrorCode = :error_code';
            $params[':error_code'] = $errorCode;
        }

        if ($this->hasSalesMasterColumn('ErrorShortMsg')) {
            $assignments[] = 'ErrorShortMsg = :error_short_message';
            $params[':error_short_message'] = $errorShortMessage;
        }

        if ($assignments === []) {
            return;
        }

        $statement = $pdo->prepare(
            'UPDATE sales_master SET ' . implode(', ', $assignments) . ' WHERE s_id = :order_id'
        );
        $statement->execute($params);
    }

    public function cancelPayPalPayment(int $orderId): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $orderId <= 0) {
            return;
        }

        $pdo->beginTransaction();

        try {
            $state = $this->findOrderWithTotals($orderId);
            if ($state === null) {
                $pdo->rollBack();
                return;
            }

            if ((string) ($state['paypal_state'] ?? '') !== 'Completed') {
                $this->restoreStock($pdo, $orderId);
            }

            $assignments = [];
            $params = [':order_id' => $orderId];

            if ($this->hasSalesMasterColumn('paypal_state')) {
                $assignments[] = 'paypal_state = :paypal_state';
                $params[':paypal_state'] = 'CANCEL';
            }

            if ($this->hasSalesMasterColumn('mail_flag')) {
                $assignments[] = 'mail_flag = :mail_flag';
                $params[':mail_flag'] = '9';
            }

            if ($assignments !== []) {
                $statement = $pdo->prepare(
                    'UPDATE sales_master SET ' . implode(', ', $assignments) . ' WHERE s_id = :order_id'
                );
                $statement->execute($params);
            }

            $this->cancelCoolpoints($pdo, $orderId);

            $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private function insertSalesMaster(PDO $pdo, array $order): int
    {
        $values = [];

        $this->assignMasterValue($values, 'sess_id', (string) ($order['session_id'] ?? session_id()));
        $this->assignMasterValue($values, 's_date', date('Y-m-d H:i:s'));
        $this->assignMasterValue($values, 'acc_id', (int) ($order['user_id'] ?? 0));
        $this->assignMasterValue($values, 'bikou', (string) ($order['notes'] ?? ''));
        $this->assignMasterValue($values, 'payment', (string) ($order['payment'] ?? 'bank'));
        $this->assignMasterValue($values, 'mail_flag', '0');
        $this->assignMasterValue($values, 'delivery_time', (string) ($order['delivery_time'] ?? ''));
        $this->assignMasterValue($values, 'app_biz', (string) ($order['member_label'] ?? ''));
        $this->assignMasterValue($values, 'su_name', (string) ($order['shipping_name'] ?? ''));
        $this->assignMasterValue($values, 'su_shop', (string) ($order['shipping_shop'] ?? ''));
        $this->assignMasterValue($values, 'u_zip', (string) ($order['customer_zip'] ?? ''));
        $this->assignMasterValue($values, 'u_add', (string) ($order['customer_address'] ?? ''));
        $this->assignMasterValue($values, 'u_tel', (string) ($order['customer_tel'] ?? ''));
        $this->assignMasterValue($values, 'u_email', (string) ($order['customer_email'] ?? ''));
        $this->assignMasterValue($values, 'smd_flag', (string) ($order['member_flag'] ?? '0'));

        $columns = array_keys($values);
        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);
        $params = [];
        foreach ($values as $column => $value) {
            $params[':' . $column] = $value;
        }

        $statement = $pdo->prepare(
            'INSERT INTO sales_master (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')'
        );
        $statement->execute($params);

        return (int) $pdo->lastInsertId();
    }

    private function insertSalesLine(PDO $pdo, int $orderId, array $line, array $order): void
    {
        $product = $line['product'];
        $values = [];

        $this->assignSubValue($values, 's_id', $orderId);
        $this->assignSubValue($values, 'parts_num', (string) ($product['parts_num'] ?? ''));
        $this->assignSubValue($values, 'syamei2', trim((string) (($product['make'] ?? '') . ' ' . ($product['name'] ?? ''))));
        $this->assignSubValue($values, 'qty', (int) ($line['qty'] ?? 0));
        $this->assignSubValue($values, 'ss_price', (int) ($line['unit_price'] ?? 0));
        $this->assignSubValue($values, 'ss_total', (int) ($line['subtotal'] ?? 0));
        $this->assignSubValue($values, 'ssd_flag', (string) ($order['member_flag'] ?? '0'));
        $this->assignSubValue($values, 'ss_date', date('Y-m-d'));

        $columns = array_keys($values);
        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);
        $params = [];
        foreach ($values as $column => $value) {
            $params[':' . $column] = $value;
        }

        $statement = $pdo->prepare(
            'INSERT INTO sales_sub (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')'
        );
        $statement->execute($params);
    }

    private function insertFeeLine(PDO $pdo, int $orderId, string $partsNum, string $name, int $amount, array $order): void
    {
        $values = [];
        $this->assignSubValue($values, 's_id', $orderId);
        $this->assignSubValue($values, 'parts_num', $partsNum);
        $this->assignSubValue($values, 'syamei2', $name);
        $this->assignSubValue($values, 'qty', 1);
        $this->assignSubValue($values, 'ss_price', $amount);
        $this->assignSubValue($values, 'ss_total', $amount);
        $this->assignSubValue($values, 'ssd_flag', (string) ($order['member_flag'] ?? '0'));
        $this->assignSubValue($values, 'ss_date', date('Y-m-d'));

        $columns = array_keys($values);
        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);
        $params = [];
        foreach ($values as $column => $value) {
            $params[':' . $column] = $value;
        }

        $statement = $pdo->prepare(
            'INSERT INTO sales_sub (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')'
        );
        $statement->execute($params);
    }

    private function decrementStock(PDO $pdo, string $partsNum, int $qty): void
    {
        if ($partsNum === '' || $qty <= 0) {
            return;
        }

        $statement = $pdo->prepare(
            'UPDATE PARTS
             SET stock = stock - :qty
             WHERE parts_num = :parts_num'
        );
        $statement->execute([
            ':qty' => $qty,
            ':parts_num' => $partsNum,
        ]);
    }

    private function syncCoolpoints(PDO $pdo, int $orderId, array $order): void
    {
        if (!$this->hasTable('coolpoint')
            || !$this->hasCoolpointColumn('cp_s_id')
            || !$this->hasCoolpointColumn('cp_acc_id')
            || !$this->hasCoolpointColumn('cp_point')
            || !$this->hasCoolpointColumn('cp_date')
            || !$this->hasCoolpointColumn('cp_state')
        ) {
            return;
        }

        $userId = (int) ($order['user_id'] ?? 0);
        if ($userId <= 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $pointRows = [];

        if ((int) ($order['coolpoint_use'] ?? 0) > 0) {
            $pointRows[] = [
                'cp_s_id' => $orderId,
                'cp_acc_id' => $userId,
                'cp_point' => -(int) $order['coolpoint_use'],
                'cp_date' => $now,
                'cp_state' => 'minus',
            ];
        }

        if ((int) ($order['coolpoint_earned'] ?? 0) > 0) {
            $pointRows[] = [
                'cp_s_id' => $orderId,
                'cp_acc_id' => $userId,
                'cp_point' => (int) $order['coolpoint_earned'],
                'cp_date' => $now,
                'cp_state' => 'plus',
            ];
        }

        foreach ($pointRows as $row) {
            $statement = $pdo->prepare(
                'INSERT INTO coolpoint (cp_s_id, cp_acc_id, cp_point, cp_date, cp_state)
                 VALUES (:cp_s_id, :cp_acc_id, :cp_point, :cp_date, :cp_state)'
            );
            $statement->execute([
                ':cp_s_id' => $row['cp_s_id'],
                ':cp_acc_id' => $row['cp_acc_id'],
                ':cp_point' => $row['cp_point'],
                ':cp_date' => $row['cp_date'],
                ':cp_state' => $row['cp_state'],
            ]);
        }
    }

    private function cancelCoolpoints(PDO $pdo, int $orderId): void
    {
        if ($orderId <= 0
            || !$this->hasTable('coolpoint')
            || !$this->hasCoolpointColumn('cp_s_id')
            || !$this->hasCoolpointColumn('cp_state')
        ) {
            return;
        }

        $statement = $pdo->prepare(
            'UPDATE coolpoint
             SET cp_state = :cp_state
             WHERE cp_s_id = :order_id'
        );
        $statement->execute([
            ':cp_state' => 'cancel',
            ':order_id' => $orderId,
        ]);
    }

    private function restoreStock(PDO $pdo, int $orderId): void
    {
        if ($orderId <= 0 || !$this->hasSalesSubColumn('parts_num') || !$this->hasSalesSubColumn('qty')) {
            return;
        }

        $statement = $pdo->prepare(
            "SELECT parts_num, qty
             FROM sales_sub
             WHERE s_id = :order_id
               AND parts_num NOT IN ('999991', '999992', '999997', '999998', '999999')"
        );
        $statement->execute([':order_id' => $orderId]);

        foreach ($statement->fetchAll() ?: [] as $row) {
            $partsNum = (string) ($row['parts_num'] ?? '');
            $qty = (int) ($row['qty'] ?? 0);
            if ($partsNum === '' || $qty <= 0) {
                continue;
            }

            $stockUpdate = $pdo->prepare(
                'UPDATE PARTS
                 SET stock = stock + :qty
                 WHERE parts_num = :parts_num'
            );
            $stockUpdate->execute([
                ':qty' => $qty,
                ':parts_num' => $partsNum,
            ]);
        }
    }

    private function assignMasterValue(array &$values, string $column, mixed $value): void
    {
        if ($this->hasSalesMasterColumn($column)) {
            $values[$column] = $value;
        }
    }

    private function assignSubValue(array &$values, string $column, mixed $value): void
    {
        if ($this->hasSalesSubColumn($column)) {
            $values[$column] = $value;
        }
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

    private function hasCoolpointColumn(string $column): bool
    {
        return in_array($column, $this->coolpointColumns(), true);
    }

    private function coolpointColumns(): array
    {
        if (is_array($this->coolpointColumns)) {
            return $this->coolpointColumns;
        }

        $this->coolpointColumns = $this->fetchColumns('coolpoint');

        return $this->coolpointColumns;
    }
}
