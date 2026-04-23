<nav class="sticky top-0 z-50 border-b border-zinc-800 bg-zinc-950/80 backdrop-blur-xl">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="text-xl font-bold tracking-wider text-white" wire:navigate>
                NOVA
            </a>

            {{-- Navigation Links --}}
            <div class="hidden md:flex items-center gap-8 text-sm text-zinc-400">
                <a href="{{ route('products.index') }}" class="hover:text-white transition" wire:navigate>Shop</a>
                <a href="{{ route('categories.index') }}" class="hover:text-white transition" wire:navigate>Categorieën</a>
                <a href="{{ route('products.new') }}" class="hover:text-white transition" wire:navigate>Nieuw</a>
            </div>

            {{-- Right Side --}}
            <div class="flex items-center gap-4">
                {{-- Cart --}}
                <a href="{{ route('cart.index') }}" class="relative text-zinc-400 hover:text-white transition" wire:navigate>
                    <flux:icon name="shopping-cart" class="size-5" />
                    @php $cartCount = app(\App\Services\CartService::class)->count(); @endphp
                    @if($cartCount > 0)
                        <span class="absolute -top-2 -right-2 flex h-4 w-4 items-center justify-center rounded-full bg-purple-600 text-[10px] font-bold text-white">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>

                {{-- Auth --}}
                @auth
                    <div class="flex items-center gap-3">
                        <a href="{{ route('orders.index') }}" class="text-sm text-zinc-400 hover:text-white transition" wire:navigate>Mijn Bestellingen</a>
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="text-sm text-purple-400 hover:text-purple-300 transition" wire:navigate>Admin</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-zinc-400 hover:text-white transition">Uitloggen</button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-zinc-400 hover:text-white transition" wire:navigate>Inloggen</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-500 transition" wire:navigate>Registreren</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
