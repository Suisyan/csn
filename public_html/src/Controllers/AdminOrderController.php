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
        $modalOrderId = (int) ($_GET['modal_order_id'] ?? 0);
        $modalOrder = $modalOrderId > 0 ? $repository->findOrderDetail($modalOrderId) : null;

        echo render('layout', [
            'title' => '受注管理',
            'content' => render('admin_orders', [
                'pageTitle' => '受注管理',
                'pageLead' => '受注を 20 件ずつ表示し、未完了と完了が分かる形で確認できます。',
                'orders' => $repository->listRecentPage($page, $perPage),
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalOrders' => $totalOrders,
                'modalOrderId' => $modalOrderId,
                'modalOrder' => $modalOrder,
                'modalLines' => $modalOrder !== null ? $repository->findOrderLines($modalOrderId) : [],
                'modalDelivery' => $modalOrder !== null ? $repository->findOrderDelivery($modalOrderId) : null,
                'closeModalHref' => '/admin/orders?page=' . $page,
                'modalReturnTo' => 'orders',
                'modalPage' => $page,
                'notice' => $this->noticeMessage((string) ($_GET['notice'] ?? '')),
                'error' => $this->errorMessage((string) ($_GET['error'] ?? '')),
            ]),
        ]);
    }

    public function show(array $params = []): void
    {
        require_admin_login();

        $orderId = (int) ($params['id'] ?? $_GET['id'] ?? $_GET['s_id'] ?? 0);
        $repository = new AdminOrderRepository();
        $order = $repository->findOrderDetail($orderId);
        $isModal = (string) ($_GET['modal'] ?? '') === '1';

        if ($order === null) {
            if ($isModal) {
                http_response_code(404);
                header('Content-Type: text/html; charset=UTF-8');
                echo '<div class="admin-order-modal__empty">受注データを取得できませんでした。</div>';

                return;
            }

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

        $viewData = [
            'order' => $order,
            'lines' => $repository->findOrderLines($orderId),
            'delivery' => $repository->findOrderDelivery($orderId),
        ];

        if ($isModal) {
            header('Content-Type: text/html; charset=UTF-8');
            echo render('admin_order_detail', $viewData);

            return;
        }

        echo render('layout', [
            'title' => '受注明細',
            'content' => render('admin_order_show', $viewData + [
                'notice' => $this->noticeMessage((string) ($_GET['notice'] ?? '')),
                'error' => $this->errorMessage((string) ($_GET['error'] ?? '')),
                'modalReturnTo' => 'orders',
                'modalPage' => 1,
            ]),
        ]);
    }

    public function saveBank(array $params = []): void
    {
        require_admin_login();

        $orderId = (int) ($params['id'] ?? 0);
        $saved = (new AdminOrderRepository())->updateBankStatus($orderId, (string) ($_POST['bank_dep'] ?? '0'));

        $this->redirectBack($orderId, $saved ? 'bank_saved' : null, $saved ? null : 'bank_failed');
    }

    public function saveShipping(array $params = []): void
    {
        require_admin_login();

        $orderId = (int) ($params['id'] ?? 0);
        $saved = (new AdminOrderRepository())->updateShipping($orderId, $_POST);

        $this->redirectBack($orderId, $saved ? 'shipping_saved' : null, $saved ? null : 'shipping_failed');
    }

    private function redirectBack(int $orderId, ?string $notice = null, ?string $error = null): void
    {
        $returnTo = (string) ($_POST['return_to'] ?? 'orders');
        $page = max(1, (int) ($_POST['page'] ?? 1));

        $path = $returnTo === 'dashboard' ? '/admin' : '/admin/orders?page=' . $page;
        $query = ['modal_order_id' => $orderId];
        if ($notice !== null) {
            $query['notice'] = $notice;
        }
        if ($error !== null) {
            $query['error'] = $error;
        }

        header('Location: ' . $path . (str_contains($path, '?') ? '&' : '?') . http_build_query($query));
        exit;
    }

    private function noticeMessage(string $code): ?string
    {
        return match ($code) {
            'bank_saved' => '入金状態を更新しました。',
            'shipping_saved' => '出荷情報を更新しました。',
            default => null,
        };
    }

    private function errorMessage(string $code): ?string
    {
        return match ($code) {
            'bank_failed' => '入金状態の更新に失敗しました。',
            'shipping_failed' => '出荷情報の更新に失敗しました。',
            default => null,
        };
    }
}
