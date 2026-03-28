<?php

declare(strict_types=1);

namespace App\Repositories;

final class CartRepository
{
    private const SESSION_KEY = 'cart_items';

    public function all(): array
    {
        $items = $_SESSION[self::SESSION_KEY] ?? [];

        return is_array($items) ? $items : [];
    }

    public function add(int $productId, int $qty = 1): void
    {
        if ($productId <= 0 || $qty <= 0) {
            return;
        }

        $items = $this->all();
        $items[$productId] = ((int) ($items[$productId] ?? 0)) + $qty;
        $_SESSION[self::SESSION_KEY] = $items;
    }

    public function update(int $productId, int $qty): void
    {
        $items = $this->all();

        if ($qty <= 0) {
            unset($items[$productId]);
        } else {
            $items[$productId] = $qty;
        }

        $_SESSION[self::SESSION_KEY] = $items;
    }

    public function remove(int $productId): void
    {
        $items = $this->all();
        unset($items[$productId]);
        $_SESSION[self::SESSION_KEY] = $items;
    }
}
