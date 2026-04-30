<?php

use App\Concerns\PasswordValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.storefront')]
#[Title('Beveiliging — NOVA')]
class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public bool $canManageTwoFactor;

    public bool $twoFactorEnabled;

    public bool $requiresConfirmation;

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();

        if ($this->canManageTwoFactor) {
            if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
                $disableTwoFactorAuthentication(auth()->user());
            }

            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
            $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        Flux::toast(variant: 'success', text: __('Password updated.'));
    }

    /**
     * Handle the two-factor authentication enabled event.
     */
    #[On('two-factor-enabled')]
    public function onTwoFactorEnabled(): void
    {
        $this->twoFactorEnabled = true;
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;
    }
}; ?>

<x-pages::settings.layout heading="Wachtwoord wijzigen" subheading="Gebruik een lang, willekeurig wachtwoord om je account veilig te houden.">
    <form method="POST" wire:submit="updatePassword" class="space-y-6">
        <flux:input wire:model="current_password" label="Huidig wachtwoord" type="password" required autocomplete="current-password" viewable />
        <flux:input wire:model="password" label="Nieuw wachtwoord" type="password" required autocomplete="new-password" viewable />
        <flux:input wire:model="password_confirmation" label="Bevestig wachtwoord" type="password" required autocomplete="new-password" viewable />

        <div>
            <flux:button variant="primary" type="submit" data-test="update-password-button">
                Opslaan
            </flux:button>
        </div>
    </form>

    @if ($canManageTwoFactor)
        <div class="mt-10 border-t border-zinc-800 pt-8">
            <h3 class="text-base font-semibold text-white mb-1">Twee-factor authenticatie</h3>
            <p class="text-sm text-zinc-400 mb-6">Beveilig je account met een extra verificatiestap bij het inloggen.</p>

            <div wire:cloak>
                @if ($twoFactorEnabled)
                    <div class="space-y-4">
                        <p class="text-sm text-zinc-400">
                            Je wordt bij het inloggen gevraagd om een code uit je authenticator-app.
                        </p>
                        <flux:button variant="danger" wire:click="disable">
                            2FA uitschakelen
                        </flux:button>
                        <livewire:pages::settings.two-factor.recovery-codes :$requiresConfirmation />
                    </div>
                @else
                    <div class="space-y-4">
                        <p class="text-sm text-zinc-400">
                            Wanneer je 2FA inschakelt, wordt bij elke login een code gevraagd vanuit je authenticator-app.
                        </p>
                        <flux:modal.trigger name="two-factor-setup-modal">
                            <flux:button variant="primary" wire:click="$dispatch('start-two-factor-setup')">
                                2FA inschakelen
                            </flux:button>
                        </flux:modal.trigger>
                        <livewire:pages::settings.two-factor-setup-modal :requires-confirmation="$requiresConfirmation" />
                    </div>
                @endif
            </div>
        </div>
    @endif
</x-pages::settings.layout>
