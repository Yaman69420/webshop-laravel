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

<x-pages::settings.layout heading="Weergave" subheading="Pas het kleurthema van de applicatie aan.">
    <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
        <flux:radio value="light" icon="sun">Licht</flux:radio>
        <flux:radio value="dark" icon="moon">Donker</flux:radio>
        <flux:radio value="system" icon="computer-desktop">Systeem</flux:radio>
    </flux:radio.group>
</x-pages::settings.layout>
