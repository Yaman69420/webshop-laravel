<!DOCTYPE html>
<html lang="nl" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Afrekenen — NOVA' }}</title>
    @include('partials.head')
    @fluxAppearance
    @livewireStyles
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 flex flex-col">

    {{-- Minimale checkout header --}}
    <header class="sticky top-0 z-50 border-b border-zinc-800 bg-zinc-950/80 backdrop-blur-xl">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2" wire:navigate>
                    <flux:icon name="globe-alt" class="size-7 text-purple-400" />
                    <span class="text-xl font-bold tracking-wider bg-gradient-to-r from-purple-400 to-violet-400 bg-clip-text text-transparent">NOVA</span>
                </a>
                <div class="flex items-center gap-2 text-sm text-zinc-400">
                    <flux:icon name="lock-closed" class="size-4 text-purple-400" />
                    <span>Beveiligde Checkout</span>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow">
        {{ $slot }}
    </main>

    {{-- Minimale footer --}}
    <footer class="border-t border-zinc-800 mt-auto">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-zinc-500">
            <p>&copy; {{ date('Y') }} NOVA Webshop. Alle rechten voorbehouden.</p>
            <div class="flex items-center gap-2">
                <flux:icon name="shield-check" class="size-4 text-purple-400" />
                <span>Veilige afrekenomgeving</span>
            </div>
        </div>
    </footer>

    @fluxScripts
    @livewireScripts
</body>
</html>
