<?php

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new
#[Layout('layouts.storefront')]
#[Title('Categorieën — NOVA')]
class extends Component
{
    #[Computed]
    public function categories()
    {
        return Category::withCount('products')->orderBy('name')->get();
    }
}

?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Categorieën</h1>
        <p class="mt-1 text-zinc-400">Blader door onze productcategorieën</p>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($this->categories as $category)
            <a href="{{ route('products.index', ['category' => $category->slug]) }}" wire:navigate
               class="group rounded-2xl border border-zinc-800 bg-zinc-900/50 p-8 transition hover:border-purple-500/50 hover:shadow-lg hover:shadow-purple-500/10">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-white group-hover:text-purple-400 transition">{{ $category->name }}</h2>
                        <p class="mt-1 text-sm text-zinc-500">{{ $category->products_count }} producten</p>
                    </div>
                    <flux:icon name="arrow-right" class="size-5 text-zinc-600 group-hover:text-purple-400 transition" />
                </div>
            </a>
        @endforeach
    </div>

    @if($this->categories->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <flux:icon name="folder" class="size-16 text-zinc-700 mb-4" />
            <h2 class="text-xl font-semibold text-zinc-400">Geen categorieën gevonden</h2>
        </div>
    @endif
</div>
