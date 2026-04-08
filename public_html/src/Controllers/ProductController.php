<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ProductRepository;

final class ProductController
{
    public function show(array $params): void
    {
        $repository = new ProductRepository();
        $product = $repository->findById((int) ($params['id'] ?? 0), current_user());

        if ($product === null) {
            http_response_code(404);
            echo render('layout', [
                'title' => '商品が見つかりません',
                'content' => '<section class="page-block"><div class="page-header"><h1 class="page-title">商品が見つかりません</h1><p class="lead">型式や品番をご確認のうえ、再度お試しください。</p></div></section>',
            ]);
            return;
        }

        echo render('layout', [
            'title' => $product['name'] . ' | 商品詳細',
            'content' => render('product', [
                'product' => $product,
            ]),
        ]);
    }
}
