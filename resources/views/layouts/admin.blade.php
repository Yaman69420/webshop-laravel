<!DOCTYPE html>
<html lang="nl" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin — NOVA' }}</title>
    @include('partials.head')
    @livewireStyles
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 flex">

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 border-r border-zinc-800 bg-zinc-950 p-6 hidden lg:block">
        <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold tracking-wider text-white" wire:navigate>
            NOVA <span class="text-purple-400 text-sm font-normal ml-1">Admin</span>
        </a>

        <nav class="mt-8 space-y-1">
            <a href="{{ route('admin.dashboard') }}" wire:navigate
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition {{ request()->routeIs('admin.dashboard') ? 'bg-purple-500/10 text-purple-400' : 'text-zinc-400 hover:text-white hover:bg-zinc-800' }}">
                <flux:icon name="chart-bar" class="size-4" />
                Dashboard
            </a>
            <a href="{{ route('admin.products') }}" wire:navigate
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition {{ request()->routeIs('admin.products') ? 'bg-purple-500/10 text-purple-400' : 'text-zinc-400 hover:text-white hover:bg-zinc-800' }}">
                <flux:icon name="cube" class="size-4" />
                Producten
            </a>
            <a href="{{ route('admin.categories') }}" wire:navigate
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition {{ request()->routeIs('admin.categories') ? 'bg-purple-500/10 text-purple-400' : 'text-zinc-400 hover:text-white hover:bg-zinc-800' }}">
                <flux:icon name="tag" class="size-4" />
                Categorieën
            </a>
            <a href="{{ route('admin.orders') }}" wire:navigate
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition {{ request()->routeIs('admin.orders') ? 'bg-purple-500/10 text-purple-400' : 'text-zinc-400 hover:text-white hover:bg-zinc-800' }}">
                <flux:icon name="clipboard-document-list" class="size-4" />
                Bestellingen
            </a>
            <a href="{{ route('admin.customers') }}" wire:navigate
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition {{ request()->routeIs('admin.customers*') ? 'bg-purple-500/10 text-purple-400' : 'text-zinc-400 hover:text-white hover:bg-zinc-800' }}">
                <flux:icon name="users" class="size-4" />
                Klanten
            </a>
        </nav>

        <div class="absolute bottom-6 left-6 right-6 space-y-2">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-sm text-zinc-500 hover:text-white transition" wire:navigate>
                <flux:icon name="arrow-left" class="size-4" />
                Naar de shop
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 text-sm text-zinc-500 hover:text-white transition">
                    <flux:icon name="arrow-right-start-on-rectangle" class="size-4" />
                    Uitloggen
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <main class="flex-grow lg:ml-64 p-6 lg:p-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
