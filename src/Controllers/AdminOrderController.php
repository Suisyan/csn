<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AdminOrderRepository;

final class AdminOrderController
{
    public function index(): void
    {
        require_admin_login();

        $orderId = (int) ($_GET['s_id'] ?? $_GET['id'] ?? 0);
        if ($orderId > 0) {
            $this->show(['id' => (string) $orderId]);

            return;
        }

        $repository = new AdminOrderRepository();
        $perPage = 20;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $totalOrders = $repository->countRecent();
        $totalPages = max(1, (int) ceil($totalOrders / $perPage));
        $page = min($page, $totalPages);

        echo render('layout', [
            'title' => '受注管理',
            'content' => render('admin_orders', [
                'pageTitle' => '受注管理',
                'pageLead' => '受注を 20 件ずつ表示し、未完了と完了が分かる形で確認できます。',
                'orders' => $repository->listRecentPage($page, $perPage),
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalOrders' => $totalOrders,
            ]),
        ]);
    }

    public function show(array $params = []): void
    {
        require_admin_login();

        $orderId = (int) ($params['id'] ?? $_GET['id'] ?? $_GET['s_id'] ?? 0);
        $repository = new AdminOrderRepository();
        $order = $repository->findOrderDetail($orderId);

        if ($order === null) {
            http_response_code(404);
            echo render('layout', [
                'title' => '受注が見つかりません',
                'content' => render('admin_dashboard', [
                    'pageTitle' => '受注が見つかりません',
                    'pageLead' => '指定された注文番号の受注データを確認できませんでした。',
                    'pendingCount' => $repository->countPending(),
                    'pendingOrders' => $repository->listPending(8),
                ]),
            ]);

            return;
        }

        echo render('layout', [
            'title' => '受注明細',
            'content' => render('admin_order_show', [
                'order' => $order,
                'lines' => $repository->findOrderLines($orderId),
                'delivery' => $repository->findOrderDelivery($orderId),
            ]),
        ]);
    }
}
