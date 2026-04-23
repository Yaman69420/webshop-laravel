<?php

use App\Actions\Admin\CreateCategoryAction;
use App\Actions\Admin\UpdateCategoryAction;
use App\Actions\Admin\DeleteCategoryAction;
use App\Models\Category;

it('creates a category with auto-generated slug', function () {
    $category = app(CreateCategoryAction::class)->execute([
        'name' => 'Audio Equipment',
    ]);

    expect($category->name)->toBe('Audio Equipment')
        ->and($category->slug)->toBe('audio-equipment');
});

it('updates a category name and slug', function () {
    $category = Category::factory()->create();

    $updated = app(UpdateCategoryAction::class)->execute($category, [
        'name' => 'Updated Category',
    ]);

    expect($updated->name)->toBe('Updated Category')
        ->and($updated->slug)->toBe('updated-category');
});

it('deletes a category', function () {
    $category = Category::factory()->create();
    $id = $category->id;

    app(DeleteCategoryAction::class)->execute($category);

    expect(Category::find($id))->toBeNull();
});
