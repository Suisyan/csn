<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\SpecialMemberRequestRepository;
use App\Repositories\UserRepository;

final class AdminSpecialMemberController
{
    public function index(): void
    {
        require_admin_login();

        $repository = new SpecialMemberRequestRepository();
        $requests = $repository->listRecent();
        foreach ($requests as &$request) {
            $request['files'] = $repository->findFilesByRequestId((int) ($request['id'] ?? 0));
        }
        unset($request);

        echo render('layout', [
            'title' => '特別会員申請管理',
            'content' => render('admin_special_members', [
                'requests' => $requests,
                'pageTitle' => '特別会員申請管理',
                'pageLead' => '申請内容、名刺画像の有無、承認状態を確認します。',
            ]),
        ]);
    }

    public function approve(array $params = []): void
    {
        require_admin_login();

        $this->handleReview((int) ($params['id'] ?? 0), 'approve');
    }

    public function reject(array $params = []): void
    {
        require_admin_login();

        $this->handleReview((int) ($params['id'] ?? 0), 'reject');
    }

    private function handleReview(int $requestId, string $action): void
    {
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
}
