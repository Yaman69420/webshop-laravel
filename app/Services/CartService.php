<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'cart';

    /**
     * Get all cart items.
     *
     * @return Collection<int, array{product_id: int, quantity: int}>
     */
    public function items(): Collection
    {
        return collect(Session::get(self::SESSION_KEY, []));
    }

    /**
     * Add a product to the cart.
     */
    public function add(int $productId, int $quantity = 1): void
    {
        $cart = Session::get(self::SESSION_KEY, []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'product_id' => $productId,
                'quantity' => $quantity,
            ];
        }

        Session::put(self::SESSION_KEY, $cart);
    }

    /**
     * Update quantity of a cart item.
     */
    public function updateQuantity(int $productId, int $quantity): void
    {
        $cart = Session::get(self::SESSION_KEY, []);

        if ($quantity <= 0) {
            unset($cart[$productId]);
        } elseif (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $quantity;
        }

        Session::put(self::SESSION_KEY, $cart);
    }

    /**
     * Remove a product from the cart.
     */
    public function remove(int $productId): void
    {
        $cart = Session::get(self::SESSION_KEY, []);
        unset($cart[$productId]);
        Session::put(self::SESSION_KEY, $cart);
    }

    /**
     * Clear the entire cart.
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Get cart items with loaded product models.
     *
     * @return Collection<int, array{product: Product, quantity: int}>
     */
    public function itemsWithProducts(): Collection
    {
        $items = $this->items();

        if ($items->isEmpty()) {
            return collect();
        }

        $products = Product::whereIn('id', $items->pluck('product_id'))->with('category')->get()->keyBy('id');

        return $items->map(function (array $item) use ($products) {
            $product = $products->get($item['product_id']);

            if (! $product) {
                return null;
            }

            return [
                'product' => $product,
                'quantity' => $item['quantity'],
            ];
        })->filter();
    }

    /**
     * Calculate the total in cents.
     */
    public function totalInCents(): int
    {
        return $this->itemsWithProducts()->sum(function (array $item): int {
            return $item['product']->price_in_cents * $item['quantity'];
        });
    }

    /**
     * Get the total number of items in the cart.
     */
    public function count(): int
    {
        return $this->items()->sum('quantity');
    }
}
