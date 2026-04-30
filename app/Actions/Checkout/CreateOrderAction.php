<?php

namespace App\Actions\Checkout;

use App\Enums\OrderStatus;
use App\Events\OrderPlaced;
use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function __construct(
        private CartService $cartService,
    ) {}

    /**
     * @param  array{shipping_name: string, shipping_address: string, shipping_city: string, shipping_postal_code: string}  $shippingData
     */
    public function execute(User $user, array $shippingData, ?string $stripeSessionId = null): Order
    {
        $cartItems = $this->cartService->itemsWithProducts();

        if ($cartItems->isEmpty()) {
            throw new \RuntimeException('Winkelmandje is leeg.');
        }

        return DB::transaction(function () use ($user, $shippingData, $stripeSessionId, $cartItems): Order {
            $totalInCents = $cartItems->sum(fn (array $item): int => $item['product']->price_in_cents * $item['quantity']);

            $order = Order::create([
                'user_id' => $user->id,
                'status' => OrderStatus::Pending,
                'total_in_cents' => $totalInCents,
                'stripe_session_id' => $stripeSessionId,
                ...$shippingData,
            ]);

            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'product_price_in_cents' => $item['product']->price_in_cents,
                    'quantity' => $item['quantity'],
                    'subtotal_in_cents' => $item['product']->price_in_cents * $item['quantity'],
                ]);

                // Decrement stock
                $item['product']->decrement('stock', $item['quantity']);
            }

            $this->cartService->clear();

            OrderPlaced::dispatch($order);

            return $order;
        });
    }
}
