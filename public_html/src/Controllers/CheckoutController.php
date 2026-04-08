<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\CartRepository;
use App\Repositories\CoolpointRepository;
use App\Repositories\DeliveryRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\MailService;
use RuntimeException;

final class CheckoutController
{
    public function show(array $params = []): void
    {
        $prefill = $this->prefillCheckout($_POST);
        $cartSummary = $this->buildCartSummary($prefill);
        if ($cartSummary['lines'] === []) {
            header('Location: /cart');
            exit;
        }

        echo render('layout', [
            'title' => 'ご注文手続き',
            'content' => render('checkout', [
                'errors' => [],
                'prefill' => $prefill,
                'summary' => $cartSummary,
                'deliveryList' => $this->deliveryList(),
            ]),
        ]);
    }

    public function confirm(array $params = []): void
    {
        $prefill = $this->prefillCheckout($_POST);
        $cartSummary = $this->buildCartSummary($prefill);
        $errors = $this->validate($prefill, $cartSummary);

        if ($errors !== []) {
            echo render('layout', [
                'title' => 'ご注文手続き',
                'content' => render('checkout', [
                    'errors' => $errors,
                    'prefill' => $prefill,
                    'summary' => $cartSummary,
                    'deliveryList' => $this->deliveryList(),
                ]),
            ]);
            return;
        }

        echo render('layout', [
            'title' => 'ご注文内容確認',
            'content' => render('checkout_confirm', [
                'prefill' => $prefill,
                'summary' => $cartSummary,
                'paypalUrl' => $prefill['payment'] === 'card'
                    ? $this->buildPayPalUrl(0, (int) ($this->currentProfile()['id'] ?? 0), (int) $cartSummary['total'])
                    : null,
            ]),
        ]);
    }

    public function complete(array $params = []): void
    {
        $prefill = $this->prefillCheckout($_POST);
        $cartSummary = $this->buildCartSummary($prefill);
        $errors = $this->validate($prefill, $cartSummary);

        if ($errors !== []) {
            echo render('layout', [
                'title' => 'ご注文手続き',
                'content' => render('checkout', [
                    'errors' => $errors,
                    'prefill' => $prefill,
                    'summary' => $cartSummary,
                    'deliveryList' => $this->deliveryList(),
                ]),
            ]);
            return;
        }

        $orderData = $this->buildOrderData($prefill, $cartSummary);

        try {
            $result = (new OrderRepository())->create($orderData, $cartSummary['lines']);
            (new CartRepository())->clear();
            $paypalUrl = $prefill['payment'] === 'card'
                ? $this->buildPayPalUrl((int) $result['order_id'], (int) ($orderData['user_id'] ?? 0), (int) $result['total'])
                : null;
            $this->sendOrderMails($orderData, $cartSummary['lines'], (int) $result['order_id'], $paypalUrl);

            echo render('layout', [
                'title' => 'ご注文完了',
                'content' => render('checkout_complete', [
                    'orderId' => (int) $result['order_id'],
                    'prefill' => $prefill,
                    'summary' => array_merge($cartSummary, $result),
                    'paymentLabel' => $this->paymentLabel((string) $prefill['payment']),
                    'paypalUrl' => $paypalUrl,
                ]),
            ]);
        } catch (RuntimeException $exception) {
            echo render('layout', [
                'title' => 'ご注文手続き',
                'content' => render('checkout', [
                    'errors' => [$exception->getMessage()],
                    'prefill' => $prefill,
                    'summary' => $cartSummary,
                    'deliveryList' => $this->deliveryList(),
                ]),
            ]);
        }
    }

