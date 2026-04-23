<?php

namespace App\Actions\Admin;

use App\Models\Category;
use Illuminate\Support\Str;

class CreateCategoryAction
{
    /**
     * @param  array{name: string}  $data
     */
    public function execute(array $data): Category
    {
        $data['slug'] = Str::slug($data['name']);

        return Category::create($data);
    }
}
