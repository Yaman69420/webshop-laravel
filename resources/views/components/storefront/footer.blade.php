<footer class="border-t border-zinc-800 bg-zinc-900/50 mt-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
            {{-- Brand (2 cols) --}}
            <div class="md:col-span-2">
                <div class="flex items-center gap-2 mb-4">
                    <flux:icon name="globe-alt" class="size-6 text-purple-400" />
                    <span class="font-bold text-lg tracking-wider bg-gradient-to-r from-purple-400 to-violet-400 bg-clip-text text-transparent">NOVA</span>
                </div>
                <p class="text-sm text-zinc-400 max-w-xs">De premium bestemming voor moderne tech en lifestyle producten. Ontworpen voor de toekomst.</p>
            </div>

            {{-- Shop --}}
            <div>
                <h4 class="font-semibold text-white mb-4">Shop</h4>
                <ul class="space-y-2 text-sm text-zinc-400">
                    <li><a href="{{ route('products.index') }}" class="hover:text-purple-400 transition" wire:navigate>Alle Producten</a></li>
                    <li><a href="{{ route('products.new') }}" class="hover:text-purple-400 transition" wire:navigate>Nieuw</a></li>
                    <li><a href="{{ route('categories.index') }}" class="hover:text-purple-400 transition" wire:navigate>Categorieën</a></li>
                </ul>
            </div>

            {{-- Account --}}
            <div>
                <h4 class="font-semibold text-white mb-4">Account</h4>
                <ul class="space-y-2 text-sm text-zinc-400">
                    @auth
                        <li><a href="{{ route('orders.index') }}" class="hover:text-purple-400 transition" wire:navigate>Mijn Bestellingen</a></li>
                        @if(auth()->user()->is_admin)
                            <li><a href="{{ route('admin.dashboard') }}" class="hover:text-purple-400 transition" wire:navigate>Admin Panel</a></li>
                        @endif
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-purple-400 transition" wire:navigate>Inloggen</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-purple-400 transition" wire:navigate>Registreren</a></li>
                    @endauth
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="mt-12 border-t border-zinc-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-zinc-500">
            <p>&copy; {{ date('Y') }} NOVA Webshop. Alle rechten voorbehouden.</p>
            <div class="flex gap-4">
                <a href="#" class="hover:text-white transition">
                    <flux:icon name="share" class="size-5" />
                </a>
            </div>
        </div>
    </div>
</footer>
