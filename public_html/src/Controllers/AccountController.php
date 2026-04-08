<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\CoolpointRepository;
use App\Repositories\DeliveryRepository;
use App\Repositories\PurchaseHistoryRepository;
use App\Repositories\SpecialMemberRequestRepository;
use App\Repositories\UserRepository;

final class AccountController
{
    public function show(array $params = []): void
    {
        $sessionUser = require_login();
        $users = new UserRepository();
        $profile = $users->findById((int) ($sessionUser['id'] ?? 0));

        if (!is_array($profile)) {
            header('Location: /login');
            exit;
        }

        $requests = new SpecialMemberRequestRepository();
        $request = $requests->findLatestByUserId((int) $profile['id']);
        $requestFiles = is_array($request)
            ? $requests->findFilesByRequestId((int) ($request['id'] ?? 0))
            : [];
        $purchases = (new PurchaseHistoryRepository())->listRecentByUserId((int) $profile['id']);
        $deliveries = new DeliveryRepository();
        $coolpoints = new CoolpointRepository();
        $user = $this->normalizeProfile($profile);
        refresh_current_user($user);

        echo render('layout', [
            'title' => 'Account',
            'content' => render('account', [
                'user' => $user,
                'profile' => $profile,
                'request' => $request,
                'requestFiles' => $requestFiles,
                'purchases' => $purchases,
                'deliveries' => $deliveries->listByUserId((int) $profile['id']),
                'deliveryEnabled' => $deliveries->isAvailable(),
                'coolpointBalance' => $coolpoints->currentBalanceByUserId((int) $profile['id']),
                'coolpointHistory' => $coolpoints->listRecentByUserId((int) $profile['id']),
                'coolpointEnabled' => $coolpoints->isAvailable(),
                'notice' => $this->noticeMessage((string) ($_GET['notice'] ?? '')),
                'deliveryError' => $this->deliveryErrorMessage((string) ($_GET['delivery_error'] ?? '')),
            ]),
        ]);
    }

    public function saveDelivery(array $params = []): void
    {
        $user = require_login();
        $delivery = new DeliveryRepository();

        if (!$delivery->isAvailable()) {
            header('Location: /account?delivery_error=delivery_unavailable');
            exit;
        }

        $shop = trim((string) ($_POST['deliv_shop'] ?? ''));
        $person = trim((string) ($_POST['deliv_person'] ?? ''));
        $zip = trim((string) ($_POST['deliv_zip'] ?? ''));
        $address = trim((string) ($_POST['deliv_add'] ?? ''));
        $tel = trim((string) ($_POST['deliv_tel'] ?? ''));

        if ($shop === '' || $person === '' || $zip === '' || $address === '' || $tel === '') {
            header('Location: /account?delivery_error=missing_delivery_fields');
            exit;
        }

        $saved = $delivery->saveForUser((int) ($user['id'] ?? 0), $_POST);

        header('Location: /account?notice=' . ($saved ? 'delivery_saved' : 'delivery_failed'));
        exit;
    }

    public function deleteDelivery(array $params = []): void
    {
        $user = require_login();
        $deliveryId = (int) ($params['id'] ?? 0);
        $deleted = (new DeliveryRepository())->deleteForUser((int) ($user['id'] ?? 0), $deliveryId);

        header('Location: /account?notice=' . ($deleted ? 'delivery_deleted' : 'delivery_failed'));
        exit;
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

    private function noticeMessage(string $code): ?string
    {
        return match ($code) {
            'delivery_saved' => '発送先を保存しました。',
            'delivery_deleted' => '発送先を削除しました。',
            'delivery_failed' => '発送先の保存に失敗しました。',
            default => null,
        };
    }

    private function deliveryErrorMessage(string $code): ?string
    {
        return match ($code) {
            'missing_delivery_fields' => '発送先登録には、宛先名・担当者名・郵便番号・住所・電話番号が必要です。',
            'delivery_unavailable' => '発送先テーブルが未接続のため、この機能はまだ利用できません。',
            default => null,
        };
    }
}
