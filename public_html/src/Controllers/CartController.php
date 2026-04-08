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
            $availableStock = max(0, (int) ($product['stock'] ?? 0));
            if ($availableStock > 0) {
                $quantity = min($quantity, $availableStock);
            }
            $unitPrice = (int) ($product['display_price'] ?? 0);
            $subtotal = $unitPrice * $quantity;
            $total += $subtotal;

            $lines[] = [
                'product' => $product,
                'qty' => $quantity,
                'max_qty' => $availableStock,
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
        $product = (new ProductRepository())->findById($productId, current_user());

        if ($product === null) {
            header('Location: ' . $this->sanitizeRedirect($redirectTo));
            exit;
        }

        $availableStock = max(0, (int) ($product['stock'] ?? 0));
        if ($availableStock <= 0) {
            header('Location: ' . $this->sanitizeRedirect($redirectTo));
            exit;
        }

        $currentQty = (int) ((new CartRepository())->all()[$productId] ?? 0);
        $qty = min($qty, max(0, $availableStock - $currentQty));

        if ($qty <= 0) {
            header('Location: ' . $this->sanitizeRedirect($redirectTo));
            exit;
        }

        (new CartRepository())->add($productId, $qty);

        header('Location: ' . $this->sanitizeRedirect($redirectTo));
        exit;
    }

    public function update(array $params = []): void
    {
        $quantities = $_POST['qty'] ?? [];
        if (is_array($quantities)) {
            $cart = new CartRepository();
            $products = new ProductRepository();
            $viewer = current_user();

            foreach ($quantities as $productId => $qty) {
                $productId = (int) $productId;
                $requestedQty = max(0, (int) $qty);
                $product = $products->findById($productId, $viewer);

                if ($product === null) {
                    $cart->remove($productId);
                    continue;
                }

                $availableStock = max(0, (int) ($product['stock'] ?? 0));
                if ($availableStock <= 0) {
                    $cart->remove($productId);
                    continue;
                }

                $cart->update($productId, min($requestedQty, $availableStock));
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
