<?php

use App\Models\Product;

it('scopeActive returns only active products', function () {
    Product::factory()->create(['is_active' => true]);
    Product::factory()->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);

    $activeProducts = Product::active()->get();

    expect($activeProducts)->toHaveCount(2)
        ->each(fn ($product) => $product->is_active->toBeTrue());
});

it('scopeActive excludes inactive products', function () {
    $inactive = Product::factory()->create(['is_active' => false]);

    $ids = Product::active()->pluck('id');

    expect($ids)->not->toContain($inactive->id);
});

it('formattedPrice returns correct euro format', function () {
    $product = Product::factory()->make(['price_in_cents' => 9999]);

    expect($product->formattedPrice())->toBe('€99,99');
});

it('formattedPrice handles whole euros correctly', function () {
    $product = Product::factory()->make(['price_in_cents' => 10000]);

    expect($product->formattedPrice())->toBe('€100,00');
});

it('generates slug automatically on creation', function () {
    $product = Product::factory()->create([
        'name' => 'Nova Pro Wireless',
        'slug' => '',
    ]);

    expect($product->slug)->toBe('nova-pro-wireless');
});
