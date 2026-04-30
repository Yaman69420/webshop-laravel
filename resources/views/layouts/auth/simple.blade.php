<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">
            {{-- NOVA logo --}}
            <a href="{{ route('home') }}" class="mb-8 flex flex-col items-center gap-3" wire:navigate>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-600 shadow-lg shadow-purple-900/40">
                    <x-app-logo-icon class="size-7 fill-current text-white" />
                </div>
                <span class="text-2xl font-bold tracking-widest text-white">NOVA</span>
            </a>

            {{-- Card --}}
            <div class="w-full max-w-sm rounded-2xl border border-zinc-800 bg-zinc-900/50 p-8 shadow-xl backdrop-blur-sm">
                {{ $slot }}
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
