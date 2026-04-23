<?php

use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
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

    public function addToCart(): void
    {
        app(\App\Actions\Cart\AddToCartAction::class)->execute($this->product->id, $this->quantity);

        session()->flash('success', 'Product toegevoegd aan winkelmandje!');
        $this->dispatch('cart-updated');
    }

    public function getTitle(): string
    {
        return $this->product->name . ' — NOVA';
    }
}

?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    {{-- Breadcrumb --}}
    <nav class="mb-6 text-sm text-zinc-500">
        <a href="{{ route('products.index') }}" class="hover:text-white transition" wire:navigate>Shop</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-white transition" wire:navigate>{{ $product->category->name }}</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-300">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">
        {{-- Image --}}
        <div class="aspect-square overflow-hidden rounded-2xl bg-zinc-900 border border-zinc-800">
            @if($product->image_path)
                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
            @else
                <div class="flex h-full w-full items-center justify-center text-zinc-700">
                    <flux:icon name="photo" class="size-24" />
                </div>
            @endif
        </div>

        {{-- Details --}}
        <div class="flex flex-col justify-center">
            <span class="text-sm text-purple-400 font-medium">{{ $product->category->name }}</span>
            <h1 class="mt-2 text-3xl font-bold text-white">{{ $product->name }}</h1>
            <p class="mt-4 text-4xl font-bold text-purple-400">{{ $product->formattedPrice() }}</p>

            <p class="mt-6 text-zinc-400 leading-relaxed">{{ $product->description }}</p>

            {{-- Stock --}}
            <div class="mt-6">
                @if($product->stock > 0)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-500/10 px-3 py-1 text-xs font-medium text-green-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-green-400"></span>
                        Op voorraad ({{ $product->stock }})
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-500/10 px-3 py-1 text-xs font-medium text-red-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                        Uitverkocht
                    </span>
                @endif
            </div>

            {{-- Add to Cart --}}
            @if($product->stock > 0)
                <div class="mt-8 flex items-center gap-4">
                    <flux:input wire:model="quantity" type="number" min="1" max="{{ $product->stock }}" class="w-20" />
                    <flux:button wire:click="addToCart" variant="primary" class="flex-1 bg-purple-600 hover:bg-purple-500">
                        <flux:icon name="shopping-cart" class="size-4 mr-2" />
                        Toevoegen aan winkelmandje
                    </flux:button>
                </div>
            @endif

            @if(session('success'))
                <div class="mt-4 rounded-lg bg-green-500/10 border border-green-500/20 p-3 text-sm text-green-400">
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </div>
</div>
