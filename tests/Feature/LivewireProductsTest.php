<?php

use App\Models\Category;
use App\Models\Product;
use Livewire\Volt\Volt;

it('shows the product listing page', function () {
    Product::factory()->count(3)->create();

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('Shop');
});

it('shows products on the listing page', function () {
    $product = Product::factory()->create(['name' => 'Test Headphones']);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('Test Headphones');
});

it('can search products by name', function () {
    Product::factory()->create(['name' => 'Nova Headphones']);
    Product::factory()->create(['name' => 'Mechanical Keyboard']);

    Volt::test('catalog.products')
        ->set('search', 'Nova')
        ->assertSee('Nova Headphones')
        ->assertDontSee('Mechanical Keyboard');
});

it('can filter products by category', function () {
    $audio = Category::factory()->create(['name' => 'Audio', 'slug' => 'audio']);
    $workspace = Category::factory()->create(['name' => 'Workspace', 'slug' => 'workspace']);

    Product::factory()->create(['name' => 'Headphones', 'category_id' => $audio->id]);
    Product::factory()->create(['name' => 'Keyboard', 'category_id' => $workspace->id]);

    Volt::test('catalog.products')
        ->set('category', 'audio')
        ->assertSee('Headphones')
        ->assertDontSee('Keyboard');
});

it('shows product detail page', function () {
    $product = Product::factory()->create([
        'name'        => 'Nova Pro Wireless',
        'slug'        => 'nova-pro-wireless',
        'description' => 'Premium koptelefoon',
    ]);

    $this->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertSee('Nova Pro Wireless')
        ->assertSee('Premium koptelefoon');
});

it('shows out of stock when stock is zero', function () {
    $product = Product::factory()->create(['stock' => 0]);

    $this->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertSee('Uitverkocht');
});

it('can add a product to cart from the listing via livewire', function () {
    $product = Product::factory()->create(['stock' => 5]);

    Volt::test('catalog.products')
        ->call('addToCart', $product->id)
        ->assertDispatched('cart-updated');
});
