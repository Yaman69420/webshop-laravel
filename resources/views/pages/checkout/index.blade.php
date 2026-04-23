<?php

use App\Services\CartService;
use App\Actions\Checkout\CreateOrderAction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new
#[Layout('layouts.storefront')]
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
        $user = auth()->user();
        $this->shipping_name = $user->name;
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

    public function placeOrder(): void
    {
        $this->validate();

        $order = app(CreateOrderAction::class)->execute(
            auth()->user(),
            [
                'shipping_name' => $this->shipping_name,
                'shipping_address' => $this->shipping_address,
                'shipping_city' => $this->shipping_city,
                'shipping_postal_code' => $this->shipping_postal_code,
            ]
        );

        $this->redirect(route('checkout.success', ['order' => $order->id]), navigate: true);
    }
}

?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-white mb-8">Afrekenen</h1>

    @if($this->cartItems->isEmpty())
        <div class="text-center py-16">
            <p class="text-zinc-400">Je winkelmandje is leeg.</p>
            <a href="{{ route('products.index') }}" class="mt-4 inline-block text-purple-400 hover:text-purple-300" wire:navigate>Ga naar de shop</a>
        </div>
    @else
        <form wire:submit="placeOrder">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                {{-- Shipping Form --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
                        <h2 class="text-lg font-semibold text-white mb-4">Verzendgegevens</h2>

                        <div class="space-y-4">
                            <flux:input wire:model="shipping_name" label="Volledige naam" required />
                            <flux:input wire:model="shipping_address" label="Adres" required />
                            <div class="grid grid-cols-2 gap-4">
                                <flux:input wire:model="shipping_city" label="Stad" required />
                                <flux:input wire:model="shipping_postal_code" label="Postcode" required />
                            </div>
                        </div>
                    </div>

                    {{-- Items Overview --}}
                    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
                        <h2 class="text-lg font-semibold text-white mb-4">Bestelling</h2>
                        <div class="space-y-3">
                            @foreach($this->cartItems as $item)
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-3">
                                        <span class="text-zinc-400">{{ $item['quantity'] }}×</span>
                                        <span class="text-white">{{ $item['product']->name }}</span>
                                    </div>
                                    <span class="text-zinc-300">€{{ number_format(($item['product']->price_in_cents * $item['quantity']) / 100, 2, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Summary --}}
                <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6 h-fit sticky top-24">
                    <h2 class="text-lg font-semibold text-white mb-4">Overzicht</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-zinc-400">
                            <span>Subtotaal</span>
                            <span>€{{ number_format($this->totalInCents / 100, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-zinc-400">
                            <span>Verzending</span>
                            <span class="text-green-400">Gratis</span>
                        </div>
                        <div class="border-t border-zinc-800 pt-3 flex justify-between text-white font-semibold text-base">
                            <span>Totaal</span>
                            <span>€{{ number_format($this->totalInCents / 100, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <flux:button type="submit" variant="primary" class="mt-6 w-full bg-purple-600 hover:bg-purple-500">
                        Bestelling plaatsen
                    </flux:button>
                </div>
            </div>
        </form>
    @endif
</div>
