<?php

declare(strict_types=1);

use App\Controllers\AccountController;
use App\Controllers\AdminAuthController;
use App\Controllers\AdminDashboardController;
use App\Controllers\AdminMemberController;
use App\Controllers\AdminOrderController;
use App\Controllers\AdminProductController;
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

require __DIR__ . '/src/bootstrap.php';

header('X-Robots-Tag: noindex, nofollow, noarchive', true);

try {
    $router = app_router();

    $router->get('/', [HomeController::class, 'index']);
    $router->get('/ra.php', [HomeController::class, 'index']);
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
    $router->get('/admin', [AdminDashboardController::class, 'index']);
    $router->get('/admin/login', [AdminAuthController::class, 'showLogin']);
    $router->post('/admin/login', [AdminAuthController::class, 'login']);
    $router->post('/admin/logout', [AdminAuthController::class, 'logout']);
    $router->get('/admin/orders', [AdminOrderController::class, 'index']);
    $router->get('/admin/orders/{id}', [AdminOrderController::class, 'show']);
    $router->get('/admin/orders/show', [AdminOrderController::class, 'show']);
    $router->get('/admin/order', [AdminOrderController::class, 'show']);
    $router->get('/admin/order/{id}', [AdminOrderController::class, 'show']);
    $router->post('/admin/orders/{id}/bank', [AdminOrderController::class, 'saveBank']);
    $router->post('/admin/orders/{id}/shipping', [AdminOrderController::class, 'saveShipping']);
    $router->get('/admin/products', [AdminProductController::class, 'index']);
    $router->get('/admin/members', [AdminMemberController::class, 'index']);
    $router->get('/admin/inquiries', [AdminDashboardController::class, 'inquiries']);
    $router->get('/admin/special-member', [AdminSpecialMemberController::class, 'index']);
    $router->get('/admin/special-members', [AdminSpecialMemberController::class, 'index']);
    $router->post('/admin/special-member/{id}/approve', [AdminSpecialMemberController::class, 'approve']);
    $router->post('/admin/special-member/{id}/reject', [AdminSpecialMemberController::class, 'reject']);
    $router->post('/admin/special-member/settings', [AdminSpecialMemberController::class, 'saveSettings']);
    $router->post('/admin/special-members/{id}/approve', [AdminSpecialMemberController::class, 'approve']);
    $router->post('/admin/special-members/{id}/reject', [AdminSpecialMemberController::class, 'reject']);
    $router->post('/admin/special-members/settings', [AdminSpecialMemberController::class, 'saveSettings']);
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
