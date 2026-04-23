<?php

namespace App\Actions\Admin;

use App\Models\Category;
use Illuminate\Support\Str;

class UpdateCategoryAction
{
    /**
     * @param  array{name?: string}  $data
     */
    public function execute(Category $category, array $data): Category
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return $category->refresh();
    }
}