    private function buildCartSummary(array $input = []): array
    {
        $viewer = current_user();
        $validation = (new OrderRepository())->validateStock((new CartRepository())->all(), $viewer);
        $lines = $validation['lines'];
        $subtotal = array_sum(array_map(static fn (array $line): int => (int) $line['subtotal'], $lines));
        $shippingFee = (int) config('ORDER_SHIPPING_FEE', 0);
        $paymentMethod = (string) ($input['payment'] ?? $_POST['payment'] ?? 'bank');
        $paymentFee = $paymentMethod === 'yamato' ? (int) config('ORDER_COD_FEE', 500) : 0;
        $pointMeta = $this->coolpointMeta();
        $requestedPoints = max(0, (int) ($input['coolpoint_use'] ?? $_POST['coolpoint_use'] ?? 0));
        $availablePoints = (int) ($pointMeta['balance'] ?? 0);
        $maxUsablePoints = max(0, $subtotal + $shippingFee + $paymentFee);
        $pointDiscount = $pointMeta['eligible']
            ? min($requestedPoints, $availablePoints, $maxUsablePoints)
            : 0;
        $earnedPointsBase = max(0, $subtotal - $pointDiscount);
        $earnedPoints = $pointMeta['eligible']
            ? (int) round($earnedPointsBase * (float) ($pointMeta['rate'] ?? 0), 0)
            : 0;

        return [
            'lines' => $lines,
            'errors' => $validation['errors'],
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'payment_fee' => $paymentFee,
            'point_discount' => $pointDiscount,
            'point_available' => $availablePoints,
            'point_earned' => max(0, $earnedPoints),
            'point_eligible' => (bool) ($pointMeta['eligible'] ?? false),
            'point_rate' => (float) ($pointMeta['rate'] ?? 0),
            'total' => max(0, $subtotal + $shippingFee + $paymentFee - $pointDiscount),
        ];
    }

    private function prefillCheckout(array $input): array
    {
        $profile = $this->currentProfile();
        $sameAsCustomer = (string) ($input['same_as_customer'] ?? 'yes');

        $customer = [
            'customer_name' => trim((string) ($input['customer_name'] ?? ($profile['u_name'] ?? $profile['name'] ?? ''))),
            'customer_shop' => trim((string) ($input['customer_shop'] ?? ($profile['u_shop'] ?? $profile['b_name'] ?? ''))),
            'customer_zip' => trim((string) ($input['customer_zip'] ?? ($profile['zip'] ?? ''))),
            'customer_address1' => trim((string) ($input['customer_address1'] ?? ($profile['add1'] ?? ''))),
            'customer_address2' => trim((string) ($input['customer_address2'] ?? ($profile['add2'] ?? ''))),
            'customer_address3' => trim((string) ($input['customer_address3'] ?? ($profile['add3'] ?? ''))),
            'customer_tel' => trim((string) ($input['customer_tel'] ?? ($profile['tel'] ?? ''))),
            'customer_email' => trim((string) ($input['customer_email'] ?? ($profile['email'] ?? ''))),
        ];

        return array_merge($customer, [
            'shipping_name' => trim((string) ($input['shipping_name'] ?? ($sameAsCustomer === 'yes' ? $customer['customer_name'] : ''))),
            'shipping_shop' => trim((string) ($input['shipping_shop'] ?? ($sameAsCustomer === 'yes' ? $customer['customer_shop'] : ''))),
            'shipping_zip' => trim((string) ($input['shipping_zip'] ?? ($sameAsCustomer === 'yes' ? $customer['customer_zip'] : ''))),
            'shipping_address1' => trim((string) ($input['shipping_address1'] ?? ($sameAsCustomer === 'yes' ? $customer['customer_address1'] : ''))),
            'shipping_address2' => trim((string) ($input['shipping_address2'] ?? ($sameAsCustomer === 'yes' ? $customer['customer_address2'] : ''))),
            'shipping_address3' => trim((string) ($input['shipping_address3'] ?? ($sameAsCustomer === 'yes' ? $customer['customer_address3'] : ''))),
            'shipping_tel' => trim((string) ($input['shipping_tel'] ?? ($sameAsCustomer === 'yes' ? $customer['customer_tel'] : ''))),
            'same_as_customer' => $sameAsCustomer,
            'payment' => trim((string) ($input['payment'] ?? 'bank')),
            'coolpoint_use' => trim((string) ($input['coolpoint_use'] ?? '0')),
            'delivery_time' => trim((string) ($input['delivery_time'] ?? '')),
            'notes' => trim((string) ($input['notes'] ?? '')),
        ]);
    }

