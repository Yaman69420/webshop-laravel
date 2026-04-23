<?php

use App\Actions\Checkout\CreateOrderAction;
use App\Enums\OrderStatus;
use App\Events\OrderPlaced;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\Event;

it('creates an order from cart items', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price_in_cents' => 1500, 'stock' => 10]);
    $cartService = app(CartService::class);

    $cartService->add($product->id, 2);

    $order = app(CreateOrderAction::class)->execute($user, [
        'shipping_name' => 'Test User',
        'shipping_address' => 'Teststraat 1',
        'shipping_city' => 'Amsterdam',
        'shipping_postal_code' => '1000 AA',
    ]);

    expect($order->user_id)->toBe($user->id)
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->total_in_cents)->toBe(3000)
        ->and($order->items)->toHaveCount(1);
});

it('snapshots product prices in order items', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price_in_cents' => 2000, 'stock' => 5, 'name' => 'Test Product']);
    $cartService = app(CartService::class);

    $cartService->add($product->id, 1);

    $order = app(CreateOrderAction::class)->execute($user, [
        'shipping_name' => 'Test',
        'shipping_address' => 'Addr',
        'shipping_city' => 'City',
        'shipping_postal_code' => '1234',
    ]);

    $item = $order->items->first();
    expect($item->product_name)->toBe('Test Product')
        ->and($item->product_price_in_cents)->toBe(2000);
});

it('decrements stock after order creation', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price_in_cents' => 1000, 'stock' => 10]);
    $cartService = app(CartService::class);

    $cartService->add($product->id, 3);

    app(CreateOrderAction::class)->execute($user, [
        'shipping_name' => 'Test',
        'shipping_address' => 'Addr',
        'shipping_city' => 'City',
        'shipping_postal_code' => '1234',
    ]);

    expect($product->fresh()->stock)->toBe(7);
});

it('clears cart after order creation', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10]);
    $cartService = app(CartService::class);

    $cartService->add($product->id, 1);

    app(CreateOrderAction::class)->execute($user, [
        'shipping_name' => 'Test',
        'shipping_address' => 'Addr',
        'shipping_city' => 'City',
        'shipping_postal_code' => '1234',
    ]);

    expect($cartService->count())->toBe(0);
});

it('dispatches OrderPlaced event', function () {
    Event::fake([OrderPlaced::class]);

    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10]);
    $cartService = app(CartService::class);

    $cartService->add($product->id, 1);

    app(CreateOrderAction::class)->execute($user, [
        'shipping_name' => 'Test',
        'shipping_address' => 'Addr',
        'shipping_city' => 'City',
        'shipping_postal_code' => '1234',
    ]);

    Event::assertDispatched(OrderPlaced::class);
});

it('throws exception when cart is empty', function () {
    $user = User::factory()->create();

    app(CreateOrderAction::class)->execute($user, [
        'shipping_name' => 'Test',
        'shipping_address' => 'Addr',
        'shipping_city' => 'City',
        'shipping_postal_code' => '1234',
    ]);
})->throws(RuntimeException::class, 'Winkelmandje is leeg.');
