<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AdminOrderRepository;

final class AdminDashboardController
{
    public function index(): void
    {
        require_admin_login();

        $repository = new AdminOrderRepository();

        echo render('layout', [
            'title' => '管理画面トップ',
            'content' => render('admin_dashboard', [
                'pageTitle' => '管理画面トップ',
                'pageLead' => '旧管理画面に近い導線で、受注管理と商品管理を順次統合していきます。',
                'pendingCount' => $repository->countPending(),
                'pendingOrders' => $repository->listPending(8),
            ]),
        ]);
    }

    public function orders(): void
    {
        $this->renderPlaceholder('受注管理', '受注一覧、受注明細、発送処理をこのセクションへ統合します。');
    }

    public function products(): void
    {
        $this->renderPlaceholder('商品管理', '商品、型式、OEM、画像アップロードを旧管理画面に近い構成で整理します。');
    }

    public function members(): void
    {
        $this->renderPlaceholder('会員管理', '会員検索、会員詳細、ポイント調整を管理画面へ統合します。');
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
