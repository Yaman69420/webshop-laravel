<?php

use App\Actions\Cart\AddToCartAction;
use App\Models\Product;
use App\Services\CartService;

it('adds a product to the cart via CartService', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $cartService = mock(CartService::class);

    $cartService->shouldReceive('add')
        ->once()
        ->with($product->id, 2);

    $action = new AddToCartAction($cartService);
    $action->execute($product->id, 2);
});

it('throws exception when stock is insufficient', function () {
    $product = Product::factory()->create(['stock' => 1]);
    $cartService = mock(CartService::class);

    $cartService->shouldNotReceive('add');

    $action = new AddToCartAction($cartService);
    $action->execute($product->id, 5);
})->throws(RuntimeException::class, 'Onvoldoende voorraad.');

it('adds a single item by default', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $cartService = mock(CartService::class);

    $cartService->shouldReceive('add')
        ->once()
        ->with($product->id, 1);

    $action = new AddToCartAction($cartService);
    $action->execute($product->id);
});
