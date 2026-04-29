<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        @livewireStyles
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 flex flex-col antialiased">
        <x-storefront.navbar />

        <main class="flex-grow flex flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-6 bg-zinc-900/50 p-6 md:p-8 rounded-xl border border-zinc-800/50 backdrop-blur-sm">
                {{ $slot }}
            </div>
        </main>

        <x-storefront.footer />

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
        @livewireScripts
    </body>
</html>