    private function validate(array $data, array $summary): array
    {
        $errors = [];
        $lines = $summary['lines'] ?? [];

        if ($lines === []) {
            $errors[] = 'カートが空のため、ご注文手続きへ進めません。';
        }

        foreach (['customer_name', 'customer_zip', 'customer_address1', 'customer_address2', 'customer_tel', 'customer_email'] as $field) {
            if (($data[$field] ?? '') === '') {
                $errors[] = 'ご購入者情報の必須項目を入力してください。';
                break;
            }
        }

        foreach (['shipping_name', 'shipping_zip', 'shipping_address1', 'shipping_address2', 'shipping_tel'] as $field) {
            if (($data[$field] ?? '') === '') {
                $errors[] = 'お届け先情報の必須項目を入力してください。';
                break;
            }
        }

        if (!filter_var((string) ($data['customer_email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'メールアドレスを正しく入力してください。';
        }

        if (!in_array((string) ($data['payment'] ?? ''), ['bank', 'yamato', 'card'], true)) {
            $errors[] = 'お支払い方法を選択してください。';
        }

        if (!preg_match('/^\d+$/', (string) ($data['coolpoint_use'] ?? '0'))) {
            $errors[] = '利用ポイントは半角数字で入力してください。';
        }

        if ((int) ($data['coolpoint_use'] ?? 0) !== (int) ($summary['point_discount'] ?? 0)) {
            $errors[] = '利用ポイントを確認して、もう一度お試しください。';
        }

        return array_merge($errors, $summary['errors'] ?? []);
    }

    private function buildOrderData(array $data, array $summary): array
    {
        $user = $this->currentProfile();
        $memberFlag = match ((string) ($user['member_type'] ?? 'guest')) {
            'biz' => '2',
            'net' => '1',
            default => '0',
        };

        return [
            'session_id' => session_id(),
            'user_id' => (int) ($user['id'] ?? 0),
            'notes' => (string) ($data['notes'] ?? ''),
            'payment' => (string) ($data['payment'] ?? 'bank'),
            'delivery_time' => (string) ($data['delivery_time'] ?? ''),
            'member_label' => account_label(is_array($user) ? $user : null),
            'member_flag' => $memberFlag,
            'customer_zip' => (string) ($data['customer_zip'] ?? ''),
            'customer_address' => trim(
                implode(' ', array_filter([
                    (string) ($data['customer_address1'] ?? ''),
                    (string) ($data['customer_address2'] ?? ''),
                    (string) ($data['customer_address3'] ?? ''),
                ]))
            ),
            'customer_tel' => preg_replace('/[^0-9]/', '', (string) ($data['customer_tel'] ?? '')) ?? '',
            'customer_email' => (string) ($data['customer_email'] ?? ''),
            'shipping_name' => (string) ($data['shipping_name'] ?? ''),
            'shipping_shop' => (string) ($data['shipping_shop'] ?? ''),
            'subtotal' => (int) ($summary['subtotal'] ?? 0),
            'shipping_fee' => (int) ($summary['shipping_fee'] ?? 0),
            'payment_fee' => (int) ($summary['payment_fee'] ?? 0),
            'coolpoint_use' => (int) ($summary['point_discount'] ?? 0),
            'coolpoint_earned' => (int) ($summary['point_earned'] ?? 0),
            'total' => (int) ($summary['total'] ?? 0),
        ];
    }

    private function sendOrderMails(array $order, array $lines, int $orderId, ?string $paypalUrl): void
    {
        $paymentLabel = $this->paymentLabel((string) ($order['payment'] ?? ''));
        $mail = new MailService();
        $lineBody = '';

        foreach ($lines as $line) {
            $product = $line['product'];
            $lineBody .= sprintf(
                "品番: %s\n商品名: %s %s\n単価: %s円\n数量: %d\n小計: %s円\n\n",
                (string) ($product['parts_num'] ?? ''),
                (string) ($product['make'] ?? ''),
                (string) ($product['name'] ?? ''),
                number_format((int) ($line['unit_price'] ?? 0)),
                (int) ($line['qty'] ?? 0),
                number_format((int) ($line['subtotal'] ?? 0))
            );
        }

        $body = "注文番号: {$orderId}\n";
        $body .= ($order['shipping_name'] ?? '') . " 様\n\n";
        $body .= "ご注文を受け付けました。\n\n";
        $body .= "お支払い方法: {$paymentLabel}\n";
        $body .= "小計: " . number_format((int) ($order['subtotal'] ?? 0)) . "円\n";
        $body .= "送料: " . number_format((int) ($order['shipping_fee'] ?? 0)) . "円\n";
        $body .= "手数料: " . number_format((int) ($order['payment_fee'] ?? 0)) . "円\n";
        if ((int) ($order['coolpoint_use'] ?? 0) > 0) {
            $body .= "クールポイント利用: -" . number_format((int) ($order['coolpoint_use'] ?? 0)) . "円\n";
        }
        $body .= "合計: " . number_format((int) ($order['total'] ?? 0)) . "円\n\n";
        $body .= $lineBody;
        if (array_key_exists('coolpoint_earned', $order)) {
            $body .= "今回加算予定ポイント: " . number_format((int) ($order['coolpoint_earned'] ?? 0)) . " pt\n\n";
        }

        if (($order['payment'] ?? '') === 'card' && $paypalUrl !== null) {
            $body .= "PayPalでのお支払いは、下記URLからお手続きください。\n";
            $body .= $paypalUrl . "\n";
            $body .= "注文完了後、PayPalからの案内または上記URLより決済をお願いします。\n\n";
        } elseif (($order['payment'] ?? '') === 'bank') {
            $body .= "銀行振込のご案内は注文確認後に別途ご連絡します。\n\n";
        } elseif (($order['payment'] ?? '') === 'yamato') {
            $body .= "代引手数料を含めた金額で発送時にご請求します。\n\n";
        }

        $body .= "お届け先: " . ($order['shipping_name'] ?? '') . "\n";
        $body .= ($order['shipping_shop'] ?? '') . "\n";
        $body .= ($order['customer_email'] ?? '') . "\n";

        $subject = 'ご注文ありがとうございます <注文番号:' . $orderId . '>';
        $mail->send((string) ($order['customer_email'] ?? ''), $subject, $body);
        $mail->sendToAdmin('注文通知 ' . $orderId . ' / ' . $paymentLabel, $body);
    }

    private function paymentLabel(string $payment): string
    {
        return match ($payment) {
            'yamato' => '代金引換',
            'card' => 'PayPal案内',
            default => '銀行振込',
        };
    }

    private function currentProfile(): ?array
    {
        $user = current_user();
        if (!is_array($user)) {
            return null;
        }

        return (new UserRepository())->findById((int) ($user['id'] ?? 0));
    }

    private function deliveryList(): array
    {
        $user = current_user();
        if (!is_array($user)) {
            return [];
        }

        return (new DeliveryRepository())->listByUserId((int) ($user['id'] ?? 0));
    }

    private function buildPayPalUrl(int $orderId, int $userId, int $amount): string
    {
        $template = (string) config('PAYPAL_PAYMENT_URL_TEMPLATE', '');
        if ($template === '') {
            $template = rtrim((string) config('APP_URL', ''), '/') . '/cart/expresscheckout2.php?s={order_id}&u={user_id}';
        }

        return str_replace(
            ['{order_id}', '{user_id}', '{amount}'],
            [(string) $orderId, (string) $userId, (string) $amount],
            $template
        );
    }

    private function coolpointMeta(): array
    {
        $user = $this->currentProfile();
        $memberType = (string) ($user['member_type'] ?? 'guest');
        $bizStatus = (string) ($user['biz_status'] ?? '');
        $eligible = isset($user['id']) && (
            $memberType === 'net'
            || ($memberType === 'biz' && $bizStatus === 'approved')
        );
        $rate = match ($memberType) {
            'biz' => (float) config('COOLPOINT_RATE_BIZ', 0.02),
            'net' => (float) config('COOLPOINT_RATE_MEMBER', 0.01),
            default => 0.0,
        };

        if (!$eligible) {
            return [
                'eligible' => false,
                'balance' => 0,
                'rate' => 0.0,
            ];
        }

        $balance = (new CoolpointRepository())->currentBalanceByUserId((int) ($user['id'] ?? 0));

        return [
            'eligible' => true,
            'balance' => max(0, (int) ($balance ?? 0)),
            'rate' => $rate,
        ];
    }
}
