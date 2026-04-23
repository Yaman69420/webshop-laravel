@props(['product'])

<div class="group flex flex-col overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 transition-all duration-300 hover:border-purple-500/50 hover:shadow-lg hover:shadow-purple-500/10">
    {{-- Image --}}
    <a href="{{ route('products.show', $product->slug) }}" wire:navigate class="block">
        <div class="aspect-[4/3] overflow-hidden bg-zinc-800 relative">
            @if($product->image_path)
                <img
                    src="{{ asset('storage/' . $product->image_path) }}"
                    alt="{{ $product->name }}"
                    class="h-full w-full object-cover opacity-80 transition-all duration-500 group-hover:scale-105 group-hover:opacity-100"
                >
            @else
                <div class="flex h-full w-full items-center justify-center text-zinc-600">
                    <flux:icon name="photo" class="size-12" />
                </div>
            @endif
        </div>
    </a>

    {{-- Details --}}
    <div class="flex flex-grow flex-col justify-between gap-4 p-5">
        <a href="{{ route('products.show', $product->slug) }}" wire:navigate class="block">
            <p class="text-xs font-medium text-purple-400 mb-1">{{ $product->category->name }}</p>
            <h3 class="font-semibold text-white transition-colors group-hover:text-purple-300 truncate">{{ $product->name }}</h3>
            <p class="mt-1 text-sm text-zinc-500 line-clamp-2">{{ $product->description }}</p>
        </a>

        <div class="flex items-center justify-between mt-auto">
            <span class="text-lg font-bold text-white">{{ $product->formattedPrice() }}</span>

            @if($product->stock > 0)
                <button
                    wire:click="addToCart({{ $product->id }})"
                    class="rounded-lg border border-zinc-700 bg-zinc-800 p-2 text-zinc-400 transition hover:border-purple-500 hover:bg-purple-600 hover:text-white"
                    aria-label="Toevoegen aan winkelmandje"
                >
                    <flux:icon name="shopping-cart" class="size-5" />
                </button>
            @else
                <span class="text-xs text-red-400">Uitverkocht</span>
            @endif
        </div>
    </div>
</div>
