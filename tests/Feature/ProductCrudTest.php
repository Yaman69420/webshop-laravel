<?php

use App\Actions\Catalog\CreateProductAction;
use App\Actions\Catalog\UpdateProductAction;
use App\Actions\Catalog\DeleteProductAction;
use App\Models\Category;
use App\Models\Product;

it('creates a product with correct data', function () {
    $category = Category::factory()->create();

    $product = app(CreateProductAction::class)->execute([
        'name' => 'Test Product',
        'description' => 'A test product',
        'price_in_cents' => 1999,
        'stock' => 10,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    expect($product->name)->toBe('Test Product')
        ->and($product->slug)->toBe('test-product')
        ->and($product->price_in_cents)->toBe(1999)
        ->and($product->category_id)->toBe($category->id);
});

it('updates a product', function () {
    $product = Product::factory()->create();

    $updated = app(UpdateProductAction::class)->execute($product, [
        'name' => 'Updated Name',
        'price_in_cents' => 2999,
    ]);

    expect($updated->name)->toBe('Updated Name')
        ->and($updated->slug)->toBe('updated-name')
        ->and($updated->price_in_cents)->toBe(2999);
});

it('deletes a product', function () {
    $product = Product::factory()->create();
    $id = $product->id;

    app(DeleteProductAction::class)->execute($product);

    expect(Product::find($id))->toBeNull();
});
