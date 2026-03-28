<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class ProductRepository
{
    private const MODE_CATEGORY_MAP = [
        '1' => [1, 3, 7, 999],
        '2' => [2, 4],
    ];

    public function search(array $filters, ?array $viewer = null): array
    {
        $mode = $this->normalizeMode((string) ($filters['mode'] ?? '2'));
        $make = trim((string) ($filters['make'] ?? ''));
        $katasiki = $this->normalizeKatasiki((string) ($filters['katasiki'] ?? ''));
        $toc = trim((string) ($filters['toc'] ?? ''));

        if ($katasiki === '' && $make === '') {
            return [];
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return array_map(
                fn (array $product): array => $this->decoratePricing($product, $viewer),
                $this->fallbackProducts($filters)
            );
        }

        $categoryIds = self::MODE_CATEGORY_MAP[$mode];
        $categoryPlaceholders = [];
        $params = [
            ':make' => $make,
            ':katasiki' => $katasiki,
        ];

        foreach ($categoryIds as $index => $categoryId) {
            $placeholder = ':cat_' . $index;
            $categoryPlaceholders[] = $placeholder;
            $params[$placeholder] = $categoryId;
        }

        $tocSql = '';
        if ($mode === '2') {
            $tocSql = 'AND (:toc = \'\' OR MODEL.toc = :toc)';
            $params[':toc'] = $toc;
        }

        $sql = <<<SQL
            SELECT
                MODEL.m_id AS id,
                PARTS.category,
                PARTS.cat_num,
                PARTS.parts_num,
                PARTS.picture_num,
                PARTS.web_num,
                MODEL.syamei1 AS make,
                MODEL.syamei2 AS name,
                MODEL.katasiki,
                MODEL.toc,
                MODEL.engine,
                PARTS.priceA AS price,
                FLOOR(PARTS.priceA * (1 - COALESCE(D1.d_rate, 0)) / 10) * 10 AS member_price,
                FLOOR(PARTS.priceA * (1 - COALESCE(D2.d_rate, 0)) / 10) * 10 AS special_price,
                PARTS.stock,
                PARTS.nouki AS lead_time,
                COALESCE(NULLIF(MODEL.bikou, ''), NULLIF(MODEL.bikou2, ''), NULLIF(PARTS.bikou2, ''), '') AS note,
                PARTS.y_flag
            FROM MODEL
            INNER JOIN PARTS ON MODEL.parts_num = PARTS.parts_num
            LEFT JOIN DISCOUNT D1 ON D1.d_id = 1
            LEFT JOIN DISCOUNT D2 ON D2.d_id = 2
            WHERE (:make = '' OR MODEL.syamei1 = :make)
              AND (
                    :katasiki = ''
                    OR MODEL.katasiki = :katasiki
                    OR REPLACE(REPLACE(REPLACE(REPLACE(MODEL.katasiki, '-', ''), ' ', ''), '　', ''), '/', '') =
                       REPLACE(REPLACE(REPLACE(REPLACE(:katasiki, '-', ''), ' ', ''), '　', ''), '/', '')
                  )
              {$tocSql}
              AND PARTS.cat_num IN (%s)
            ORDER BY PARTS.cat_num, MODEL.katasiki, MODEL.m_id
            LIMIT 100
        SQL;

        $statement = $pdo->prepare(sprintf($sql, implode(', ', $categoryPlaceholders)));
        $statement->execute($params);

        $rows = $statement->fetchAll() ?: [];

        return array_map(
            fn (array $product): array => $this->decoratePricing($product, $viewer),
            $rows
        );
    }

    public function findById(int $id, ?array $viewer = null): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            foreach ($this->fallbackProducts([]) as $product) {
                if ((int) $product['id'] === $id) {
                    return $this->decoratePricing($product, $viewer);
                }
            }

            return null;
        }

        $sql = <<<SQL
            SELECT
                MODEL.m_id AS id,
                PARTS.category,
                PARTS.cat_num,
                PARTS.parts_num,
                PARTS.picture_num,
                PARTS.web_num,
                MODEL.syamei1 AS make,
                MODEL.syamei2 AS name,
                MODEL.katasiki,
                MODEL.toc,
                MODEL.engine,
                PARTS.priceA AS price,
                FLOOR(PARTS.priceA * (1 - COALESCE(D1.d_rate, 0)) / 10) * 10 AS member_price,
                FLOOR(PARTS.priceA * (1 - COALESCE(D2.d_rate, 0)) / 10) * 10 AS special_price,
                PARTS.stock,
                PARTS.nouki AS lead_time,
                COALESCE(NULLIF(MODEL.bikou, ''), NULLIF(MODEL.bikou2, ''), NULLIF(PARTS.bikou2, ''), '') AS note,
                PARTS.y_flag
            FROM MODEL
            INNER JOIN PARTS ON MODEL.parts_num = PARTS.parts_num
            LEFT JOIN DISCOUNT D1 ON D1.d_id = 1
            LEFT JOIN DISCOUNT D2 ON D2.d_id = 2
            WHERE MODEL.m_id = :id
            LIMIT 1
        SQL;

        $statement = $pdo->prepare($sql);
        $statement->execute([':id' => $id]);
        $row = $statement->fetch();

        return $row ? $this->decoratePricing($row, $viewer) : null;
    }

    private function decoratePricing(array $product, ?array $viewer): array
    {
        $guestPrice = (int) ($product['price'] ?? 0);
        $memberPrice = (int) ($product['member_price'] ?? $guestPrice);
        $specialPrice = (int) ($product['special_price'] ?? $memberPrice);
        $memberType = (string) ($viewer['member_type'] ?? '');
        $bizStatus = (string) ($viewer['biz_status'] ?? '');

        $displayPrice = $guestPrice;
        $displayLabel = '非会員価格';
        $priceNote = '非会員向けの価格を表示しています。';

        if ($memberType === 'biz' && $bizStatus === 'approved') {
            $displayPrice = $specialPrice;
            $displayLabel = '特別会員価格';
            $priceNote = '特別会員向けの価格を表示しています。';
        } elseif ($memberType === 'net' || $bizStatus === 'pending') {
            $displayPrice = $memberPrice;
            $displayLabel = '会員価格';
            $priceNote = $bizStatus === 'pending'
                ? '特別会員申請中のため、現在は会員価格を表示しています。'
                : '会員向けの価格を表示しています。';
        }

        $product['guest_price'] = $guestPrice;
        $product['member_price'] = $memberPrice;
        $product['special_price'] = $specialPrice;
        $product['display_price'] = $displayPrice;
        $product['display_price_label'] = $displayLabel;
        $product['price_note'] = $priceNote;

        return $product;
    }

    private function normalizeMode(string $mode): string
    {
        return array_key_exists($mode, self::MODE_CATEGORY_MAP) ? $mode : '2';
    }

    private function normalizeKatasiki(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = mb_convert_kana($value, 'asKV', 'UTF-8');
        $value = str_replace(['=', 'ー', '－', '―', '−', '/', '　'], ['-', '-', '-', '-', '-', '', ''], $value);
        $value = str_replace(' ', '', $value);
        $value = preg_replace('/-+/', '-', $value) ?? $value;

        return trim($value, '-');
    }

    private function fallbackProducts(array $filters): array
    {
        $items = [
            [
                'id' => 1,
                'category' => '日本車ラジエーター',
                'cat_num' => 2,
                'parts_num' => 'NR0463',
                'picture_num' => 'GC0050',
                'web_num' => 'NIS0350',
                'make' => '日産',
                'name' => 'ラフェスタ',
                'katasiki' => 'DBA-CWEFWN',
                'toc' => 'CVT',
                'engine' => 'LF-VDS',
                'price' => 16000,
                'member_price' => 15680,
                'special_price' => 14980,
                'stock' => 4,
                'lead_time' => '即日出荷(15時まで受付・休日除く)',
                'note' => '参考用のサンプル表示です。',
                'y_flag' => 0,
            ],
            [
                'id' => 2,
                'category' => '日本車コンデンサー',
                'cat_num' => 4,
                'parts_num' => 'NC0471',
                'picture_num' => 'GC0051',
                'web_num' => 'CNS0014',
                'make' => '日産',
                'name' => 'ノート',
                'katasiki' => 'DBA-E12',
                'toc' => 'CVT',
                'engine' => 'HR12DE',
                'price' => 13000,
                'member_price' => 12740,
                'special_price' => 12100,
                'stock' => 1,
                'lead_time' => '即日出荷(15時まで受付・休日除く)',
                'note' => 'コンデンサーのサンプル表示です。',
                'y_flag' => 0,
            ],
            [
                'id' => 3,
                'category' => '輸入車ラジエーター',
                'cat_num' => 1,
                'parts_num' => 'GR0352',
                'picture_num' => 'BMW18',
                'web_num' => 'BMW0070',
                'make' => 'BMW',
                'name' => '3シリーズ',
                'katasiki' => 'ABA-VR20',
                'toc' => 'A/T',
                'engine' => 'N46B20B',
                'price' => 21000,
                'member_price' => 20580,
                'special_price' => 19800,
                'stock' => 0,
                'lead_time' => 'お問い合わせ後にご案内です。',
                'note' => '輸入車検索のサンプル表示です。',
                'y_flag' => 0,
            ],
        ];

        $mode = $this->normalizeMode((string) ($filters['mode'] ?? '2'));
        $make = trim((string) ($filters['make'] ?? ''));
        $katasiki = $this->normalizeKatasiki((string) ($filters['katasiki'] ?? ''));
        $toc = trim((string) ($filters['toc'] ?? ''));
        $allowedCategories = self::MODE_CATEGORY_MAP[$mode];

        return array_values(array_filter($items, static function (array $item) use ($allowedCategories, $make, $katasiki, $toc, $mode): bool {
            if (!in_array((int) $item['cat_num'], $allowedCategories, true)) {
                return false;
            }

            if ($make !== '' && $item['make'] !== $make) {
                return false;
            }

            if ($katasiki !== '' && stripos(str_replace('-', '', $item['katasiki']), str_replace('-', '', $katasiki)) === false) {
                return false;
            }

            if ($mode === '2' && $toc !== '' && $item['toc'] !== $toc) {
                return false;
            }

            return true;
        }));
    }
}
