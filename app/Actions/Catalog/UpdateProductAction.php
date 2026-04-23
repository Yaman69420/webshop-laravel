<?php

namespace App\Actions\Catalog;

use App\Models\Product;
use Illuminate\Support\Str;

class UpdateProductAction
{
    /**
     * @param  array{name?: string, description?: ?string, price_in_cents?: int, stock?: int, category_id?: int, image_path?: ?string, is_active?: bool}  $data
     */
    public function execute(Product $product, array $data): Product
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $product->update($data);

        return $product->refresh();
    }
}
