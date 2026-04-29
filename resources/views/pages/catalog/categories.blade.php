<?php

use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
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

    public function categoryImage(string $slug): string
    {
        return match ($slug) {
            'audio' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=800&auto=format&fit=crop',
            'workspace' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?q=80&w=800&auto=format&fit=crop',
            'accessoires' => 'https://images.unsplash.com/photo-1600269452121-4f2416e55c28?q=80&w=800&auto=format&fit=crop',
            default => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?q=80&w=800&auto=format&fit=crop',
        };
    }
}

?>

<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-white mb-4">Onze Categorieën</h1>
        <p class="text-zinc-400 text-lg max-w-xl mx-auto">Ontdek ons uitgebreide assortiment van premium tech en lifestyle producten, onderverdeeld in handige categorieën.</p>
    </div>

    @if($this->categories->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <flux:icon name="folder" class="size-16 text-zinc-700 mb-4" />
            <h2 class="text-xl font-semibold text-zinc-400">Geen categorieën gevonden</h2>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($this->categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" wire:navigate
                   class="group block">
                    <div class="relative h-80 rounded-2xl overflow-hidden border border-zinc-800">
                        {{-- Background Image --}}
                        <img
                            src="{{ $this->categoryImage($category->slug) }}"
                            alt="{{ $category->name }}"
                            class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-80 group-hover:scale-110 transition-all duration-700"
                        >
                        {{-- Gradient Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/50 to-transparent"></div>

                        {{-- Content --}}
                        <div class="absolute bottom-0 left-0 right-0 p-8 transform group-hover:-translate-y-2 transition-transform duration-300">
                            <h2 class="text-3xl font-bold text-white mb-2">{{ $category->name }}</h2>
                            <p class="text-purple-400 font-medium flex items-center gap-2">
                                {{ $category->products_count }} {{ Str::plural('product', $category->products_count) }}
                                <flux:icon name="arrow-right" class="size-4 opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all duration-300" />
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
