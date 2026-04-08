<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AdminProductRepository;

final class AdminProductController
{
    public function index(): void
    {
        require_admin_login();

        $keyword = trim((string) ($_GET['key'] ?? ''));
        $repository = new AdminProductRepository();
        $products = $repository->search($keyword);

        echo render('layout', [
            'title' => '商品管理',
            'content' => render('admin_products', [
                'pageTitle' => '商品管理',
                'pageLead' => '旧管理画面の並びを参考に、商品検索と一覧確認を先に整理しています。',
                'keyword' => $keyword,
                'products' => $products,
                'hasSearched' => $keyword !== '',
            ]),
        ]);
    }
}
