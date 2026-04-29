<?php

use App\Actions\Checkout\CreateOrderAction;
use App\Services\CartService;
use App\Services\StripeService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new
#[Layout('layouts.checkout')]
#[Title('Afrekenen — NOVA')]
class extends Component
{
    #[Validate('required|string|max:255')]
    public string $shipping_name = '';

    #[Validate('required|string|max:255')]
    public string $shipping_address = '';

    #[Validate('required|string|max:255')]
    public string $shipping_city = '';

    #[Validate('required|string|max:10')]
    public string $shipping_postal_code = '';

    public function mount(): void
    {
        $this->shipping_name = auth()->user()->name;
    }

    #[Computed]
    public function cartItems()
    {
        return app(CartService::class)->itemsWithProducts();
    }

    #[Computed]
    public function totalInCents(): int
    {
        return app(CartService::class)->totalInCents();
    }

    #[Computed]
    public function vatInCents(): int
    {
        return (int) round($this->totalInCents * 0.21 / 1.21);
    }

    public function placeOrder(): void
    {
        $this->validate();

        // Build Stripe line items from cart
        $lineItems = $this->cartItems->map(fn (array $item) => [
            'name' => $item['product']->name,
            'price_in_cents' => $item['product']->price_in_cents,
            'quantity' => $item['quantity'],
        ])->values()->all();

        // Create Stripe Checkout Session
        $successUrl = route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('checkout.index');

        $stripeSession = app(StripeService::class)->createCheckoutSession(
            $lineItems,
            $successUrl,
            $cancelUrl
        );

        // Create order in database with pending status + stripe session id
        app(CreateOrderAction::class)->execute(
            auth()->user(),
            [
                'shipping_name' => $this->shipping_name,
                'shipping_address' => $this->shipping_address,
                'shipping_city' => $this->shipping_city,
                'shipping_postal_code' => $this->shipping_postal_code,
            ],
            $stripeSession->id
        );

        // Redirect to Stripe hosted checkout (navigate: false = external URL)
        $this->redirect($stripeSession->url, navigate: false);
    }
}

?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    <div class="mb-8">
        <a href="{{ route('cart.index') }}" class="flex items-center gap-2 text-sm text-purple-400 hover:text-purple-300 transition mb-4 w-fit" wire:navigate>
            <flux:icon name="arrow-left" class="size-4" />
            Terug naar winkelmand
        </a>
        <h1 class="text-3xl font-bold text-white">Afrekenen</h1>
    </div>

    @if($this->cartItems->isEmpty())
        <div class="text-center py-16">
            <p class="text-zinc-400">Je winkelmandje is leeg.</p>
            <a href="{{ route('products.index') }}" class="mt-4 inline-block text-purple-400 hover:text-purple-300" wire:navigate>Ga naar de shop</a>
        </div>
    @else
        <form wire:submit="placeOrder">
            <div class="flex flex-col lg:flex-row gap-8">

                {{-- Left: Formulier --}}
                <div class="flex-grow space-y-6">

                    {{-- Stap 1: Verzendgegevens --}}
                    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6 md:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">1</div>
                            <h2 class="text-xl font-semibold text-white">Verzendgegevens</h2>
                        </div>

                        <div class="space-y-4">
                            <flux:input wire:model="shipping_name" label="Volledige naam" placeholder="Jan De Smet" required />
                            <flux:input wire:model="shipping_address" label="Straat en huisnummer" placeholder="Straatnaam 123" required />
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <flux:input wire:model="shipping_postal_code" label="Postcode" placeholder="1234 AB" required />
                                <flux:input wire:model="shipping_city" label="Woonplaats" placeholder="Amsterdam" required />
                            </div>
                        </div>
                    </div>

                    {{-- Stap 2: Betaling --}}
                    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6 md:p-8 relative overflow-hidden">
                        <div class="absolute top-4 right-4 opacity-10">
                            <flux:icon name="shield-check" class="size-16 text-purple-400" />
                        </div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">2</div>
                            <h2 class="text-xl font-semibold text-white">Betaling</h2>
                        </div>

                        <div class="rounded-lg border border-zinc-700 bg-zinc-900 p-5">
                            <div class="flex items-center gap-3 mb-2">
                                <flux:icon name="credit-card" class="size-5 text-purple-400" />
                                <span class="text-sm font-medium text-white">Veilig betalen via Stripe</span>
                            </div>
                            <p class="text-xs text-zinc-500">Na het plaatsen van je bestelling word je doorgestuurd naar de beveiligde betaalpagina van Stripe.</p>
                        </div>
                    </div>
                </div>

                {{-- Right: Overzicht --}}
                <div class="w-full lg:w-96 flex-shrink-0">
                    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6 sticky top-24">
                        <h2 class="text-xl font-semibold text-white mb-6">Overzicht</h2>

                        {{-- Product thumbnails --}}
                        <div class="space-y-4 mb-6 max-h-60 overflow-y-auto pr-1">
                            @foreach($this->cartItems as $item)
                                <div class="flex items-center gap-3">
                                    <div class="w-14 h-14 rounded-lg bg-zinc-800 flex-shrink-0 overflow-hidden relative">
                                        @if($item['product']->image_path)
                                            <img src="{{ asset('storage/' . $item['product']->image_path) }}" alt="{{ $item['product']->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-zinc-600">
                                                <flux:icon name="photo" class="size-5" />
                                            </div>
                                        @endif
                                        <span class="absolute -top-1 -right-1 bg-zinc-700 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full">{{ $item['quantity'] }}</span>
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <p class="text-sm font-medium text-white truncate">{{ $item['product']->name }}</p>
                                    </div>
                                    <span class="text-sm font-medium text-white flex-shrink-0">
                                        €{{ number_format(($item['product']->price_in_cents * $item['quantity']) / 100, 2, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Totalen --}}
                        <div class="border-t border-zinc-800 pt-4 space-y-3 text-sm mb-4">
                            <div class="flex justify-between text-zinc-300">
                                <span>Subtotaal</span>
                                <span>€{{ number_format($this->totalInCents / 100, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-zinc-300">
                                <span>Verzending</span>
                                <span class="text-green-400">Gratis</span>
                            </div>
                            <div class="flex justify-between text-zinc-300">
                                <span>BTW (21%)</span>
                                <span>€{{ number_format($this->vatInCents / 100, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="border-t border-zinc-800 pt-4 mb-6">
                            <div class="flex justify-between items-end">
                                <span class="font-medium text-white">Totaal</span>
                                <span class="text-2xl font-bold bg-gradient-to-r from-purple-400 to-violet-400 bg-clip-text text-transparent">
                                    €{{ number_format($this->totalInCents / 100, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <flux:button type="submit" variant="primary" class="w-full bg-purple-600 hover:bg-purple-500 flex items-center justify-center gap-2">
                            <flux:icon name="lock-closed" class="size-4" />
                            Betalen via Stripe
                        </flux:button>

                        <p class="text-xs text-center text-zinc-500 mt-4">
                            Door je bestelling te plaatsen ga je akkoord met onze algemene voorwaarden.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>
