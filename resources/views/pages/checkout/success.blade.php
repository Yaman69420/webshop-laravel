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

<div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8 text-center">
    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-8">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-500/10 mb-6">
            <flux:icon name="check" class="size-8 text-green-400" />
        </div>

        <h1 class="text-2xl font-bold text-white">Bedankt voor je bestelling!</h1>
        <p class="mt-2 text-zinc-400">Je bestelling is succesvol geplaatst.</p>

        @isset($order)
            <div class="mt-6 rounded-xl bg-zinc-800/50 p-4 text-left">
                <div class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <span class="text-zinc-400">Bestelnummer</span>
                        <span class="text-white font-mono">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-400">Totaal</span>
                        <span class="text-white">{{ $order->formattedTotal() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-400">Status</span>
                        <span class="text-yellow-400">{{ $order->status->label() }}</span>
                    </div>
                </div>
            </div>
        @endisset

        <div class="mt-8 flex flex-col gap-3">
            <a href="{{ route('orders.index') }}" class="rounded-lg bg-purple-600 px-6 py-3 text-sm font-medium text-white hover:bg-purple-500 transition" wire:navigate>
                Mijn Bestellingen
            </a>
            <a href="{{ route('products.index') }}" class="text-sm text-zinc-400 hover:text-white transition" wire:navigate>
                Verder winkelen
            </a>
        </div>
    </div>
</div>
