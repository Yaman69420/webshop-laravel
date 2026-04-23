<?php

namespace App\Actions\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;

class UpdateOrderStatusAction
{
    public function execute(Order $order, OrderStatus $status): Order
    {
        $order->update(['status' => $status]);

        return $order->refresh();
    }
}
