<a href="{{ route('cart.index') }}" class="relative text-zinc-400 hover:text-white transition" wire:navigate>
    <flux:icon name="shopping-cart" class="size-5" />
    @if($count > 0)
        <span class="absolute -top-2 -right-2 flex h-4 w-4 items-center justify-center rounded-full bg-purple-600 text-[10px] font-bold text-white">
            {{ $count }}
        </span>
    @endif
</a>
