<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class PayPalExpressService
{
    public function startCheckout(array $request): array
    {
        return $this->call('SetExpressCheckout', $request);
    }

    public function fetchCheckoutDetails(string $token): array
    {
        return $this->call('GetExpressCheckoutDetails', [
            'TOKEN' => $token,
        ]);
    }

    public function completeCheckout(string $token, string $payerId, int $amount): array
    {
        return $this->call('DoExpressCheckoutPayment', [
            'TOKEN' => $token,
            'PAYERID' => $payerId,
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
            'PAYMENTREQUEST_0_AMT' => (string) $amount,
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'JPY',
            'IPADDRESS' => (string) ($_SERVER['SERVER_NAME'] ?? 'localhost'),
        ]);
    }

    public function redirectUrl(string $token): string
    {
        return $this->paypalBaseUrl() . urlencode($token);
    }

    private function call(string $method, array $params): array
    {
        $username = trim((string) config('PAYPAL_API_USERNAME', ''));
        $password = trim((string) config('PAYPAL_API_PASSWORD', ''));
        $signature = trim((string) config('PAYPAL_API_SIGNATURE', ''));

        if ($username === '' || $password === '' || $signature === '') {
            throw new RuntimeException('PayPal API 設定が未登録のため、Express Checkout を開始できません。');
        }

        $payload = array_merge([
            'METHOD' => $method,
            'VERSION' => (string) config('PAYPAL_API_VERSION', '124'),
            'USER' => $username,
            'PWD' => $password,
            'SIGNATURE' => $signature,
            'BUTTONSOURCE' => (string) config('PAYPAL_API_BUTTON_SOURCE', 'PP-ECWizard'),
        ], $params);

        $ch = curl_init($this->apiEndpoint());
        if ($ch === false) {
            throw new RuntimeException('PayPal 通信の初期化に失敗しました。');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('PayPal 通信に失敗しました: ' . $error);
        }

        $statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($statusCode >= 400) {
            throw new RuntimeException('PayPal API 応答エラー: HTTP ' . $statusCode);
        }

        parse_str($response, $parsed);
        $result = [];
        foreach ($parsed as $key => $value) {
            $result[(string) $key] = is_string($value) ? urldecode($value) : $value;
        }

        return $result;
    }

    private function apiEndpoint(): string
    {
        return $this->isSandbox()
            ? 'https://api-3t.sandbox.paypal.com/nvp'
            : 'https://api-3t.paypal.com/nvp';
    }

    private function paypalBaseUrl(): string
    {
        return $this->isSandbox()
            ? 'https://www.sandbox.paypal.com/jp/webscr?cmd=_express-checkout&token='
            : 'https://www.paypal.com/jp/cgi-bin/webscr?cmd=_express-checkout&token=';
    }

    private function isSandbox(): bool
    {
        return strtolower((string) config('PAYPAL_API_MODE', 'live')) === 'sandbox';
    }
}
