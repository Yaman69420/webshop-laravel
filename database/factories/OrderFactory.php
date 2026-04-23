<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => OrderStatus::Pending,
            'total_in_cents' => fake()->numberBetween(1000, 100000),
            'shipping_name' => fake()->name(),
            'shipping_address' => fake()->streetAddress(),
            'shipping_city' => fake()->city(),
            'shipping_postal_code' => fake()->postcode(),
            'stripe_session_id' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => OrderStatus::Processing]);
    }

    public function shipped(): static
    {
        return $this->state(fn () => ['status' => OrderStatus::Shipped]);
    }

    public function delivered(): static
    {
        return $this->state(fn () => ['status' => OrderStatus::Delivered]);
    }
}
