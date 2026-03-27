<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\InquiryRepository;

final class InquiryController
{
    public function show(): void
    {
        echo render('layout', [
            'title' => 'お問い合わせ',
            'content' => render('inquiry', [
                'errors' => [],
                'success' => false,
            ]),
        ]);
    }

    public function submit(): void
    {
        $data = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'tel' => trim((string) ($_POST['tel'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? '')),
            'katasiki' => trim((string) ($_POST['katasiki'] ?? '')),
            'parts_num' => trim((string) ($_POST['parts_num'] ?? '')),
            'toc' => trim((string) ($_POST['toc'] ?? '')),
            'message' => trim((string) ($_POST['message'] ?? '')),
        ];

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
            ]),
        ]);
    }
}
