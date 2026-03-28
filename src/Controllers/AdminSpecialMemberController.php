<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\SpecialMemberRequestRepository;
use App\Repositories\UserRepository;

final class AdminSpecialMemberController
{
    public function index(): void
    {
        $this->requireAdmin();

        $repository = new SpecialMemberRequestRepository();
        $requests = $repository->listRecent();
        foreach ($requests as &$request) {
            $request['files'] = $repository->findFilesByRequestId((int) ($request['id'] ?? 0));
        }
        unset($request);

        echo render('layout', [
            'title' => 'Special Member Admin',
            'content' => render('admin_special_members', [
                'requests' => $requests,
            ]),
        ]);
    }

    public function review(): void
    {
        $this->requireAdmin();

        $requestId = (int) ($_POST['request_id'] ?? 0);
        $action = (string) ($_POST['action'] ?? '');
        $repository = new SpecialMemberRequestRepository();
        $request = $repository->findById($requestId);

        if (is_array($request)) {
            $users = new UserRepository();
            $userId = (int) ($request['acc_id'] ?? 0);

            if ($action === 'approve') {
                $repository->updateStatus($requestId, 'approved');
                $users->promoteToSpecialMember($userId);
            } elseif ($action === 'reject') {
                $repository->updateStatus($requestId, 'rejected');
                $users->markSpecialMemberRejected($userId);
            }
        }

        header('Location: /admin/special-members');
        exit;
    }

    private function requireAdmin(): void
    {
        $expectedUser = (string) config('ADMIN_USER', '');
        $expectedPass = (string) config('ADMIN_PASS', '');

        if ($expectedUser === '' || $expectedPass === '') {
            header('HTTP/1.0 500 Internal Server Error');
            exit('Admin credentials are not configured.');
        }

        $user = (string) ($_SERVER['PHP_AUTH_USER'] ?? '');
        $pass = (string) ($_SERVER['PHP_AUTH_PW'] ?? '');

        if (!hash_equals($expectedUser, $user) || !hash_equals($expectedPass, $pass)) {
            header('WWW-Authenticate: Basic realm="Special Member Admin"');
            header('HTTP/1.0 401 Unauthorized');
            exit('Unauthorized');
        }
    }
}
