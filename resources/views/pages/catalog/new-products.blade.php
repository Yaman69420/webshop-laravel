<?php

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new
#[Layout('layouts.storefront')]
#[Title('Nieuw — NOVA')]
class extends Component
{
    #[Computed]
    public function products()
    {
        return Product::query()
            ->active()
            ->with('category')
            ->latest()
            ->take(12)
            ->get();
    }
}

?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Nieuwe Producten</h1>
        <p class="mt-1 text-zinc-400">De nieuwste toevoegingen aan onze collectie</p>
    </div>

    @if($this->products->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <flux:icon name="sparkles" class="size-16 text-zinc-700 mb-4" />
            <h2 class="text-xl font-semibold text-zinc-400">Nog geen nieuwe producten</h2>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($this->products as $product)
                <x-storefront.product-card :product="$product" />
            @endforeach
        </div>
    @endif
</div>
