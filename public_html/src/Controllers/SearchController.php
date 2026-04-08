<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ProductRepository;

final class SearchController
{
    public function index(): void
    {
        $repository = new ProductRepository();
        $filters = [
            'make' => trim((string) ($_GET['make'] ?? '')),
            'katasiki' => trim((string) ($_GET['katasiki'] ?? '')),
            'toc' => trim((string) ($_GET['toc'] ?? '')),
            'mode' => trim((string) ($_GET['mode'] ?? '2')),
        ];

        $hasSearched = $filters['make'] !== ''
            || $filters['katasiki'] !== ''
            || $filters['toc'] !== ''
            || isset($_GET['mode']);

        echo render('layout', [
            'title' => '商品検索結果',
            'content' => render('search', [
                'filters' => $filters,
                'products' => $repository->search($filters, current_user()),
                'hasSearched' => $hasSearched,
            ]),
        ]);
    }
}
