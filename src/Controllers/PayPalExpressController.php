<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\OrderRepository;
use App\Services\PayPalExpressService;
use RuntimeException;

final class PayPalExpressController
{
    public function start(array $params = []): void
    {
        $orderId = (int) ($_GET['s'] ?? 0);
        $userId = (int) ($_GET['u'] ?? 0);

        try {
            $order = $this->loadPayPalOrder($orderId, $userId);
            $service = new PayPalExpressService();
            $response = $service->startCheckout([
                'PAYMENTREQUEST_0_AMT' => (string) ((int) ($order['total_amount'] ?? 0)),
                'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
                'RETURNURL' => $this->absoluteUrl('/cart/pp_confirm2.php'),
                'CANCELURL' => $this->absoluteUrl('/pp_cancel.php?s_id=' . $orderId),
                'PAYMENTREQUEST_0_CURRENCYCODE' => 'JPY',
                'LANDINGPAGE' => 'Billing',
                'PAGESTYLE' => 'index',
                'PAYMENTREQUEST_0_INVNUM' => (string) $orderId,
                'EMAIL' => (string) ($order['u_email'] ?? ''),
                'PAYMENTREQUEST_0_SHIPTONAME' => (string) ($order['su_name'] ?? ''),
                'PAYMENTREQUEST_0_SHIPTOSTREET' => (string) ($order['shipto_street'] ?? ''),
                'PAYMENTREQUEST_0_SHIPTOCITY' => (string) ($order['shipto_city'] ?? ''),
                'PAYMENTREQUEST_0_SHIPTOSTATE' => (string) ($order['shipto_state'] ?? ''),
                'PAYMENTREQUEST_0_SHIPTOZIP' => (string) ($order['shipto_zip'] ?? ''),
                'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => 'JP',
                'PAYMENTREQUEST_0_SHIPTOPHONENUM' => (string) ($order['u_tel'] ?? ''),
            ]);

            $ack = strtoupper((string) ($response['ACK'] ?? ''));
            if (!in_array($ack, ['SUCCESS', 'SUCCESSWITHWARNING'], true)) {
                throw new RuntimeException($this->buildApiErrorMessage($response));
            }

            $token = (string) ($response['TOKEN'] ?? '');
            if ($token === '') {
                throw new RuntimeException('PayPal トークンの取得に失敗しました。');
            }

            (new OrderRepository())->updatePayPalToken($orderId, $token);
            header('Location: ' . $service->redirectUrl($token));
            exit;
        } catch (RuntimeException $exception) {
            $this->renderError('PayPal 決済を開始できませんでした。', $exception->getMessage(), $orderId);
        }
    }

    public function review(array $params = []): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        if ($token === '') {
            $this->renderError('PayPal 情報を確認できませんでした。', 'token が不足しています。');
            return;
        }

