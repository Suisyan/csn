<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\InquiryRepository;

final class InquiryController
{
    public function show(): void
    {
        $prefill = $this->normalizeInput($_GET);

        echo render('layout', [
            'title' => 'お問い合わせ',
            'content' => render('inquiry', [
                'errors' => [],
                'success' => false,
                'prefill' => $prefill,
                'fitMode' => $this->isFitMode($prefill),
            ]),
        ]);
    }

    public function submit(): void
    {
        $data = $this->normalizeInput($_POST);

        $errors = [];

        if ($data['name'] === '') {
            $errors[] = 'お名前を入力してください。';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'E-mailを正しく入力してください。';
        }
        if ($data['category'] === '') {
            $errors[] = '部品区分を選択してください。';
        }
        if ($data['katasiki'] === '') {
            $errors[] = '型式を入力してください。';
        }
        if ($data['toc'] === '') {
            $errors[] = 'ミッションを選択してください。';
        }

        $success = false;
        if ($errors === []) {
            $success = (new InquiryRepository())->store($data);
            if (!$success) {
                $errors[] = 'お問い合わせの保存に失敗しました。接続設定をご確認ください。';
            }
        }

        echo render('layout', [
            'title' => 'お問い合わせ',
            'content' => render('inquiry', [
                'errors' => $errors,
                'success' => $success,
                'prefill' => $data,
                'fitMode' => $this->isFitMode($data),
            ]),
        ]);
    }

    private function normalizeInput(array $input): array
    {
        return [
            'source' => trim((string) ($input['source'] ?? '')),
            'name' => trim((string) ($input['name'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
            'tel' => trim((string) ($input['tel'] ?? '')),
            'category' => trim((string) ($input['category'] ?? '')),
            'katasiki' => trim((string) ($input['katasiki'] ?? '')),
            'parts_num' => trim((string) ($input['parts_num'] ?? '')),
            'syamei1' => trim((string) ($input['syamei1'] ?? '')),
            'syamei2' => trim((string) ($input['syamei2'] ?? '')),
            'toc' => trim((string) ($input['toc'] ?? '')),
            'message' => trim((string) ($input['message'] ?? '')),
        ];
    }

    private function isFitMode(array $data): bool
    {
        if (($data['source'] ?? '') === 'fit') {
            return true;
        }

        foreach (['category', 'parts_num', 'katasiki', 'syamei1', 'syamei2', 'toc'] as $key) {
            if (($data[$key] ?? '') !== '') {
                return true;
            }
        }

        return false;
    }
}
