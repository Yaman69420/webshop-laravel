<?php

use App\Actions\Cart\RemoveFromCartAction;
use App\Actions\Cart\UpdateCartItemAction;
use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
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

    #[Computed]
    public function vatInCents(): int
    {
        return (int) round($this->totalInCents * 0.21 / 1.21);
    }

    public function updateQuantity(int $productId, int $quantity): void
    {
        app(UpdateCartItemAction::class)->execute($productId, $quantity);
        unset($this->cartItems, $this->totalInCents, $this->vatInCents);
        $this->dispatch('cart-updated');
    }

    public string $removedProduct = '';

    public function removeItem(int $productId): void
    {
        $product = $this->cartItems->firstWhere('product.id', $productId);

        app(RemoveFromCartAction::class)->execute($productId);
        unset($this->cartItems, $this->totalInCents, $this->vatInCents);

        if ($product) {
            $this->removedProduct = $product['product']->name;
        }

        $this->dispatch('cart-updated');
    }

    public function formattedTotal(): string
    {
        return '€'.number_format($this->totalInCents / 100, 2, ',', '.');
    }

    public function formattedVat(): string
    {
        return '€'.number_format($this->vatInCents / 100, 2, ',', '.');
    }
}

?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-white mb-8">Winkelmand</h1>

    {{-- Remove notification toast --}}
    @if($removedProduct)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="fixed top-6 right-6 z-50 flex items-center gap-3 rounded-xl border border-red-500/20 bg-zinc-900 px-4 py-3 shadow-xl"
        >
            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-red-500/10">
                <flux:icon name="trash" class="size-4 text-red-400" />
            </div>
            <p class="text-sm text-white">
                <span class="font-medium">{{ $removedProduct }}</span>
                <span class="text-zinc-400"> verwijderd uit je winkelmandje.</span>
            </p>
            <button @click="show = false" class="ml-2 text-zinc-500 hover:text-white transition">
                <flux:icon name="x-mark" class="size-4" />
            </button>
        </div>
    @endif

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
        <div class="flex flex-col lg:flex-row gap-8">

            {{-- Cart Items --}}
            <div class="flex-grow">
                <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 overflow-hidden">

                    {{-- Table Headers --}}
                    <div class="hidden md:grid grid-cols-12 gap-4 px-6 py-4 border-b border-zinc-800 bg-zinc-900 text-xs font-medium text-zinc-500 uppercase tracking-wider">
                        <div class="col-span-6">Product</div>
                        <div class="col-span-2 text-center">Aantal</div>
                        <div class="col-span-2 text-right">Prijs</div>
                        <div class="col-span-2 text-right">Totaal</div>
                    </div>

                    {{-- Items --}}
                    @foreach($this->cartItems as $item)
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 px-6 py-5 border-b border-zinc-800 last:border-0 items-center hover:bg-white/[0.02] transition" wire:key="cart-{{ $item['product']->id }}">

                            {{-- Product Info --}}
                            <div class="col-span-1 md:col-span-6 flex gap-4">
                                <div class="w-20 h-20 md:w-24 md:h-24 rounded-lg bg-zinc-800 flex-shrink-0 overflow-hidden">
                                    @if($item['product']->image_path)
                                        <img src="{{ asset('storage/' . $item['product']->image_path) }}" alt="{{ $item['product']->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-zinc-600">
                                            <flux:icon name="photo" class="size-8" />
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-col justify-center">
                                    <a href="{{ route('products.show', $item['product']->slug) }}" class="font-semibold text-white hover:text-purple-400 transition" wire:navigate>
                                        {{ $item['product']->name }}
                                    </a>
                                    <p class="text-sm text-zinc-500 mt-1">{{ $item['product']->category->name ?? '' }}</p>
                                    <button wire:click="removeItem({{ $item['product']->id }})" class="mt-2 flex items-center gap-1 text-xs text-red-400 hover:text-red-300 transition w-fit">
                                        <flux:icon name="trash" class="size-3" />
                                        Verwijderen
                                    </button>
                                </div>
                            </div>

                            {{-- Quantity --}}
                            <div class="col-span-1 md:col-span-2 flex justify-start md:justify-center">
                                <div class="flex items-center border border-zinc-700 rounded-lg bg-zinc-900 h-10 w-28">
                                    <button wire:click="updateQuantity({{ $item['product']->id }}, {{ $item['quantity'] - 1 }})" class="px-3 text-zinc-400 hover:text-white transition h-full flex items-center">
                                        <flux:icon name="minus" class="size-3" />
                                    </button>
                                    <span class="w-full text-center text-sm font-medium text-white">{{ $item['quantity'] }}</span>
                                    <button wire:click="updateQuantity({{ $item['product']->id }}, {{ $item['quantity'] + 1 }})" class="px-3 text-zinc-400 hover:text-white transition h-full flex items-center">
                                        <flux:icon name="plus" class="size-3" />
                                    </button>
                                </div>
                            </div>

                            {{-- Unit Price --}}
                            <div class="hidden md:block md:col-span-2 text-right text-zinc-400 text-sm">
                                {{ $item['product']->formattedPrice() }}
                            </div>

                            {{-- Line Total --}}
                            <div class="col-span-1 md:col-span-2 flex justify-between md:justify-end items-center">
                                <span class="md:hidden text-xs text-zinc-500">Totaal:</span>
                                <span class="font-semibold text-white">
                                    €{{ number_format(($item['product']->price_in_cents * $item['quantity']) / 100, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    <a href="{{ route('products.index') }}" class="flex items-center gap-2 text-sm text-purple-400 hover:text-purple-300 transition w-fit" wire:navigate>
                        <flux:icon name="arrow-left" class="size-4" />
                        Verder winkelen
                    </a>
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="w-full lg:w-96 flex-shrink-0">
                <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6 sticky top-24">
                    <h2 class="text-xl font-semibold text-white mb-6">Besteloverzicht</h2>

                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between text-sm text-zinc-300">
                            <span>Subtotaal ({{ $this->cartItems->sum('quantity') }} items)</span>
                            <span>{{ $this->formattedTotal() }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-zinc-300">
                            <span>Verzending</span>
                            <span class="text-green-400">Gratis</span>
                        </div>
                        <div class="flex justify-between text-sm text-zinc-300">
                            <span>BTW (21%)</span>
                            <span>{{ $this->formattedVat() }}</span>
                        </div>
                    </div>

                    <div class="border-t border-zinc-800 pt-4 mb-8">
                        <div class="flex justify-between items-end">
                            <span class="text-base font-medium text-white">Totaal</span>
                            <div class="text-right">
                                <span class="text-3xl font-bold bg-gradient-to-r from-purple-400 to-violet-400 bg-clip-text text-transparent">{{ $this->formattedTotal() }}</span>
                                <p class="text-xs text-zinc-500 mt-1">Inclusief BTW</p>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="flex items-center justify-center gap-2 w-full rounded-lg bg-purple-600 px-4 py-3 text-sm font-medium text-white hover:bg-purple-500 transition" wire:navigate>
                        Afrekenen
                        <flux:icon name="arrow-right" class="size-4" />
                    </a>

                    {{-- Security Info --}}
                    <div class="mt-6 pt-6 border-t border-zinc-800 space-y-3">
                        <div class="flex items-center gap-3 text-sm text-zinc-400">
                            <flux:icon name="shield-check" class="size-5 text-green-400 flex-shrink-0" />
                            <span>Veilige betaling via Stripe</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-zinc-400">
                            <flux:icon name="arrow-uturn-left" class="size-5 flex-shrink-0" />
                            <span>30 dagen bedenktijd</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
