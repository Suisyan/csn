<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AdminOrderRepository;
use App\Repositories\AdminMemberRepository;
use App\Repositories\AdminProductRepository;
use App\Repositories\SpecialMemberRequestRepository;

final class AdminDashboardController
{
    public function index(): void
    {
        require_admin_login();

        $repository = new AdminOrderRepository();
        $specialRepository = new SpecialMemberRequestRepository();
        $specialStatuses = ['docs_pending', 'pending'];
        $modalOrderId = (int) ($_GET['modal_order_id'] ?? 0);
        $modalOrder = $modalOrderId > 0 ? $repository->findOrderDetail($modalOrderId) : null;

        echo render('layout', [
            'title' => '管理画面トップ',
            'content' => render('admin_dashboard', [
                'pageTitle' => '管理画面トップ',
                'pageLead' => '旧管理画面に近い導線で、受注管理と商品管理を順次統合していきます。',
                'pendingCount' => $repository->countPending(),
                'pendingOrders' => $repository->listPending(8),
                'specialPendingCount' => $specialRepository->countByStatuses($specialStatuses),
                'specialPendingRequests' => $specialRepository->listByStatuses($specialStatuses, 8),
                'modalOrderId' => $modalOrderId,
                'modalOrder' => $modalOrder,
                'modalLines' => $modalOrder !== null ? $repository->findOrderLines($modalOrderId) : [],
                'modalDelivery' => $modalOrder !== null ? $repository->findOrderDelivery($modalOrderId) : null,
                'closeModalHref' => '/admin',
                'modalReturnTo' => 'dashboard',
                'modalPage' => 1,
                'notice' => $this->noticeMessage((string) ($_GET['notice'] ?? '')),
                'error' => $this->errorMessage((string) ($_GET['error'] ?? '')),
            ]),
        ]);
    }

    public function orders(): void
    {
        $this->renderPlaceholder('受注管理', '受注一覧、受注明細、発送処理をこのセクションへ統合します。');
    }

    public function products(): void
    {
        require_admin_login();

        $keyword = trim((string) ($_GET['key'] ?? ''));
        $repository = new AdminProductRepository();

        echo render('layout', [
            'title' => '商品管理',
            'content' => render('admin_products', [
                'pageTitle' => '商品管理',
                'pageLead' => '旧管理画面の並びを参考に、商品検索と一覧確認を先に整理しています。',
                'keyword' => $keyword,
                'products' => $repository->search($keyword),
                'hasSearched' => $keyword !== '',
            ]),
        ]);
    }

    public function members(): void
    {
        require_admin_login();

        $keyword = trim((string) ($_GET['key'] ?? ''));
        $repository = new AdminMemberRepository();

        echo render('layout', [
            'title' => '会員管理',
            'content' => render('admin_members', [
                'pageTitle' => '会員管理',
                'pageLead' => '旧管理画面の検索導線を参考に、名前・会社名・電話番号・メール・ID で会員を確認できます。',
                'keyword' => $keyword,
                'members' => $repository->search($keyword),
                'hasSearched' => $keyword !== '',
            ]),
        ]);
    }

    public function inquiries(): void
    {
        $this->renderPlaceholder('その他', '問い合わせ対応や補助運用機能を、このセクションへ順次整理します。');
    }

    private function renderPlaceholder(string $title, string $lead): void
    {
        require_admin_login();

        echo render('layout', [
            'title' => $title,
            'content' => render('admin_dashboard', [
                'pageTitle' => $title,
                'pageLead' => $lead,
            ]),
        ]);
    }

    private function noticeMessage(string $code): ?string
    {
        return match ($code) {
            'bank_saved' => '入金状態を更新しました。',
            'shipping_saved' => '出荷情報を更新しました。',
            default => null,
        };
    }

    private function errorMessage(string $code): ?string
    {
        return match ($code) {
            'bank_failed' => '入金状態の更新に失敗しました。',
            'shipping_failed' => '出荷情報の更新に失敗しました。',
            default => null,
        };
    }
}
