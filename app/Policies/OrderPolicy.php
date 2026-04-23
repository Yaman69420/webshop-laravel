<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine if the user can view any orders (their own list).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the given order.
     * Users can only view their own orders. Admins can view all.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->is_admin || $user->id === $order->user_id;
    }
}
