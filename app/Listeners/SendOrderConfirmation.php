<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmation
{
    public function handle(OrderPlaced $event): void
    {
        // In production: Mail::to($event->order->user)->send(new OrderConfirmationMail($event->order));
        Log::info('Order confirmation email would be sent', [
            'order_id' => $event->order->id,
            'user_email' => $event->order->user->email,
        ]);
    }
}
