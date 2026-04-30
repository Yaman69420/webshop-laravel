<x-layouts::auth :title="__('Wachtwoord bevestigen')">
    <div class="flex flex-col gap-6">
        <div class="text-center">
            <h1 class="text-xl font-bold text-white">Beveiligde pagina</h1>
            <p class="mt-1 text-sm text-zinc-400">Bevestig je wachtwoord om verder te gaan.</p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-5">
            @csrf

            <flux:input
                name="password"
                label="Wachtwoord"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Wachtwoord"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                Bevestigen
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
