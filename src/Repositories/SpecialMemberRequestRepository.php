<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class SpecialMemberRequestRepository
{
    public function countByStatuses(array $statuses): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $statuses === []) {
            return 0;
        }

        $placeholders = [];
        $params = [];
        foreach (array_values($statuses) as $index => $status) {
            $placeholder = ':status_' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $status;
        }

        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM special_member_requests WHERE status IN (' . implode(', ', $placeholders) . ')'
        );
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public function listByStatuses(array $statuses, int $limit = 10): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $statuses === []) {
            return [];
        }

        $limit = max(1, min($limit, 50));
        $placeholders = [];
        foreach (array_values($statuses) as $index => $status) {
            $placeholders[':status_' . $index] = $status;
        }

        $statement = $pdo->prepare(
            'SELECT * FROM special_member_requests
             WHERE status IN (' . implode(', ', array_keys($placeholders)) . ')
             ORDER BY id DESC
             LIMIT :limit'
        );
        foreach ($placeholders as $placeholder => $status) {
            $statement->bindValue($placeholder, $status, PDO::PARAM_STR);
        }
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll() ?: [];
    }

    public function create(int $userId, array $data, string $status = 'docs_pending'): ?int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = <<<SQL
            INSERT INTO special_member_requests (
                acc_id,
                company_name,
                shop_name,
                contact_name,
                email,
                tel,
                zip,
                address_line1,
                address_line2,
                address_line3,
                website_url,
                business_type,
                notes,
                status,
                requested_at
            ) VALUES (
                :acc_id,
                :company_name,
                :shop_name,
                :contact_name,
                :email,
                :tel,
                :zip,
                :address_line1,
                :address_line2,
                :address_line3,
                :website_url,
                :business_type,
                :notes,
                :status,
                NOW()
            )
        SQL;

        $statement = $pdo->prepare($sql);
        $saved = $statement->execute([
            ':acc_id' => $userId,
            ':company_name' => $data['company_name'],
            ':shop_name' => $data['shop_name'],
            ':contact_name' => $data['contact_name'],
            ':email' => $data['email'],
            ':tel' => $data['tel'],
            ':zip' => $data['zip'],
            ':address_line1' => $data['address_line1'],
            ':address_line2' => $data['address_line2'],
            ':address_line3' => $data['address_line3'],
            ':website_url' => $data['website_url'],
            ':business_type' => $data['business_type'],
            ':notes' => $data['notes'],
            ':status' => $status,
        ]);

        if (!$saved) {
            return null;
        }

        return (int) $pdo->lastInsertId();
    }

    public function addFile(int $requestId, array $file): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        $sql = <<<SQL
            INSERT INTO special_member_request_files (
                request_id,
                stored_name,
                original_name,
                file_path,
                mime_type,
                file_size,
                created_at
            ) VALUES (
                :request_id,
                :stored_name,
                :original_name,
                :file_path,
                :mime_type,
                :file_size,
                NOW()
            )
        SQL;

        $statement = $pdo->prepare($sql);

        return $statement->execute([
            ':request_id' => $requestId,
            ':stored_name' => $file['stored_name'],
            ':original_name' => $file['original_name'],
            ':file_path' => $file['file_path'],
            ':mime_type' => $file['mime_type'],
            ':file_size' => $file['file_size'],
        ]);
    }

    public function updateStatus(int $requestId, string $status): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $statement = $pdo->prepare(
            'UPDATE special_member_requests
             SET status = :status,
                 reviewed_at = CASE WHEN :status IN ("approved", "rejected") THEN NOW() ELSE reviewed_at END
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute([
            ':status' => $status,
            ':id' => $requestId,
        ]);
    }

    public function deleteById(int $requestId): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $statement = $pdo->prepare('DELETE FROM special_member_request_files WHERE request_id = :request_id');
        $statement->execute([':request_id' => $requestId]);

        $statement = $pdo->prepare('DELETE FROM special_member_requests WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $requestId]);
    }

    public function findLatestByUserId(int $userId): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $statement = $pdo->prepare(
            'SELECT * FROM special_member_requests WHERE acc_id = :acc_id ORDER BY id DESC LIMIT 1'
        );
        $statement->execute([':acc_id' => $userId]);
        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    public function findById(int $requestId): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $statement = $pdo->prepare(
            'SELECT * FROM special_member_requests WHERE id = :id LIMIT 1'
        );
        $statement->execute([':id' => $requestId]);
        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    public function findFilesByRequestId(int $requestId): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $statement = $pdo->prepare(
            'SELECT * FROM special_member_request_files WHERE request_id = :request_id ORDER BY id ASC'
        );
        $statement->execute([':request_id' => $requestId]);

        return $statement->fetchAll() ?: [];
    }

    public function listRecent(int $limit = 100): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $statement = $pdo->prepare(
            'SELECT * FROM special_member_requests ORDER BY id DESC LIMIT :limit'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll() ?: [];
    }
}
