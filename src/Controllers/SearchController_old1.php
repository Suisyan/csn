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
        ];

        echo render('layout', [
            'title' => '検索結果',
            'content' => render('search', [
                'filters' => $filters,
                'products' => $repository->search($filters),
            ]),
        ]);
    }
}
