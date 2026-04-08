<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\SpecialMemberRequestRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\MailService;
use RuntimeException;

final class SpecialMemberController
{
    public function showRegister(): void
    {
        echo render('layout', [
            'title' => 'Special Member Registration',
            'content' => render('special_member_apply', [
                'errors' => [],
                'success' => false,
                'prefill' => $this->prefill([], $_POST),
                'user' => current_user(),
            ]),
        ]);
    }

    public function submitRegister(): void
    {
        $data = $this->prefill([], $_POST);
        $errors = $this->validateRegistration($data);
        $users = new UserRepository();

        if ($users->findActiveCandidatesByEmail($data['email']) !== []) {
            $errors[] = 'This email address is already registered. Please log in.';
        }

        if ($errors === []) {
            $passwordHash = AuthService::makePasswordHash($data['password']);
            $userId = $users->createApplicant($data, $passwordHash);

            if ($userId !== null) {
                $requestId = (new SpecialMemberRequestRepository())->create($userId, $data, 'docs_pending');
                if ($requestId !== null) {
                    $this->sendRegistrationMail($data['email'], $data['password']);

                    echo render('layout', [
                        'title' => 'Special Member Registration',
                        'content' => render('special_member_apply', [
                            'errors' => [],
                            'success' => true,
                            'prefill' => $data,
                            'user' => current_user(),
                        ]),
                    ]);
                    return;
                }
            }

            $errors[] = 'Failed to save the registration.';
        }

        echo render('layout', [
            'title' => 'Special Member Registration',
            'content' => render('special_member_apply', [
                'errors' => $errors,
                'success' => false,
                'prefill' => $data,
                'user' => current_user(),
            ]),
        ]);
    }

    public function showUpload(): void
    {
        $user = require_login();
        $request = (new SpecialMemberRequestRepository())->findLatestByUserId((int) ($user['id'] ?? 0));

        echo render('layout', [
            'title' => 'Special Member Upload',
            'content' => render('special_member_upload', [
                'errors' => [],
                'success' => false,
                'request' => $request,
                'user' => $user,
            ]),
        ]);
    }

