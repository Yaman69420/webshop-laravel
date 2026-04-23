<footer class="border-t border-zinc-800 bg-zinc-950 py-12 mt-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
            {{-- Brand --}}
            <div>
                <span class="text-xl font-bold text-white tracking-wider">NOVA</span>
                <p class="mt-3 text-sm text-zinc-500">Premium tech & lifestyle producten.</p>
            </div>

            {{-- Shop --}}
            <div>
                <h4 class="font-semibold text-white mb-4">Shop</h4>
                <ul class="space-y-2 text-sm text-zinc-400">
                    <li><a href="{{ route('products.index') }}" class="hover:text-purple-400 transition" wire:navigate>Alle Producten</a></li>
                    <li><a href="{{ route('categories.index') }}" class="hover:text-purple-400 transition" wire:navigate>Categorieën</a></li>
                    <li><a href="{{ route('products.new') }}" class="hover:text-purple-400 transition" wire:navigate>Nieuw</a></li>
                </ul>
            </div>

            {{-- Account --}}
            <div>
                <h4 class="font-semibold text-white mb-4">Account</h4>
                <ul class="space-y-2 text-sm text-zinc-400">
                    @auth
                        <li><a href="{{ route('orders.index') }}" class="hover:text-purple-400 transition" wire:navigate>Mijn Bestellingen</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-purple-400 transition" wire:navigate>Inloggen</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-purple-400 transition" wire:navigate>Registreren</a></li>
                    @endauth
                </ul>
            </div>

            {{-- Info --}}
            <div>
                <h4 class="font-semibold text-white mb-4">Info</h4>
                <ul class="space-y-2 text-sm text-zinc-400">
                    <li><span>Gratis verzending vanaf €50</span></li>
                    <li><span>30 dagen retourrecht</span></li>
                </ul>
            </div>
        </div>

        <div class="mt-8 border-t border-zinc-800 pt-8 text-center text-sm text-zinc-600">
            &copy; {{ date('Y') }} NOVA. Alle rechten voorbehouden.
        </div>
    </div>
</footer>
