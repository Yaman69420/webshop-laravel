<!DOCTYPE html>
<html lang="nl" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'NOVA Webshop' }}</title>
    @include('partials.head')
    @fluxAppearance
    @livewireStyles
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 flex flex-col">

    <x-storefront.navbar />

    <main class="flex-grow">
        {{ $slot }}
    </main>

    <x-storefront.footer />

    @fluxScripts
    @livewireScripts
</body>
</html>