    public function submitUpload(): void
    {
        $user = require_login();
        $repository = new SpecialMemberRequestRepository();
        $request = $repository->findLatestByUserId((int) ($user['id'] ?? 0));
        $errors = $this->validateUpload($user, $request, $_FILES['card_image'] ?? null);

        if ($errors === [] && is_array($request)) {
            try {
                $fileMeta = $this->storeCardFile((int) $request['id'], $_FILES['card_image']);
                $saved = $repository->addFile((int) $request['id'], $fileMeta);

                if ($saved) {
                    $repository->updateStatus((int) $request['id'], 'pending');
                    (new UserRepository())->updateBizStatus((int) ($user['id'] ?? 0), 'pending');
                    $user['biz_status'] = 'pending';
                    refresh_current_user($user);

                    echo render('layout', [
                        'title' => 'Special Member Upload',
                        'content' => render('special_member_upload', [
                            'errors' => [],
                            'success' => true,
                            'request' => $repository->findLatestByUserId((int) ($user['id'] ?? 0)),
                            'user' => $user,
                        ]),
                    ]);
                    return;
                }

                $this->deleteStoredFile($fileMeta['absolute_path']);
                $errors[] = 'Failed to save the uploaded file.';
            } catch (RuntimeException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        echo render('layout', [
            'title' => 'Special Member Upload',
            'content' => render('special_member_upload', [
                'errors' => $errors,
                'success' => false,
                'request' => $request,
                'user' => $user,
            ]),
        ]);
    }

    private function prefill(array $profile, array $input): array
    {
        return [
            'company_name' => trim((string) ($input['company_name'] ?? ($profile['b_name'] ?? ''))),
            'shop_name' => trim((string) ($input['shop_name'] ?? ($profile['u_shop'] ?? ''))),
            'contact_name' => trim((string) ($input['contact_name'] ?? ($profile['u_name'] ?? ''))),
            'email' => trim((string) ($input['email'] ?? ($profile['email'] ?? ''))),
            'tel' => trim((string) ($input['tel'] ?? ($profile['tel'] ?? ''))),
            'zip' => trim((string) ($input['zip'] ?? ($profile['zip'] ?? ''))),
            'address_line1' => trim((string) ($input['address_line1'] ?? ($profile['add1'] ?? ''))),
            'address_line2' => trim((string) ($input['address_line2'] ?? ($profile['add2'] ?? ''))),
            'address_line3' => trim((string) ($input['address_line3'] ?? ($profile['add3'] ?? ''))),
            'website_url' => trim((string) ($input['website_url'] ?? '')),
            'business_type' => trim((string) ($input['business_type'] ?? '')),
            'notes' => trim((string) ($input['notes'] ?? '')),
            'password' => (string) ($input['password'] ?? ''),
            'password_confirm' => (string) ($input['password_confirm'] ?? ''),
            'agreed_terms' => trim((string) ($input['agreed_terms'] ?? '')),
        ];
    }

    private function validateRegistration(array $data): array
    {
        $errors = [];

        foreach (['company_name', 'contact_name', 'email', 'tel', 'password', 'password_confirm'] as $field) {
            if ($data[$field] === '') {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }

        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email format is invalid.';
        }

        if ($data['password'] !== '' && strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if ($data['password'] !== $data['password_confirm']) {
            $errors[] = 'Password confirmation does not match.';
        }

        if ($data['agreed_terms'] !== 'yes') {
            $errors[] = 'You must agree to the special member terms before submitting.';
        }

        return $errors;
    }

    private function validateUpload(array $user, ?array $request, mixed $file): array
    {
        $errors = [];
        $bizStatus = (string) ($user['biz_status'] ?? '');

        if (!is_array($request)) {
            $errors[] = 'No registration request was found for this account.';
            return $errors;
        }

        if (!in_array($bizStatus, ['docs_pending', 'rejected'], true)) {
            $errors[] = 'This account is not waiting for card upload.';
        }

        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Business card image is required.';
            return $errors;
        }

        if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed.';
            return $errors;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            $errors[] = 'Only jpg, jpeg, png, and pdf files are allowed.';
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > 5 * 1024 * 1024) {
            $errors[] = 'File size must be 5MB or smaller.';
        }

        return $errors;
    }

    private function storeCardFile(int $requestId, array $file): array
    {
        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $storedName = sprintf('card_%d_%s.%s', $requestId, bin2hex(random_bytes(8)), $extension);

        $basePath = (string) config('base_path');
        $relativeDir = '/storage/uploads/special-member/' . $requestId;
        $absoluteDir = $basePath . $relativeDir;

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new RuntimeException('Failed to prepare upload directory.');
        }

        $absolutePath = $absoluteDir . '/' . $storedName;
        if (!move_uploaded_file((string) ($file['tmp_name'] ?? ''), $absolutePath)) {
            throw new RuntimeException('Failed to move the uploaded file.');
        }

        return [
            'stored_name' => $storedName,
            'original_name' => (string) ($file['name'] ?? ''),
            'file_path' => $relativeDir . '/' . $storedName,
            'mime_type' => (string) ($file['type'] ?? 'application/octet-stream'),
            'file_size' => (int) ($file['size'] ?? 0),
            'absolute_path' => $absolutePath,
        ];
    }

    private function deleteStoredFile(string $absolutePath): void
    {
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function sendRegistrationMail(string $email, string $plainPassword): void
    {
        $appUrl = rtrim((string) config('APP_URL', ''), '/');

        $userBody = implode("\n", [
            '特別会員申請を受け付けました。',
            '',
            '次の手順でログインし、名刺画像アップロードを完了してください。',
            $appUrl . '/login',
            '',
            'Email: ' . $email,
            'Password: ' . $plainPassword,
            '',
            'ログイン後のアップロード先:',
            $appUrl . '/special-member/upload',
        ]);

        $mail = new MailService();
        $mail->send($email, '特別会員申請の受付', $userBody);

        $adminBody = implode("\n", [
            '特別会員申請を受け付けました。',
            '',
            'Email: ' . $email,
            'Login URL: ' . $appUrl . '/login',
            'Upload URL: ' . $appUrl . '/special-member/upload',
        ]);
        $mail->sendToAdmin('特別会員申請の管理者通知', $adminBody);
    }
}
