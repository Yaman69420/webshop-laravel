<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id', 'status', 'total_in_cents',
    'shipping_name', 'shipping_address', 'shipping_city', 'shipping_postal_code',
    'stripe_session_id', 'stripe_payment_intent_id', 'order_number',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total_in_cents' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope: orders for a specific user.
     *
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the formatted total in euros.
     */
    public function formattedTotal(): string
    {
        return '€'.number_format($this->total_in_cents / 100, 2, ',', '.');
    }

    protected static function booted(): void
    {
        static::created(function (Order $order): void {
            if (empty($order->order_number)) {
                $order->updateQuietly([
                    'order_number' => 'NOVA-'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }
}
