<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.storefront')]
#[Title('Profiel — NOVA')]
class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Flux::toast(text: __('A new verification link has been sent to your email address.'));
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<x-pages::settings.layout heading="Profiel" subheading="Pas je naam en e-mailadres aan.">
    <form wire:submit="updateProfileInformation" class="space-y-6">
        <flux:input wire:model="name" label="Naam" type="text" required autofocus autocomplete="name" />

        <div>
            <flux:input wire:model="email" label="E-mailadres" type="email" required autocomplete="email" />

            @if ($this->hasUnverifiedEmail)
                <p class="mt-3 text-sm text-zinc-400">
                    Je e-mailadres is nog niet geverifieerd.
                    <button wire:click.prevent="resendVerificationNotification" class="text-purple-400 hover:text-purple-300 underline">
                        Stuur verificatiemail opnieuw.
                    </button>
                </p>
            @endif
        </div>

        <div>
            <flux:button variant="primary" type="submit" data-test="update-profile-button">
                Opslaan
            </flux:button>
        </div>
    </form>

    @if ($this->showDeleteUser)
        <div class="mt-10 border-t border-zinc-800 pt-8">
            <livewire:pages::settings.delete-user-form />
        </div>
    @endif
</x-pages::settings.layout>
