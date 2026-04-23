<?php

namespace App\Actions\Cart;

use App\Models\Product;
use App\Services\CartService;

class AddToCartAction
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function execute(int $productId, int $quantity = 1): void
    {
        $product = Product::findOrFail($productId);

        if ($product->stock < $quantity) {
            throw new \RuntimeException('Onvoldoende voorraad.');
        }

        $this->cartService->add($productId, $quantity);
    }
}
