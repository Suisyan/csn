<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AdminSettingRepository;
use App\Repositories\CoolpointRepository;
use App\Repositories\SpecialMemberRequestRepository;
use App\Repositories\UserRepository;

final class AdminSpecialMemberController
{
    public function index(): void
    {
        require_admin_login();

        $repository = new SpecialMemberRequestRepository();
        $settings = new AdminSettingRepository();
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
                'pageLead' => '申請内容、名刺画像の有無、承認状態を確認します。承認時の付与ポイントもここで変更できます。',
                'approvalBonusPoints' => $settings->getSpecialMemberApprovalBonus(),
                'notice' => $this->noticeMessage((string) ($_GET['notice'] ?? '')),
                'error' => $this->errorMessage((string) ($_GET['error'] ?? '')),
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

    public function saveSettings(): void
    {
        require_admin_login();

        $points = max(0, (int) ($_POST['special_member_approval_bonus'] ?? 0));
        $saved = (new AdminSettingRepository())->saveSpecialMemberApprovalBonus($points);

        header('Location: /admin/special-members?' . ($saved ? 'notice=bonus_saved' : 'error=bonus_save_failed'));
        exit;
    }

    private function handleReview(int $requestId, string $action): void
    {
        $repository = new SpecialMemberRequestRepository();
        $request = $repository->findById($requestId);

        if (is_array($request)) {
            $users = new UserRepository();
            $coolpoints = new CoolpointRepository();
            $settings = new AdminSettingRepository();
            $userId = (int) ($request['acc_id'] ?? 0);
            $previousStatus = (string) ($request['status'] ?? '');
            $redirectQuery = '';

            if ($action === 'approve') {
                $repository->updateStatus($requestId, 'approved');
                $users->promoteToSpecialMember($userId);
                $bonusPoints = $settings->getSpecialMemberApprovalBonus();
                if ($previousStatus !== 'approved'
                    && $bonusPoints > 0
                    && !$coolpoints->hasSpecialMemberApprovalBonus($userId)
                ) {
                    $granted = $coolpoints->grantSpecialMemberApprovalBonus($userId, $bonusPoints);
                    $redirectQuery = $granted ? '?notice=approved_bonus' : '?notice=approved';
                } else {
                    $redirectQuery = '?notice=approved';
                }
            } elseif ($action === 'reject') {
                $repository->updateStatus($requestId, 'rejected');
                $users->markSpecialMemberRejected($userId);
                $redirectQuery = '?notice=rejected';
            }

            header('Location: /admin/special-members' . $redirectQuery);
            exit;
        }

        header('Location: /admin/special-members');
        exit;
    }

    private function noticeMessage(string $code): ?string
    {
        return match ($code) {
            'bonus_saved' => '承認ポイントを更新しました。',
            'approved' => '特別会員として承認しました。',
            'approved_bonus' => '特別会員として承認し、初回承認ポイントを付与しました。',
            'rejected' => '特別会員申請を却下しました。',
            default => null,
        };
    }

    private function errorMessage(string $code): ?string
    {
        return match ($code) {
            'bonus_save_failed' => '承認ポイントの保存に失敗しました。',
            default => null,
        };
    }
}
