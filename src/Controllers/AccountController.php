<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\SpecialMemberRequestRepository;
use App\Repositories\UserRepository;

final class AccountController
{
    public function show(): void
    {
        $sessionUser = require_login();
        $users = new UserRepository();
        $profile = $users->findById((int) ($sessionUser['id'] ?? 0));

        if (!is_array($profile)) {
            header('Location: /login');
            exit;
        }

        $request = (new SpecialMemberRequestRepository())->findLatestByUserId((int) $profile['id']);
        $user = $this->normalizeProfile($profile);
        refresh_current_user($user);

        echo render('layout', [
            'title' => 'Account',
            'content' => render('account', [
                'user' => $user,
                'profile' => $profile,
                'request' => $request,
            ]),
        ]);
    }

    private function normalizeProfile(array $profile): array
    {
        $memberType = (string) ($profile['member_type'] ?? '');
        if (!in_array($memberType, ['guest', 'net', 'biz'], true)) {
            $memberType = match ((string) ($profile['state'] ?? '')) {
                '1' => 'net',
                '2' => 'biz',
                default => 'guest',
            };
        }

        $bizStatus = (string) ($profile['biz_status'] ?? '');
        if (!in_array($bizStatus, ['none', 'docs_pending', 'pending', 'approved', 'rejected'], true)) {
            $bizStatus = $memberType === 'biz' ? 'approved' : 'none';
        }

        return [
            'id' => (int) ($profile['id'] ?? 0),
            'name' => (string) ($profile['name'] ?? ''),
            'email' => (string) ($profile['email'] ?? ''),
            'member_type' => $memberType,
            'biz_status' => $bizStatus,
        ];
    }
}
