<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Layout('layouts.storefront')]
#[Title('Bestelling Bevestigd — NOVA')]
class extends Component
{
    public Order $order;

    public function mount(?int $order = null): void
    {
        if ($order) {
            $this->order = Order::where('id', $order)
                ->where('user_id', auth()->id())
                ->with('items')
                ->firstOrFail();
        }
    }
}

?>

{{-- Confetti pieces --}}
<style>
    @keyframes confetti-fall {
        0%   { opacity: 1; transform: translateY(-80px) rotate(0deg); }
        100% { opacity: 0; transform: translateY(100vh) rotate(720deg); }
    }
    .confetti-piece {
        position: fixed;
        width: 8px;
        height: 16px;
        border-radius: 2px;
        opacity: 0;
        animation: confetti-fall linear infinite;
        pointer-events: none;
    }
</style>

<div class="relative overflow-hidden">
    {{-- Confetti --}}
    <div aria-hidden="true">
        <div class="confetti-piece bg-purple-400"  style="left:5%;  animation-duration:3.2s; animation-delay:0s;"></div>
        <div class="confetti-piece bg-violet-400"  style="left:15%; animation-duration:2.8s; animation-delay:0.4s;"></div>
        <div class="confetti-piece bg-pink-400"    style="left:25%; animation-duration:3.6s; animation-delay:0.2s;"></div>
        <div class="confetti-piece bg-indigo-400"  style="left:35%; animation-duration:2.5s; animation-delay:0.8s;"></div>
        <div class="confetti-piece bg-purple-300"  style="left:50%; animation-duration:3.0s; animation-delay:0.1s;"></div>
        <div class="confetti-piece bg-green-400"   style="left:62%; animation-duration:3.4s; animation-delay:0.6s;"></div>
        <div class="confetti-piece bg-violet-300"  style="left:74%; animation-duration:2.7s; animation-delay:0.3s;"></div>
        <div class="confetti-piece bg-pink-300"    style="left:85%; animation-duration:3.1s; animation-delay:0.9s;"></div>
        <div class="confetti-piece bg-purple-500"  style="left:92%; animation-duration:2.9s; animation-delay:0.5s;"></div>
    </div>

    <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-8 md:p-12 text-center">

            {{-- Check icon --}}
            <div class="mx-auto w-20 h-20 rounded-full bg-green-500/10 flex items-center justify-center mb-6 shadow-[0_0_30px_rgba(52,211,153,0.3)]">
                <flux:icon name="check-circle" class="size-12 text-green-400" />
            </div>

            <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Bedankt voor je bestelling!</h1>
            <p class="text-zinc-400 text-lg mb-2">We hebben je bestelling succesvol ontvangen.</p>
            @auth
                <p class="text-zinc-500 text-sm mb-8">
                    Een bevestiging is verstuurd naar <strong class="text-white">{{ auth()->user()->email }}</strong>.
                </p>
            @endauth

            @isset($order)
                <div class="bg-zinc-800/50 border border-zinc-700 rounded-xl p-6 mb-8 text-left max-w-sm mx-auto">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-zinc-400">Ordernummer</span>
                            <span class="font-mono font-bold text-white">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-zinc-400">Totaalbedrag</span>
                            <span class="font-bold text-white">{{ $order->formattedTotal() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-zinc-400">Status</span>
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-yellow-500/10 text-yellow-400">
                                {{ $order->status->label() }}
                            </span>
                        </div>
                    </div>
                </div>
            @endisset

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                @isset($order)
                    <a href="{{ route('orders.show', $order) }}" class="rounded-lg border border-zinc-700 px-6 py-3 text-sm font-medium text-zinc-300 hover:border-purple-500 hover:text-white transition" wire:navigate>
                        Bekijk Bestelling
                    </a>
                @endisset
                <a href="{{ route('products.index') }}" class="rounded-lg bg-purple-600 px-6 py-3 text-sm font-medium text-white hover:bg-purple-500 transition" wire:navigate>
                    Verder Winkelen
                    <flux:icon name="arrow-right" class="size-4 inline ml-1" />
                </a>
            </div>
        </div>
    </div>
</div>
