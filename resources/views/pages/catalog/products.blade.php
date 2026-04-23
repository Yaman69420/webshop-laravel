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

    #[Computed]
    public function products()
    {
        return Product::query()
            ->active()
            ->with('category')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->category, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $this->category)))
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

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }
}

?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Shop</h1>
        <p class="mt-1 text-zinc-400">Ontdek onze premium collectie</p>
    </div>

    {{-- Filters --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap gap-3">
            {{-- Search --}}
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Zoeken..." icon="magnifying-glass" class="w-64" />

            {{-- Category Filter --}}
            <flux:select wire:model.live="category" class="w-48">
                <option value="">Alle Categorieën</option>
                @foreach($this->categories as $cat)
                    <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                @endforeach
            </flux:select>
        </div>

        {{-- Sort --}}
        <flux:select wire:model.live="sort" class="w-48">
            <option value="newest">Nieuwste eerst</option>
            <option value="price_asc">Prijs: laag → hoog</option>
            <option value="price_desc">Prijs: hoog → laag</option>
        </flux:select>
    </div>

    {{-- Product Grid --}}
    @if($this->products->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <flux:icon name="shopping-bag" class="size-16 text-zinc-700 mb-4" />
            <h2 class="text-xl font-semibold text-zinc-400">Geen producten gevonden</h2>
            <p class="mt-2 text-zinc-500">Probeer een andere zoekterm of filter.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($this->products as $product)
                <x-storefront.product-card :product="$product" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $this->products->links() }}
        </div>
    @endif
</div>
