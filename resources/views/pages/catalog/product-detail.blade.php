<?php

use App\Actions\Cart\AddToCartAction;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new
#[Layout('layouts.storefront')]
class extends Component
{
    public Product $product;

    public int $quantity = 1;

    public function mount(string $slug): void
    {
        $this->product = Product::where('slug', $slug)->active()->with('category')->firstOrFail();
    }

    #[Computed]
    public function relatedProducts()
    {
        return Product::query()
            ->active()
            ->with('category')
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->latest()
            ->take(4)
            ->get();
    }

    public function addToCart(): void
    {
        app(AddToCartAction::class)->execute($this->product->id, $this->quantity);
        session()->flash('success', 'Product toegevoegd aan winkelmandje!');
        $this->dispatch('cart-updated');
    }

    public function increment(): void
    {
        if ($this->quantity < $this->product->stock) {
            $this->quantity++;
        }
    }

    public function decrement(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addRelatedToCart(int $productId): void
    {
        app(AddToCartAction::class)->execute($productId, 1);
        $this->dispatch('cart-updated');
    }

    public function getTitle(): string
    {
        return $this->product->name.' — NOVA';
    }
}

?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    {{-- Breadcrumb --}}
    <nav class="mb-8 text-sm text-zinc-500 flex items-center gap-2">
        <a href="{{ route('products.index') }}" class="hover:text-white transition" wire:navigate>Shop</a>
        <flux:icon name="chevron-right" class="size-3" />
        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-white transition" wire:navigate>{{ $product->category->name }}</a>
        <flux:icon name="chevron-right" class="size-3" />
        <span class="text-zinc-300">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">

        {{-- Image --}}
        <div class="relative aspect-square overflow-hidden rounded-2xl bg-zinc-900 border border-zinc-800">
            @if($product->image_path)
                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
            @else
                <div class="flex h-full w-full items-center justify-center text-zinc-700">
                    <flux:icon name="photo" class="size-24" />
                </div>
            @endif
            {{-- Nieuw badge --}}
            <div class="absolute top-4 left-4">
                <span class="inline-flex rounded-full bg-purple-500/20 border border-purple-500/30 px-3 py-1 text-xs font-medium text-purple-300 backdrop-blur-sm">
                    Nieuw
                </span>
            </div>
        </div>

        {{-- Details --}}
        <div class="flex flex-col justify-center">
            <span class="text-sm text-purple-400 font-medium">{{ $product->category->name }}</span>
            <h1 class="mt-2 text-4xl font-bold text-white tracking-tight">{{ $product->name }}</h1>
            <p class="mt-4 text-2xl font-bold text-purple-400">{{ $product->formattedPrice() }}</p>

            {{-- Stock --}}
            <div class="mt-4 flex items-center gap-2">
                @if($product->stock > 0)
                    <flux:icon name="check-circle" class="size-4 text-green-400" />
                    <span class="text-sm font-medium text-green-400">Op voorraad ({{ $product->stock }} beschikbaar)</span>
                @else
                    <flux:icon name="x-circle" class="size-4 text-red-400" />
                    <span class="text-sm font-medium text-red-400">Uitverkocht</span>
                @endif
            </div>

            <div class="border-t border-zinc-800 my-6"></div>

            <p class="text-zinc-400 leading-relaxed">{{ $product->description }}</p>

            {{-- Add to Cart --}}
            @if($product->stock > 0)
                <div class="mt-8 flex flex-col sm:flex-row gap-4">
                    <div class="flex items-center border border-zinc-700 rounded-lg bg-zinc-900 h-12 w-32">
                        <button wire:click="decrement" class="px-3 text-zinc-400 hover:text-white transition h-full flex items-center justify-center">
                            <flux:icon name="minus" class="size-4" />
                        </button>
                        <span class="w-full text-center text-white font-medium">{{ $quantity }}</span>
                        <button wire:click="increment" class="px-3 text-zinc-400 hover:text-white transition h-full flex items-center justify-center">
                            <flux:icon name="plus" class="size-4" />
                        </button>
                    </div>
                    <button wire:click="addToCart" class="flex-1 h-12 flex items-center justify-center gap-2 rounded-lg bg-purple-600 text-sm font-medium text-white hover:bg-purple-500 transition shadow-lg">
                        <flux:icon name="shopping-cart" class="size-5" />
                        In Winkelmand
                    </button>
                </div>
            @endif

            @if(session('success'))
                <div class="mt-4 rounded-lg bg-green-500/10 border border-green-500/20 p-3 text-sm text-green-400 flex items-center gap-2">
                    <flux:icon name="check-circle" class="size-4" />
                    {{ session('success') }}
                </div>
            @endif

            {{-- Kenmerken --}}
            <div class="mt-8 pt-6 border-t border-zinc-800">
                <h3 class="text-sm font-semibold text-white mb-3">Kenmerken</h3>
                <ul class="space-y-2 text-sm text-zinc-400">
                    <li class="flex items-center gap-2">
                        <flux:icon name="check" class="size-4 text-purple-400 flex-shrink-0" />
                        Gratis verzending op alle bestellingen
                    </li>
                    <li class="flex items-center gap-2">
                        <flux:icon name="check" class="size-4 text-purple-400 flex-shrink-0" />
                        30 dagen bedenktijd
                    </li>
                    <li class="flex items-center gap-2">
                        <flux:icon name="check" class="size-4 text-purple-400 flex-shrink-0" />
                        2 jaar garantie
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Gerelateerde producten --}}
    @if($this->relatedProducts->isNotEmpty())
        <div class="mt-24 border-t border-zinc-800 pt-16">
            <h2 class="text-2xl font-bold text-white mb-8">Misschien vind je dit ook leuk</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($this->relatedProducts as $related)
                    <a href="{{ route('products.show', $related->slug) }}" wire:navigate
                       class="group rounded-xl border border-zinc-800 bg-zinc-900/50 flex flex-col overflow-hidden hover:border-purple-500/40 transition">
                        <div class="aspect-[4/3] bg-zinc-800 overflow-hidden">
                            @if($related->image_path)
                                <img src="{{ asset('storage/' . $related->image_path) }}" alt="{{ $related->name }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-zinc-700">
                                    <flux:icon name="photo" class="size-10" />
                                </div>
                            @endif
                        </div>
                        <div class="p-4 flex flex-col gap-1">
                            <p class="text-xs text-purple-400">{{ $related->category->name }}</p>
                            <h3 class="text-sm font-semibold text-white group-hover:text-purple-400 transition line-clamp-1">{{ $related->name }}</h3>
                            <p class="text-sm font-bold text-white mt-1">{{ $related->formattedPrice() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
