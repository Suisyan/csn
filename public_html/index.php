<?php

declare(strict_types=1);

use App\Controllers\AccountController;
use App\Controllers\AdminSpecialMemberController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\HomeController;
use App\Controllers\InquiryController;
use App\Controllers\ProductController;
use App\Controllers\PayPalExpressController;
use App\Controllers\SearchController;
use App\Controllers\SpecialMemberController;

require dirname(__DIR__) . '/src/bootstrap.php';

header('X-Robots-Tag: noindex, nofollow, noarchive', true);

try {
    $router = app_router();

    $router->get('/', [HomeController::class, 'index']);
    $router->get('/search', [SearchController::class, 'index']);
    $router->get('/product/{id}', [ProductController::class, 'show']);
    $router->get('/inquiry', [InquiryController::class, 'show']);
    $router->post('/inquiry', [InquiryController::class, 'submit']);
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->post('/logout', [AuthController::class, 'logout']);
    $router->get('/account', [AccountController::class, 'show']);
    $router->post('/account/delivery/save', [AccountController::class, 'saveDelivery']);
    $router->post('/account/delivery/{id}/delete', [AccountController::class, 'deleteDelivery']);
    $router->get('/special-member/apply', [SpecialMemberController::class, 'showRegister']);
    $router->post('/special-member/apply', [SpecialMemberController::class, 'submitRegister']);
    $router->get('/special-member/register', [SpecialMemberController::class, 'showRegister']);
    $router->post('/special-member/register', [SpecialMemberController::class, 'submitRegister']);
    $router->get('/special-member/upload', [SpecialMemberController::class, 'showUpload']);
    $router->post('/special-member/upload', [SpecialMemberController::class, 'submitUpload']);
    $router->get('/admin/special-member', [AdminSpecialMemberController::class, 'index']);
    $router->get('/admin/special-members', [AdminSpecialMemberController::class, 'index']);
    $router->post('/admin/special-member/{id}/approve', [AdminSpecialMemberController::class, 'approve']);
    $router->post('/admin/special-member/{id}/reject', [AdminSpecialMemberController::class, 'reject']);
    $router->post('/admin/special-members/{id}/approve', [AdminSpecialMemberController::class, 'approve']);
    $router->post('/admin/special-members/{id}/reject', [AdminSpecialMemberController::class, 'reject']);
    $router->get('/cart', [CartController::class, 'show']);
    $router->post('/cart/add', [CartController::class, 'add']);
    $router->post('/cart/update', [CartController::class, 'update']);
    $router->post('/cart/remove', [CartController::class, 'remove']);
    $router->get('/checkout', [CheckoutController::class, 'show']);
    $router->post('/checkout/confirm', [CheckoutController::class, 'confirm']);
    $router->post('/checkout/complete', [CheckoutController::class, 'complete']);
    $router->get('/cart/expresscheckout2.php', [PayPalExpressController::class, 'start']);
    $router->get('/cart/pp_confirm2.php', [PayPalExpressController::class, 'review']);
    $router->get('/thanks_pp.php', [PayPalExpressController::class, 'complete']);
    $router->post('/thanks_pp.php', [PayPalExpressController::class, 'complete']);
    $router->get('/pp_cancel.php', [PayPalExpressController::class, 'cancel']);

    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', current_path());
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "APP_ERROR\n";
    echo 'TYPE=' . $e::class . "\n";
    echo 'MESSAGE=' . $e->getMessage() . "\n";
    echo 'FILE=' . $e->getFile() . "\n";
    echo 'LINE=' . $e->getLine() . "\n";
}
