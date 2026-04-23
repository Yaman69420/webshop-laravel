<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'product_id', 'product_name', 'product_price_in_cents', 'quantity'])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'product_price_in_cents' => 'integer',
            'quantity' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the line total in cents.
     */
    public function lineTotalInCents(): int
    {
        return $this->product_price_in_cents * $this->quantity;
    }

    /**
     * Get the formatted line total in euros.
     */
    public function formattedLineTotal(): string
    {
        return '€'.number_format($this->lineTotalInCents() / 100, 2, ',', '.');
    }

    /**
     * Get the formatted unit price in euros.
     */
    public function formattedPrice(): string
    {
        return '€'.number_format($this->product_price_in_cents / 100, 2, ',', '.');
    }
}
