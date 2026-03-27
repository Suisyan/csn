<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\InquiryController;
use App\Controllers\ProductController;
use App\Controllers\SearchController;

require dirname(__DIR__) . '/src/bootstrap.php';

$router = app_router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/search', [SearchController::class, 'index']);
$router->get('/product/{id}', [ProductController::class, 'show']);
$router->get('/inquiry', [InquiryController::class, 'show']);
$router->post('/inquiry', [InquiryController::class, 'submit']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', current_path());