        try {
            $service = new PayPalExpressService();
            $response = $service->fetchCheckoutDetails($token);
            $ack = strtoupper((string) ($response['ACK'] ?? ''));
            if (!in_array($ack, ['SUCCESS', 'SUCCESSWITHWARNING'], true)) {
                throw new RuntimeException($this->buildApiErrorMessage($response));
            }

            $orderId = (int) ($response['PAYMENTREQUEST_0_INVNUM'] ?? $response['INVNUM'] ?? 0);
            $payerId = (string) ($response['PAYERID'] ?? '');
            $repository = new OrderRepository();
            if ($orderId > 0) {
                $repository->updatePayPalToken($orderId, $token);
            }

            $order = $this->loadPayPalOrder($orderId);

            echo render('layout', [
                'title' => 'PayPal ご確認',
                'content' => render('paypal_review', [
                    'order' => $order,
                    'orderId' => $orderId,
                    'token' => $token,
                    'payerId' => $payerId,
                    'payerEmail' => (string) ($response['EMAIL'] ?? ''),
                    'payerName' => trim((string) (($response['LASTNAME'] ?? '') . ' ' . ($response['FIRSTNAME'] ?? ''))),
                    'amount' => (int) ($order['total_amount'] ?? 0),
                ]),
            ]);
        } catch (RuntimeException $exception) {
            $this->renderError('PayPal の確認画面を表示できませんでした。', $exception->getMessage());
        }
    }

    public function complete(array $params = []): void
    {
        $token = trim((string) ($_POST['t'] ?? $_GET['t'] ?? ''));
        $payerId = trim((string) ($_POST['p'] ?? $_GET['p'] ?? $_GET['PayerID'] ?? ''));
        $orderId = (int) ($_POST['s_id'] ?? $_GET['s_id'] ?? 0);

        try {
            if ($token === '' || $payerId === '') {
                throw new RuntimeException('PayPal 決済に必要な情報が不足しています。');
            }

            $repository = new OrderRepository();
            if ($orderId <= 0) {
                $order = $repository->findByPayPalToken($token);
                $orderId = (int) ($order['s_id'] ?? 0);
            }

            $order = $this->loadPayPalOrder($orderId);
            $amount = (int) ($order['total_amount'] ?? 0);
            $service = new PayPalExpressService();
            $response = $service->completeCheckout($token, $payerId, $amount);
            $ack = strtoupper((string) ($response['ACK'] ?? ''));

            if (!in_array($ack, ['SUCCESS', 'SUCCESSWITHWARNING'], true)) {
                $repository->updatePayPalError(
                    $orderId,
                    (string) ($response['L_ERRORCODE0'] ?? ''),
                    (string) ($response['L_SHORTMESSAGE0'] ?? '')
                );
                throw new RuntimeException($this->buildApiErrorMessage($response));
            }

            $paymentStatus = (string) ($response['PAYMENTINFO_0_PAYMENTSTATUS'] ?? '');
            $transactionId = (string) ($response['PAYMENTINFO_0_TRANSACTIONID'] ?? '');
            $repository->completePayPalPayment($orderId, $token, $paymentStatus, $transactionId);

            echo render('layout', [
                'title' => 'PayPal 決済完了',
                'content' => render('paypal_result', [
                    'state' => 'success',
                    'title' => 'PayPal 決済が完了しました',
                    'message' => 'クレジット決済の承認が完了しました。ご注文内容を引き続き手配いたします。',
                    'order' => $order,
                    'orderId' => $orderId,
                    'transactionId' => $transactionId,
                    'paymentStatus' => $paymentStatus,
                ]),
            ]);
        } catch (RuntimeException $exception) {
            $this->renderError('PayPal 決済を完了できませんでした。', $exception->getMessage(), $orderId);
        }
    }

    public function cancel(array $params = []): void
    {
        $orderId = (int) ($_GET['s_id'] ?? 0);

        try {
            if ($orderId <= 0) {
                throw new RuntimeException('注文番号が不足しています。');
            }

            $repository = new OrderRepository();
            $order = $repository->findOrderWithTotals($orderId);
            if ($order === null) {
                throw new RuntimeException('対象の注文が見つかりません。');
            }

            $repository->cancelPayPalPayment($orderId);

            echo render('layout', [
                'title' => 'PayPal 決済キャンセル',
                'content' => render('paypal_result', [
                    'state' => 'cancel',
                    'title' => 'PayPal 決済をキャンセルしました',
                    'message' => 'PayPal 側で決済がキャンセルされたため、対象注文もキャンセル扱いにしました。必要であれば、改めてご注文ください。',
                    'order' => $order,
                    'orderId' => $orderId,
                    'transactionId' => '',
                    'paymentStatus' => 'CANCEL',
                ]),
            ]);
        } catch (RuntimeException $exception) {
            $this->renderError('PayPal 決済キャンセルを反映できませんでした。', $exception->getMessage(), $orderId);
        }
    }

    private function loadPayPalOrder(int $orderId, int $userId = 0): array
    {
        if ($orderId <= 0) {
            throw new RuntimeException('注文番号が不足しています。');
        }

        $order = (new OrderRepository())->findOrderWithTotals($orderId);
        if ($order === null) {
            throw new RuntimeException('対象の注文が見つかりません。');
        }

        if ((string) ($order['payment'] ?? '') !== 'card') {
            throw new RuntimeException('この注文は PayPal 決済対象ではありません。');
        }

        if ($userId > 0 && (int) ($order['acc_id'] ?? 0) !== $userId) {
            throw new RuntimeException('注文情報の照合に失敗しました。');
        }

        return $order;
    }

    private function buildApiErrorMessage(array $response): string
    {
        $short = trim((string) ($response['L_SHORTMESSAGE0'] ?? ''));
        $long = trim((string) ($response['L_LONGMESSAGE0'] ?? ''));
        $code = trim((string) ($response['L_ERRORCODE0'] ?? ''));

        return trim(implode(' / ', array_filter([$code, $short, $long])));
    }

    private function absoluteUrl(string $path): string
    {
        return rtrim((string) config('APP_URL', ''), '/') . $path;
    }

    private function renderError(string $title, string $message, int $orderId = 0): void
    {
        http_response_code(400);

        echo render('layout', [
            'title' => $title,
            'content' => render('paypal_result', [
                'state' => 'error',
                'title' => $title,
                'message' => $message,
                'order' => null,
                'orderId' => $orderId,
                'transactionId' => '',
                'paymentStatus' => 'ERROR',
            ]),
        ]);
    }
}
