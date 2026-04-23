<?php

namespace App\Actions\Cart;

use App\Services\CartService;

class RemoveFromCartAction
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function execute(int $productId): void
    {
        $this->cartService->remove($productId);
    }
}
