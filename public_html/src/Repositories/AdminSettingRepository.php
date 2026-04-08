<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config;

final class AdminSettingRepository
{
    private const FILE_PATH = '/storage/admin_settings.json';
    private const SPECIAL_MEMBER_APPROVAL_BONUS = 'special_member_approval_bonus';

    public function getSpecialMemberApprovalBonus(): int
    {
        $settings = $this->load();
        $default = max(0, (int) Config::get('SPECIAL_MEMBER_APPROVAL_BONUS', 1000));

        return max(0, (int) ($settings[self::SPECIAL_MEMBER_APPROVAL_BONUS] ?? $default));
    }

    public function saveSpecialMemberApprovalBonus(int $points): bool
    {
        $settings = $this->load();
        $settings[self::SPECIAL_MEMBER_APPROVAL_BONUS] = max(0, $points);

        return $this->save($settings);
    }

    private function load(): array
    {
        $path = $this->path();
        if (!is_file($path)) {
            return [];
        }

        $contents = file_get_contents($path);
        if ($contents === false || trim($contents) === '') {
            return [];
        }

        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function save(array $settings): bool
    {
        $path = $this->path();
        $directory = dirname($path);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return false;
        }

        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        return file_put_contents($path, $json . PHP_EOL, LOCK_EX) !== false;
    }

    private function path(): string
    {
        return (string) Config::get('base_path', dirname(__DIR__, 2)) . self::FILE_PATH;
    }
}
