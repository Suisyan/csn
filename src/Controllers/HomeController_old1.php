<?php

declare(strict_types=1);

namespace App\Controllers;

final class HomeController
{
    public function index(): void
    {
        echo render('layout', [
            'title' => 'Cooling Shop Renewal',
            'content' => render('home', [
                'makes' => ['トヨタ', 'ニッサン', 'ホンダ', 'マツダ', 'スバル', 'スズキ'],
            ]),
        ]);
    }
}
