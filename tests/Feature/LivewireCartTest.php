<?php

use App\Models\Product;
use App\Services\CartService;
use Livewire\Volt\Volt;

it('shows empty cart message when cart is empty', function () {
    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Je winkelmandje is leeg');
});

it('shows cart items when products are added', function () {
    $product = Product::factory()->create(['name' => 'Nova Headphones', 'stock' => 5]);

    app(CartService::class)->add($product->id, 2);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Nova Headphones');
});

it('can remove an item from the cart via livewire', function () {
    $product = Product::factory()->create(['stock' => 5]);
    app(CartService::class)->add($product->id, 1);

    Volt::test('cart.index')
        ->call('removeItem', $product->id)
        ->assertDispatched('cart-updated');

    expect(app(CartService::class)->count())->toBe(0);
});

it('can update quantity of a cart item via livewire', function () {
    $product = Product::factory()->create(['stock' => 10]);
    app(CartService::class)->add($product->id, 1);

    Volt::test('cart.index')
        ->call('updateQuantity', $product->id, 3)
        ->assertDispatched('cart-updated');

    expect(app(CartService::class)->count())->toBe(3);
});

it('shows correct total price in cart', function () {
    $product = Product::factory()->create(['price_in_cents' => 2999, 'stock' => 5]);
    app(CartService::class)->add($product->id, 2);

    Volt::test('cart.index')
        ->assertSee('59,98');
});

it('cart count component updates when cart changes', function () {
    $product = Product::factory()->create(['stock' => 5]);

    $component = \Livewire\Livewire::test(\App\Livewire\CartCount::class);
    expect($component->get('count'))->toBe(0);

    app(CartService::class)->add($product->id, 2);
    $component->dispatch('cart-updated');

    expect($component->get('count'))->toBe(2);
});
