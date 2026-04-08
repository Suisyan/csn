<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AdminMemberRepository;

final class AdminMemberController
{
    public function index(): void
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
}
