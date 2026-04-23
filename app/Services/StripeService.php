<?php

namespace App\Services;

use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Checkout Session.
     *
     * @param  array<int, array{name: string, price_in_cents: int, quantity: int}>  $lineItems
     */
    public function createCheckoutSession(array $lineItems, string $successUrl, string $cancelUrl): StripeSession
    {
        $stripeLineItems = array_map(fn (array $item) => [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $item['name'],
                ],
                'unit_amount' => $item['price_in_cents'],
            ],
            'quantity' => $item['quantity'],
        ], $lineItems);

        return StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $stripeLineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }

    /**
     * Verify a Stripe webhook signature and return the event.
     */
    public function verifyWebhook(string $payload, string $sigHeader): \Stripe\Event
    {
        return Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('services.stripe.webhook_secret')
        );
    }

    /**
     * Retrieve a Checkout Session by ID.
     */
    public function retrieveSession(string $sessionId): StripeSession
    {
        return StripeSession::retrieve($sessionId);
    }
}
