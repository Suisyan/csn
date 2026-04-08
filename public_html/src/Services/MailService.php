<?php

declare(strict_types=1);

namespace App\Services;

final class MailService
{
    public function send(string $to, string $subject, string $body): bool
    {
        $from = (string) config('MAIL_FROM', '');
        $result = function_exists('mb_send_mail')
            ? $this->sendJapaneseMail($to, $subject, $body, $from)
            : $this->sendFallbackMail($to, $subject, $body, $from);

        if (!$result) {
            error_log(sprintf('Mail send failed. to=%s subject=%s', $to, $subject));
        }

        return $result;
    }

    public function sendToAdmin(string $subject, string $body): bool
    {
        $to = (string) config('MAIL_TO', '');
        if ($to === '') {
            return false;
        }

        return $this->send($to, $subject, $body);
    }

    private function sendJapaneseMail(string $to, string $subject, string $body, string $from): bool
    {
        mb_language('Japanese');
        mb_internal_encoding('UTF-8');

        $encodedBody = mb_convert_encoding($body, 'ISO-2022-JP-MS', 'UTF-8');
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=ISO-2022-JP',
            'Content-Transfer-Encoding: 7bit',
        ];
        $params = '';

        if ($from !== '') {
            $headers[] = 'From: ' . $from;
            $headers[] = 'Reply-To: ' . $from;
            $headers[] = 'Return-Path: ' . $from;
            $params = '-f' . $from;
        }

        return mb_send_mail(
            $to,
            $subject,
            $encodedBody,
            implode("\r\n", $headers),
            $params
        );
    }

    private function sendFallbackMail(string $to, string $subject, string $body, string $from): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];
        $params = '';

        if ($from !== '') {
            $headers[] = 'From: ' . $from;
            $headers[] = 'Reply-To: ' . $from;
            $headers[] = 'Return-Path: ' . $from;
            $params = '-f' . $from;
        }

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        return mail(
            $to,
            $encodedSubject,
            $body,
            implode("\r\n", $headers),
            $params
        );
    }
}
