<?php

namespace App\Actions\Admin;

use App\Enums\OrderStatus;
use App\Events\OrderStatusUpdated;
use App\Models\Order;

class UpdateOrderStatusAction
{
    public function execute(Order $order, OrderStatus $status): Order
    {
        $oldStatus = $order->status;

        $order->update(['status' => $status]);

        if ($oldStatus !== $status) {
            OrderStatusUpdated::dispatch($order, $oldStatus, $status);
        }

        return $order->refresh();
    }
}
