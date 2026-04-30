<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.storefront')]
#[Title('Weergave — NOVA')]
class extends Component {
    //
}; ?>

<x-pages::settings.layout heading="Weergave" subheading="Het kleurthema van NOVA.">
    <div class="flex items-center gap-4 rounded-xl border border-purple-500/20 bg-purple-500/5 px-4 py-3">
        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-purple-500/10">
            <flux:icon name="moon" class="size-5 text-purple-400" />
        </div>
        <div>
            <p class="text-sm font-medium text-white">Donkere modus</p>
            <p class="text-xs text-zinc-400">NOVA gebruikt altijd de donkere modus.</p>
        </div>
        <div class="ml-auto">
            <span class="inline-flex rounded-full bg-purple-600/20 px-2.5 py-0.5 text-xs font-medium text-purple-400">Actief</span>
        </div>
    </div>
</x-pages::settings.layout>
