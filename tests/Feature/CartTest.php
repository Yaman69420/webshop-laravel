<?php

use App\Models\Product;
use App\Models\Category;
use App\Services\CartService;

it('can add a product to the cart', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $cartService = app(CartService::class);

    $cartService->add($product->id);

    expect($cartService->count())->toBe(1);
    expect($cartService->items())->toHaveCount(1);
});

it('increases quantity when adding the same product twice', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $cartService = app(CartService::class);

    $cartService->add($product->id, 1);
    $cartService->add($product->id, 2);

    expect($cartService->count())->toBe(3);
});

it('can update cart item quantity', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $cartService = app(CartService::class);

    $cartService->add($product->id, 2);
    $cartService->updateQuantity($product->id, 5);

    expect($cartService->count())->toBe(5);
});

it('removes item when quantity is set to zero', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $cartService = app(CartService::class);

    $cartService->add($product->id, 2);
    $cartService->updateQuantity($product->id, 0);

    expect($cartService->count())->toBe(0);
});

it('can remove a product from the cart', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $cartService = app(CartService::class);

    $cartService->add($product->id);
    $cartService->remove($product->id);

    expect($cartService->count())->toBe(0);
});

it('calculates total correctly', function () {
    $product1 = Product::factory()->create(['price_in_cents' => 1000, 'stock' => 10]);
    $product2 = Product::factory()->create(['price_in_cents' => 2500, 'stock' => 10]);
    $cartService = app(CartService::class);

    $cartService->add($product1->id, 2);
    $cartService->add($product2->id, 1);

    expect($cartService->totalInCents())->toBe(4500);
});

it('can clear the entire cart', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $cartService = app(CartService::class);

    $cartService->add($product->id, 3);
    $cartService->clear();

    expect($cartService->count())->toBe(0);
    expect($cartService->items())->toBeEmpty();
});
