<x-layouts::auth :title="__('Wachtwoord vergeten')">
    <div class="flex flex-col gap-6">
        <div class="text-center">
            <h1 class="text-xl font-bold text-white">Wachtwoord vergeten?</h1>
            <p class="mt-1 text-sm text-zinc-400">Vul je e-mailadres in en we sturen je een resetlink.</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
            @csrf

            <flux:input
                name="email"
                label="E-mailadres"
                type="email"
                required
                autofocus
                placeholder="jouw@email.com"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                Resetlink versturen
            </flux:button>
        </form>

        <p class="text-center text-sm text-zinc-500">
            Toch nog het wachtwoord?
            <flux:link :href="route('login')" wire:navigate class="text-purple-400 hover:text-purple-300">Inloggen</flux:link>
        </p>
    </div>
</x-layouts::auth>
