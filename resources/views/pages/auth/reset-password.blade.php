<x-layouts::auth :title="__('Wachtwoord instellen')">
    <div class="flex flex-col gap-6">
        <div class="text-center">
            <h1 class="text-xl font-bold text-white">Nieuw wachtwoord instellen</h1>
            <p class="mt-1 text-sm text-zinc-400">Voer hieronder je nieuwe wachtwoord in.</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-5">
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <flux:input
                name="email"
                value="{{ request('email') }}"
                label="E-mailadres"
                type="email"
                required
                autocomplete="email"
            />

            <flux:input
                name="password"
                label="Nieuw wachtwoord"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Nieuw wachtwoord"
                viewable
            />

            <flux:input
                name="password_confirmation"
                label="Bevestig wachtwoord"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Bevestig wachtwoord"
                viewable
            />

            <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                Wachtwoord instellen
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
