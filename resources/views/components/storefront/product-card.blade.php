@props(['product'])

<div class="group rounded-2xl border border-zinc-800 bg-zinc-900/50 p-4 transition hover:border-purple-500/50 hover:shadow-lg hover:shadow-purple-500/10">
    <a href="{{ route('products.show', $product->slug) }}" wire:navigate>
        {{-- Image --}}
        <div class="aspect-square overflow-hidden rounded-xl bg-zinc-800 mb-4">
            @if($product->image_path)
                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition group-hover:scale-105">
            @else
                <div class="flex h-full w-full items-center justify-center text-zinc-600">
                    <flux:icon name="photo" class="size-12" />
                </div>
            @endif
        </div>

        {{-- Details --}}
        <div>
            <p class="text-xs text-zinc-500 mb-1">{{ $product->category->name }}</p>
            <h3 class="font-medium text-white truncate">{{ $product->name }}</h3>
            <p class="mt-1 text-lg font-bold text-purple-400">{{ $product->formattedPrice() }}</p>
        </div>
    </a>
</div>
