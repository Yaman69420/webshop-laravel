<?php

use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new
#[Layout('layouts.storefront')]
#[Title('Winkelmandje — NOVA')]
class extends Component
{
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

    public function updateQuantity(int $productId, int $quantity): void
    {
        app(\App\Actions\Cart\UpdateCartItemAction::class)->execute($productId, $quantity);
        unset($this->cartItems, $this->totalInCents);
    }

    public function removeItem(int $productId): void
    {
        app(\App\Actions\Cart\RemoveFromCartAction::class)->execute($productId);
        unset($this->cartItems, $this->totalInCents);
    }

    public function formattedTotal(): string
    {
        return '€' . number_format($this->totalInCents / 100, 2, ',', '.');
    }
}

?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-white mb-8">Winkelmandje</h1>

    @if($this->cartItems->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <flux:icon name="shopping-cart" class="size-16 text-zinc-700 mb-4" />
            <h2 class="text-xl font-semibold text-zinc-400">Je winkelmandje is leeg</h2>
            <p class="mt-2 text-zinc-500">Voeg producten toe om verder te gaan.</p>
            <a href="{{ route('products.index') }}" class="mt-6 rounded-lg bg-purple-600 px-6 py-3 text-sm font-medium text-white hover:bg-purple-500 transition" wire:navigate>
                Verder winkelen
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            {{-- Cart Items --}}
            <div class="lg:col-span-2 space-y-4">
                @foreach($this->cartItems as $item)
                    <div class="flex items-center gap-4 rounded-xl border border-zinc-800 bg-zinc-900/50 p-4" wire:key="cart-{{ $item['product']->id }}">
                        {{-- Image --}}
                        <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-zinc-800">
                            @if($item['product']->image_path)
                                <img src="{{ asset('storage/' . $item['product']->image_path) }}" alt="{{ $item['product']->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-zinc-600">
                                    <flux:icon name="photo" class="size-8" />
                                </div>
                            @endif
                        </div>

                        {{-- Details --}}
                        <div class="flex-grow">
                            <h3 class="font-medium text-white">{{ $item['product']->name }}</h3>
                            <p class="text-sm text-purple-400">{{ $item['product']->formattedPrice() }}</p>
                        </div>

                        {{-- Quantity --}}
                        <div class="flex items-center gap-2">
                            <flux:button wire:click="updateQuantity({{ $item['product']->id }}, {{ $item['quantity'] - 1 }})" size="sm" variant="ghost" class="text-zinc-400">
                                <flux:icon name="minus" class="size-3" />
                            </flux:button>
                            <span class="w-8 text-center text-white">{{ $item['quantity'] }}</span>
                            <flux:button wire:click="updateQuantity({{ $item['product']->id }}, {{ $item['quantity'] + 1 }})" size="sm" variant="ghost" class="text-zinc-400">
                                <flux:icon name="plus" class="size-3" />
                            </flux:button>
                        </div>

                        {{-- Line Total --}}
                        <span class="w-24 text-right font-semibold text-white">
                            €{{ number_format(($item['product']->price_in_cents * $item['quantity']) / 100, 2, ',', '.') }}
                        </span>

                        {{-- Remove --}}
                        <flux:button wire:click="removeItem({{ $item['product']->id }})" size="sm" variant="ghost" class="text-red-400 hover:text-red-300">
                            <flux:icon name="trash" class="size-4" />
                        </flux:button>
                    </div>
                @endforeach
            </div>

            {{-- Order Summary --}}
            <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6 h-fit sticky top-24">
                <h2 class="text-lg font-semibold text-white mb-4">Overzicht</h2>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-zinc-400">
                        <span>Subtotaal</span>
                        <span>{{ $this->formattedTotal() }}</span>
                    </div>
                    <div class="flex justify-between text-zinc-400">
                        <span>Verzending</span>
                        <span class="text-green-400">Gratis</span>
                    </div>
                    <div class="border-t border-zinc-800 pt-3 flex justify-between text-white font-semibold text-base">
                        <span>Totaal</span>
                        <span>{{ $this->formattedTotal() }}</span>
                    </div>
                </div>

                <a href="{{ route('checkout.index') }}" class="mt-6 block w-full rounded-lg bg-purple-600 px-4 py-3 text-center text-sm font-medium text-white hover:bg-purple-500 transition" wire:navigate>
                    Afrekenen
                </a>

                <a href="{{ route('products.index') }}" class="mt-3 block w-full text-center text-sm text-zinc-400 hover:text-white transition" wire:navigate>
                    Verder winkelen
                </a>
            </div>
        </div>
    @endif
</div>
