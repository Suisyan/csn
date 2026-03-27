<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class ProductRepository
{
    public function search(array $filters): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return $this->fallbackProducts($filters);
        }

        $sql = <<<SQL
            SELECT
                m_id AS id,
                category,
                parts_num,
                syamei1 AS make,
                syamei2 AS name,
                katasiki,
                toc,
                priceA AS price,
                FLOOR(priceA * (1 - COALESCE(DISCOUNT.d_rate, 0)) / 10) * 10 AS discount_price,
                stock,
                nouki AS lead_time,
                bikou AS note
            FROM MODEL
            INNER JOIN PARTS ON MODEL.parts_num = PARTS.parts_num
            LEFT JOIN DISCOUNT ON DISCOUNT.d_id = 1
            WHERE (:make = '' OR syamei1 = :make)
              AND (:katasiki = '' OR katasiki LIKE :katasiki_like)
              AND (:toc = '' OR toc = :toc)
            ORDER BY PARTS.cat_num, MODEL.katasiki
            LIMIT 50
        SQL;

        $statement = $pdo->prepare($sql);
        $statement->execute([
            ':make' => $filters['make'] ?? '',
            ':katasiki' => $filters['katasiki'] ?? '',
            ':katasiki_like' => '%' . ($filters['katasiki'] ?? '') . '%',
            ':toc' => $filters['toc'] ?? '',
        ]);

        return $statement->fetchAll() ?: [];
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            foreach ($this->fallbackProducts([]) as $product) {
                if ((int) $product['id'] === $id) {
                    return $product;
                }
            }

            return null;
        }

        $sql = <<<SQL
            SELECT
                m_id AS id,
                category,
                parts_num,
                syamei1 AS make,
                syamei2 AS name,
                katasiki,
                toc,
                engine,
                priceA AS price,
                nouki AS lead_time,
                bikou AS note
            FROM MODEL
            INNER JOIN PARTS ON MODEL.parts_num = PARTS.parts_num
            WHERE m_id = :id
            LIMIT 1
        SQL;

        $statement = $pdo->prepare($sql);
        $statement->execute([':id' => $id]);
        $row = $statement->fetch();

        return $row ?: null;
    }

    private function fallbackProducts(array $filters): array
    {
        $items = [
            [
                'id' => 1,
                'category' => '国内車ラジエーター',
                'parts_num' => '1015596',
                'make' => 'トヨタ',
                'name' => 'カローラ ラジエーター',
                'katasiki' => 'E-AE91',
                'toc' => 'A/T',
                'engine' => '5A-FE',
                'price' => 19800,
                'discount_price' => 18400,
                'stock' => 5,
                'lead_time' => '在庫状況により変動',
                'note' => '旧サイトの文言を維持しながら順次移行予定です。',
            ],
            [
                'id' => 2,
                'category' => '国内車コンデンサー',
                'parts_num' => '8846012450',
                'make' => 'ニッサン',
                'name' => 'スカイライン コンデンサー',
                'katasiki' => 'GF-ER34',
                'toc' => 'M/T',
                'engine' => 'RB25DET',
                'price' => 24800,
                'discount_price' => 22900,
                'stock' => 0,
                'lead_time' => 'お問い合わせください',
                'note' => '検索・商品詳細のデモ表示用データです。',
            ],
            [
                'id' => 3,
                'category' => '輸入車ラジエーター',
                'parts_num' => 'BMW-17111728907',
                'make' => 'BMW',
                'name' => '3シリーズ ラジエーター',
                'katasiki' => 'E-CA18',
                'toc' => 'A/T',
                'engine' => 'M42',
                'price' => 32800,
                'discount_price' => 30200,
                'stock' => -1,
                'lead_time' => 'お問い合わせください',
                'note' => '輸入車検索イメージ確認用のサンプルです。',
            ],
        ];

        return array_values(array_filter($items, static function (array $item) use ($filters): bool {
            if (($filters['make'] ?? '') !== '' && $item['make'] !== $filters['make']) {
                return false;
            }

            if (($filters['katasiki'] ?? '') !== '' && stripos($item['katasiki'], $filters['katasiki']) === false) {
                return false;
            }

            if (($filters['toc'] ?? '') !== '' && $item['toc'] !== $filters['toc']) {
                return false;
            }

            return true;
        }));
    }
}
