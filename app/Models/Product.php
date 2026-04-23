<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'description', 'price_in_cents', 'stock', 'category_id', 'image_path', 'is_active'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price_in_cents' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope: only active products.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the formatted price in euros.
     */
    public function formattedPrice(): string
    {
        return '€'.number_format($this->price_in_cents / 100, 2, ',', '.');
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }
}
