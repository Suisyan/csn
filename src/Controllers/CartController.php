<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;

final class CartController
{
    public function show(array $params = []): void
    {
        $cart = new CartRepository();
        $products = new ProductRepository();
        $viewer = current_user();
        $lines = [];
        $total = 0;

        foreach ($cart->all() as $productId => $qty) {
            $product = $products->findById((int) $productId, $viewer);
            if ($product === null) {
                continue;
            }

            $quantity = max(1, (int) $qty);
            $unitPrice = (int) ($product['display_price'] ?? 0);
            $subtotal = $unitPrice * $quantity;
            $total += $subtotal;

            $lines[] = [
                'product' => $product,
                'qty' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ];
        }

        echo render('layout', [
            'title' => 'カート',
            'content' => render('cart', [
                'lines' => $lines,
                'total' => $total,
            ]),
        ]);
    }

    public function add(array $params = []): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $qty = max(1, (int) ($_POST['qty'] ?? 1));
        $redirectTo = (string) ($_POST['redirect_to'] ?? '/cart');

        (new CartRepository())->add($productId, $qty);

        header('Location: ' . $this->sanitizeRedirect($redirectTo));
        exit;
    }

    public function update(array $params = []): void
    {
        $quantities = $_POST['qty'] ?? [];
        if (is_array($quantities)) {
            $cart = new CartRepository();
            foreach ($quantities as $productId => $qty) {
                $cart->update((int) $productId, max(0, (int) $qty));
            }
        }

        header('Location: /cart');
        exit;
    }

    public function remove(array $params = []): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);
        (new CartRepository())->remove($productId);

        header('Location: /cart');
        exit;
    }

    private function sanitizeRedirect(string $path): string
    {
        if ($path === '' || !str_starts_with($path, '/')) {
            return '/cart';
        }

        return $path;
    }
}
