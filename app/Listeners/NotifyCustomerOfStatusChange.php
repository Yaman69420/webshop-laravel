<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use Illuminate\Support\Facades\Log;

class NotifyCustomerOfStatusChange
{
    public function handle(OrderStatusUpdated $event): void
    {
        Log::info('Order status change notification would be sent', [
            'order_id' => $event->order->id,
            'old_status' => $event->oldStatus->value,
            'new_status' => $event->newStatus->value,
            'user_email' => $event->order->user->email,
        ]);
    }
}
