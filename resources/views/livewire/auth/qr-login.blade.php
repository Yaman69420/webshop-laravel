<?php

use function Livewire\Volt\{state, on};
use App\Actions\QrLogin\StartQrSessionAction;
use App\Services\QrLoginService;
use App\Enums\QrLoginStatus;
use Illuminate\Support\Facades\Auth;

state([
    'qrStatus' => 'idle',
    'token' => null,
    'countdown' => 120,
]);

on(['start-qr-session' => function (StartQrSessionAction $action) {
    $this->qrStatus = 'loading';
    $this->countdown = 120;
    
    $result = $action->execute(request());
    $this->token = $result['token'];
    $this->qrStatus = 'pending';

    $this->dispatch('qr-session-started', scanUrl: $result['scan_url']);
}]);

$pollStatus = function (QrLoginService $service) {
    if ($this->qrStatus !== 'pending' || !$this->token) {
        return;
    }
    
    $this->countdown -= 2; // wire:poll.2s decreases by 2s each time
    if ($this->countdown <= 0) {
        $this->qrStatus = 'expired';
        return;
    }

    $session = $service->findByToken($this->token);
    if (!$session) {
        $this->qrStatus = 'invalid';
        return;
    }

    $status = $service->resolveStatus($session);
    $this->qrStatus = $status->value;

    if ($status === QrLoginStatus::Approved) {
        $user = app(\App\Actions\QrLogin\ConsumeQrLoginAction::class)->execute($this->token);
        if ($user) {
            Auth::login($user, remember: true);
            if (request()->hasSession()) {
                request()->session()->regenerate();
            }
            $this->redirectRoute('home', navigate: true);
        } else {
            $this->qrStatus = 'invalid';
        }
    }
};

?>

<div class="flex flex-col items-center gap-4" wire:poll.2s="pollStatus">
    {{-- Status: Loading --}}
    @if($qrStatus === 'loading')
        <div class="flex flex-col items-center gap-2 py-4">
            <svg class="size-6 animate-spin text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm text-zinc-400">{{ __('QR-code laden...') }}</span>
        </div>
    @endif

    {{-- Status: Pending (show QR) --}}
    @if($qrStatus === 'pending')
        <div class="flex flex-col items-center gap-3">
            <div wire:ignore>
                <div id="qr-code-container" class="rounded-lg bg-white p-3"></div>
            </div>

            <div class="flex items-center gap-2 text-sm text-zinc-400">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ __('Verloopt over') }} <strong class="text-zinc-200">{{ $countdown }}</strong>s</span>
            </div>
            <p class="text-center text-xs text-zinc-500">
                {{ __('Scan deze QR-code met je telefoon waarop je al bent ingelogd.') }}
            </p>
        </div>
    @endif

    {{-- Status: Approved (consuming) --}}
    @if($qrStatus === 'approved')
        <div class="flex flex-col items-center gap-2 py-4">
            <svg class="size-8 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span class="text-sm font-medium text-green-400">{{ __('Login goedgekeurd! Je wordt doorgestuurd...') }}</span>
        </div>
    @endif

    {{-- Status: Expired --}}
    @if($qrStatus === 'expired')
        <div class="flex flex-col items-center gap-3 py-4 text-center">
            <svg class="size-8 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span class="text-sm text-amber-400">{{ __('De QR-code is vervallen en dient te worden vernieuwd.') }}</span>
            <button wire:click="$dispatch('start-qr-session')" class="text-sm text-zinc-400 underline hover:text-white">
                {{ __('QR-code vernieuwen') }}
            </button>
        </div>
    @endif

    {{-- Status: Invalid (Network error / Server error) --}}
    @if($qrStatus === 'invalid')
        <div class="flex flex-col items-center gap-3 py-4 text-center">
            <svg class="size-8 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span class="text-sm text-red-400">{{ __('Er is een fout opgetreden.') }}</span>
            <button wire:click="$dispatch('start-qr-session')" class="text-sm text-zinc-400 underline hover:text-white">
                {{ __('Probeer opnieuw') }}
            </button>
        </div>
    @endif

    {{-- Status: Denied --}}
    @if($qrStatus === 'denied')
        <div class="flex flex-col items-center gap-3 py-4">
            <svg class="size-8 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span class="text-sm text-red-400">{{ __('Login is geweigerd.') }}</span>
            <button wire:click="$dispatch('start-qr-session')" class="text-sm text-zinc-400 underline hover:text-white">
                {{ __('Opnieuw proberen') }}
            </button>
        </div>
    @endif
</div>
