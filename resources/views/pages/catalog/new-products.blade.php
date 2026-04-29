<?php

use App\Actions\Cart\AddToCartAction;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Layout('layouts.storefront')]
#[Title('Nieuw — NOVA')]
class extends Component
{
    #[Computed]
    public function featured(): ?Product
    {
        return Product::query()
            ->active()
            ->with('category')
            ->latest()
            ->first();
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->active()
            ->with('category')
            ->latest()
            ->skip(1)
            ->take(11)
            ->get();
    }

    public function addToCart(int $productId): void
    {
        app(AddToCartAction::class)->execute($productId, 1);
        $this->dispatch('cart-updated');
    }
}

?>

<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="text-center mb-12">
        <span class="inline-flex items-center rounded-full bg-purple-500/10 border border-purple-500/20 px-3 py-1 text-xs font-medium text-purple-400 mb-4 shadow-[0_0_15px_rgba(168,85,247,0.2)]">
            Nieuw in assortiment
        </span>
        <h1 class="text-4xl font-bold text-white mb-4">De Nieuwste Drops</h1>
        <p class="text-zinc-400 text-lg max-w-xl mx-auto">Ontdek als eerste onze allernieuwste producten en innovaties. Gelimiteerde voorraad.</p>
    </div>

    @if($this->featured)
        {{-- Featured product --}}
        <div class="mb-12 rounded-2xl border border-zinc-800 bg-zinc-900/50 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2">
                {{-- Text --}}
                <div class="p-8 md:p-12 flex flex-col justify-center order-2 md:order-1">
                    <span class="text-purple-400 font-semibold tracking-wider text-sm mb-2 uppercase">Uitgelicht</span>
                    <h2 class="text-3xl font-bold text-white mb-4">{{ $this->featured->name }}</h2>
                    <p class="text-zinc-400 mb-8 leading-relaxed">{{ $this->featured->description }}</p>
                    <div class="flex items-center gap-6">
                        <span class="text-2xl font-bold text-white">{{ $this->featured->formattedPrice() }}</span>
                        <a href="{{ route('products.show', $this->featured->slug) }}"
                           class="rounded-lg bg-purple-600 px-6 py-3 text-sm font-medium text-white hover:bg-purple-500 transition"
                           wire:navigate>
                            Bekijk Product
                        </a>
                    </div>
                </div>
                {{-- Image --}}
                <div class="relative h-64 md:h-auto bg-zinc-800 order-1 md:order-2">
                    @if($this->featured->image_path)
                        <img src="{{ asset('storage/' . $this->featured->image_path) }}"
                             alt="{{ $this->featured->name }}"
                             class="absolute inset-0 w-full h-full object-cover">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center text-zinc-700">
                            <flux:icon name="photo" class="size-24" />
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-r from-zinc-900 to-transparent hidden md:block"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 to-transparent md:hidden"></div>
                </div>
            </div>
        </div>
    @endif

    {{-- Grid --}}
    @if($this->products->isNotEmpty())
        <div class="flex justify-between items-end mb-6">
            <h2 class="text-2xl font-bold text-white">Recent Toegevoegd</h2>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($this->products as $product)
                <a href="{{ route('products.show', $product->slug) }}" wire:navigate
                   class="group rounded-2xl border border-zinc-800 bg-zinc-900/50 flex flex-col overflow-hidden hover:border-purple-500/40 transition">
                    {{-- Image --}}
                    <div class="aspect-[4/3] bg-zinc-800 relative overflow-hidden">
                        @if($product->image_path)
                            <img src="{{ asset('storage/' . $product->image_path) }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-zinc-700">
                                <flux:icon name="photo" class="size-12" />
                            </div>
                        @endif
                        <div class="absolute top-3 right-3">
                            <span class="inline-flex rounded-full bg-purple-500/20 border border-purple-500/30 px-2.5 py-0.5 text-xs font-medium text-purple-300">
                                Nieuw
                            </span>
                        </div>
                    </div>
                    {{-- Info --}}
                    <div class="p-5 flex-grow flex flex-col justify-between gap-4">
                        <div>
                            <p class="text-xs text-purple-400 font-medium mb-1">{{ $product->category->name }}</p>
                            <h3 class="text-base font-semibold text-white group-hover:text-purple-400 transition">{{ $product->name }}</h3>
                            <p class="text-sm text-zinc-400 mt-1 line-clamp-2">{{ $product->description }}</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-white">{{ $product->formattedPrice() }}</span>
                            <button wire:click.prevent="addToCart({{ $product->id }})"
                                    class="rounded-lg border border-zinc-700 p-2 text-zinc-400 hover:border-purple-500 hover:text-purple-400 transition"
                                    aria-label="Toevoegen aan winkelwagen">
                                <flux:icon name="shopping-cart" class="size-5" />
                            </button>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    @if(!$this->featured && $this->products->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <flux:icon name="sparkles" class="size-16 text-zinc-700 mb-4" />
            <h2 class="text-xl font-semibold text-zinc-400">Nog geen nieuwe producten</h2>
        </div>
    @endif
</div>
