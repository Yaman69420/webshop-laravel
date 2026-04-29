<?php

use App\Models\Product;
use App\Models\Category;
use App\Actions\Cart\AddToCartAction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new
#[Layout('layouts.storefront')]
#[Title('Shop — NOVA')]
class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $sort = 'newest';

    #[Url]
    public int $max_price = 1000;

    #[Computed]
    public function products()
    {
        return Product::query()
            ->active()
            ->with('category')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->category, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $this->category)))
            ->where('price_in_cents', '<=', $this->max_price * 100)
            ->when($this->sort === 'price_asc', fn ($q) => $q->orderBy('price_in_cents', 'asc'))
            ->when($this->sort === 'price_desc', fn ($q) => $q->orderBy('price_in_cents', 'desc'))
            ->when($this->sort === 'newest', fn ($q) => $q->latest())
            ->paginate(12);
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    public function addToCart(int $productId): void
    {
        app(AddToCartAction::class)->execute($productId, 1);
        $this->dispatch('cart-updated');
    }

    public function setCategory(string $slug): void
    {
        $this->category = ($this->category === $slug) ? '' : $slug;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->category = '';
        $this->search = '';
        $this->max_price = 1000;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMaxPrice(): void
    {
        $this->resetPage();
    }
}

?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Alle Producten</h1>
        <p class="mt-1 text-zinc-400">Ontdek onze premium collectie van tech en lifestyle items.</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sidebar --}}
        <aside class="w-full lg:w-64 flex-shrink-0">
            <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6 sticky top-24">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-semibold text-white text-lg">Filters</h2>
                    <button wire:click="clearFilters" class="text-xs text-purple-400 hover:text-purple-300 transition">Wis alles</button>
                </div>

                {{-- Categories --}}
                <div class="mb-6">
                    <h3 class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-3">Categorie</h3>
                    <div class="space-y-2">
                        @foreach($this->categories as $cat)
                            <label class="flex items-center gap-3 cursor-pointer group" wire:key="cat-{{ $cat->id }}">
                                <input
                                    type="checkbox"
                                    wire:click="setCategory('{{ $cat->slug }}')"
                                    {{ $category === $cat->slug ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-zinc-600 bg-zinc-800 text-purple-500 focus:ring-purple-500 focus:ring-offset-zinc-900"
                                >
                                <span class="text-sm text-zinc-300 group-hover:text-white transition">{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Price Range --}}
                <div>
                    <h3 class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-3">Prijs</h3>
                    <input
                        type="range"
                        wire:model.live="max_price"
                        min="0"
                        max="1000"
                        class="w-full accent-purple-500"
                    >
                    <div class="flex justify-between text-xs text-zinc-400 mt-2">
                        <span>€0</span>
                        <span>€{{ $max_price }}</span>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Product Grid Area --}}
        <div class="flex-grow">

            {{-- Toolbar --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Zoek producten..." icon="magnifying-glass" class="w-full sm:w-72" />

                <flux:select wire:model.live="sort" class="w-full sm:w-48">
                    <option value="newest">Nieuwste eerst</option>
                    <option value="price_asc">Prijs: laag → hoog</option>
                    <option value="price_desc">Prijs: hoog → laag</option>
                </flux:select>
            </div>

            {{-- Products --}}
            @if($this->products->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <flux:icon name="shopping-bag" class="size-16 text-zinc-700 mb-4" />
                    <h2 class="text-xl font-semibold text-zinc-400">Geen producten gevonden</h2>
                    <p class="mt-2 text-zinc-500">Probeer een andere zoekterm of filter.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($this->products as $product)
                        <x-storefront.product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $this->products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
