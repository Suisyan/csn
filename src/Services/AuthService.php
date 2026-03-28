<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;

final class AuthService
{
    public function attempt(string $email, string $password): array
    {
        if ($email === '' || $password === '') {
            return [
                'success' => false,
                'user' => null,
                'error_code' => 'missing_credentials',
            ];
        }

        $users = new UserRepository();
        $candidates = $users->findActiveCandidatesByEmail($email);
        if ($candidates === []) {
            return [
                'success' => false,
                'user' => null,
                'error_code' => 'invalid_credentials',
            ];
        }

        foreach ($candidates as $candidate) {
            $passwordHash = trim((string) ($candidate['password_hash'] ?? ''));
            $legacyPassword = (string) ($candidate['legacy_password'] ?? '');

            $verifiedByHash = $passwordHash !== '' && password_verify($password, $passwordHash);
            $verifiedByLegacy = $legacyPassword !== '' && hash_equals($legacyPassword, $password);

            if (!$verifiedByHash && !$verifiedByLegacy) {
                continue;
            }

            $memberType = $this->resolveMemberType($candidate);
            if ($memberType === 'guest') {
                return [
                    'success' => false,
                    'user' => null,
                    'error_code' => 'guest_login_disabled',
                ];
            }

            $bizStatus = $this->resolveBizStatus($candidate, $memberType);
            $newPasswordHash = $verifiedByHash && !password_needs_rehash($passwordHash, PASSWORD_DEFAULT)
                ? $passwordHash
                : self::makePasswordHash($password);

            $users->upgradeCredentials((int) $candidate['id'], $newPasswordHash, $memberType, $bizStatus);

            return [
                'success' => true,
                'user' => [
                    'id' => (int) $candidate['id'],
                    'name' => (string) ($candidate['name'] ?? ''),
                    'email' => (string) ($candidate['email'] ?? ''),
                    'member_type' => $memberType,
                    'biz_status' => $bizStatus,
                ],
                'error_code' => null,
            ];
        }

        return [
            'success' => false,
            'user' => null,
            'error_code' => 'invalid_credentials',
        ];
    }

    public static function makePasswordHash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    private function resolveMemberType(array $user): string
    {
        $memberType = trim((string) ($user['member_type'] ?? ''));
        if (in_array($memberType, ['guest', 'net', 'biz'], true)) {
            return $memberType;
        }

        return match ((string) ($user['state'] ?? '')) {
            '1' => 'net',
            '2' => 'biz',
            default => 'guest',
        };
    }

    private function resolveBizStatus(array $user, string $memberType): string
    {
        $bizStatus = trim((string) ($user['biz_status'] ?? ''));
        if (in_array($bizStatus, ['none', 'docs_pending', 'pending', 'approved', 'rejected'], true)) {
            return $bizStatus;
        }

        return $memberType === 'biz' ? 'approved' : 'none';
    }
}
