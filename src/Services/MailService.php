<?php

declare(strict_types=1);

namespace App\Services;

final class MailService
{
    public function send(string $to, string $subject, string $body): bool
    {
        $from = (string) config('MAIL_FROM', '');
        $headers = [];

        if ($from !== '') {
            $headers[] = 'From: ' . $from;
            $headers[] = 'Reply-To: ' . $from;
        }

        $headers[] = 'Content-Type: text/plain; charset=UTF-8';

        if (function_exists('mb_send_mail')) {
            mb_language('Japanese');
            mb_internal_encoding('UTF-8');

            return mb_send_mail($to, $subject, $body, implode("\r\n", $headers));
        }

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    public function sendToAdmin(string $subject, string $body): bool
    {
        $to = (string) config('MAIL_TO', '');
        if ($to === '') {
            return false;
        }

        return $this->send($to, $subject, $body);
    }
}
