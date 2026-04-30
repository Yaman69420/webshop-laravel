<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $password = '';

    public bool $isSocialUser;

    public function mount(): void
    {
        $this->isSocialUser = is_null(Auth::user()->password);
    }

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        if (! $this->isSocialUser) {
            $this->validate([
                'password' => $this->currentPasswordRules(),
            ]);
        }

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form method="POST" wire:submit="deleteUser" class="space-y-6">
        <div>
            <flux:heading size="lg">Weet je het zeker?</flux:heading>
            <flux:subheading>
                Zodra je account is verwijderd, worden alle gegevens permanent gewist.
                @if(! $isSocialUser) Voer je wachtwoord in om te bevestigen. @endif
            </flux:subheading>
        </div>

        @if(! $isSocialUser)
            <flux:input wire:model="password" label="Wachtwoord" type="password" viewable />
        @endif

        <div class="flex justify-end space-x-2 rtl:space-x-reverse">
            <flux:modal.close>
                <flux:button variant="filled">Annuleren</flux:button>
            </flux:modal.close>

            <flux:button variant="danger" type="submit" data-test="confirm-delete-user-button">
                Account verwijderen
            </flux:button>
        </div>
    </form>
</flux:modal>
