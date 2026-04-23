<?php

namespace App\Actions\Catalog;

use App\Models\Product;
use Illuminate\Support\Str;

class CreateProductAction
{
    /**
     * @param  array{name: string, description: ?string, price_in_cents: int, stock: int, category_id: int, image_path: ?string, is_active: bool}  $data
     */
    public function execute(array $data): Product
    {
        $data['slug'] = Str::slug($data['name']);

        return Product::create($data);
    }
}
