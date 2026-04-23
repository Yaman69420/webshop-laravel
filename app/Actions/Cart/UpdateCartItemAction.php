<?php

namespace App\Actions\Cart;

use App\Services\CartService;

class UpdateCartItemAction
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function execute(int $productId, int $quantity): void
    {
        $this->cartService->updateQuantity($productId, $quantity);
    }
}
