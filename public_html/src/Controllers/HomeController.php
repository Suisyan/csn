<?php

declare(strict_types=1);

namespace App\Controllers;

final class HomeController
{
    public function index(): void
    {
        echo render('layout', [
            'title' => 'Sample Test Site',
            'content' => render('home', [
                'specialEntryUrl' => '/special-member/register',
                'makes' => ['TOYOTA', 'NISSAN', 'HONDA', 'MAZDA', 'SUBARU', 'SUZUKI', 'DAIHATSU', 'BMW'],
                'newsItems' => [
                    [
                        'date' => 'Now',
                        'title' => 'Testing unified pricing and shared cart flow.',
                    ],
                    [
                        'date' => 'Check',
                        'title' => 'Verify guest, member, and special member behavior.',
                    ],
                ],
                'reasons' => [
                    [
                        'title' => 'Shared flow',
                        'body' => 'Search, product detail, cart, and inquiry can be tested in one flow.',
                    ],
                    [
                        'title' => 'Pricing switch',
                        'body' => 'Guest, member, and special member can each see only their own price.',
                    ],
                    [
                        'title' => 'Special member intake',
                        'body' => 'Special member registration is available from the top page before purchase and before login.',
                    ],
                ],
            ]),
        ]);
    }
}
