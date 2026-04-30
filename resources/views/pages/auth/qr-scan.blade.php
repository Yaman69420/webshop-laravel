<x-layouts::auth :title="__('QR Login bevestigen')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Inloggen bevestigen')" :description="__('Wil je inloggen op het volgende apparaat?')" />

        {{-- Device Info --}}
        <div class="rounded-lg border border-zinc-700 bg-zinc-800/50 p-4 text-sm text-zinc-300">
            <div class="mb-3 flex items-center gap-2 text-zinc-400">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25Z" />
                </svg>
                <span class="font-medium text-zinc-200">{{ __('Apparaatgegevens') }}</span>
            </div>

            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt class="text-zinc-500">{{ __('IP-adres') }}</dt>
                    <dd class="font-mono text-zinc-300">{{ $ip_address ?? __('Onbekend') }}</dd>
                </div>
                <div>
                    <dt class="mb-1 text-zinc-500">{{ __('Browser / apparaat') }}</dt>
                    <dd class="break-all text-xs text-zinc-400">{{ $user_agent ?? __('Onbekend') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Confirm Form --}}
        <form method="POST" action="{{ route('qr-login.confirm') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <flux:button variant="primary" type="submit" class="w-full" data-test="qr-confirm-button">
                {{ __('Bevestig login') }}
            </flux:button>
        </form>

        {{-- Deny Form --}}
        <form method="POST" action="{{ route('qr-login.deny') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <flux:button variant="danger" type="submit" class="w-full" data-test="qr-deny-button">
                {{ __('Weiger login') }}
            </flux:button>
        </form>

        <p class="text-center text-xs text-zinc-500">
            {{ __('Als je dit verzoek niet herkent, weiger het dan en wijzig je wachtwoord.') }}
        </p>
    </div>
</x-layouts::auth>
