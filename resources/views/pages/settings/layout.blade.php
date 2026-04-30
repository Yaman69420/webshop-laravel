<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Instellingen</h1>
        <p class="mt-1 text-zinc-400">Beheer je profiel en accountinstellingen.</p>
    </div>

    <div class="flex flex-col gap-8 lg:flex-row">

        {{-- Sidebar nav --}}
        <aside class="w-full lg:w-56 flex-shrink-0">
            <nav class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-2 flex flex-row lg:flex-col gap-1">
                <a href="{{ route('profile.edit') }}"
                   wire:navigate
                   class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition
                          {{ request()->routeIs('profile.edit') ? 'bg-purple-600 text-white' : 'text-zinc-400 hover:bg-zinc-800 hover:text-white' }}">
                    <flux:icon name="user" class="size-4 flex-shrink-0" />
                    Profiel
                </a>
                <a href="{{ route('security.edit') }}"
                   wire:navigate
                   class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition
                          {{ request()->routeIs('security.edit') ? 'bg-purple-600 text-white' : 'text-zinc-400 hover:bg-zinc-800 hover:text-white' }}">
                    <flux:icon name="shield-check" class="size-4 flex-shrink-0" />
                    Beveiliging
                </a>
                <a href="{{ route('appearance.edit') }}"
                   wire:navigate
                   class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition
                          {{ request()->routeIs('appearance.edit') ? 'bg-purple-600 text-white' : 'text-zinc-400 hover:bg-zinc-800 hover:text-white' }}">
                    <flux:icon name="swatch" class="size-4 flex-shrink-0" />
                    Weergave
                </a>
            </nav>
        </aside>

        {{-- Content --}}
        <div class="flex-grow rounded-xl border border-zinc-800 bg-zinc-900/50 p-6 md:p-8">
            <div class="mb-6 border-b border-zinc-800 pb-6">
                <h2 class="text-xl font-semibold text-white">{{ $heading ?? '' }}</h2>
                <p class="mt-1 text-sm text-zinc-400">{{ $subheading ?? '' }}</p>
            </div>
            <div class="max-w-lg">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
