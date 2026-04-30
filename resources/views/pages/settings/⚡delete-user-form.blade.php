<?php

use Livewire\Component;

new class extends Component {}; ?>

<section class="space-y-4">
    <div>
        <h3 class="text-base font-semibold text-red-400">Account verwijderen</h3>
        <p class="text-sm text-zinc-400 mt-1">Verwijder je account en alle bijbehorende gegevens permanent.</p>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" data-test="delete-user-button">
            Account verwijderen
        </flux:button>
    </flux:modal.trigger>

    <livewire:pages::settings.delete-user-modal />
</section>
