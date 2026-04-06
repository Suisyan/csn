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

        echo render('layout', [
            'title' => '管理画面トップ',
            'content' => render('admin_dashboard', [
                'pageTitle' => '管理画面トップ',
                'pageLead' => '旧管理画面に近い導線で、受注管理と商品管理を順次統合していきます。',
                'pendingCount' => $repository->countPending(),
                'pendingOrders' => $repository->listPending(8),
                'specialPendingCount' => $specialRepository->countByStatuses($specialStatuses),
                'specialPendingRequests' => $specialRepository->listByStatuses($specialStatuses, 8),
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
}
