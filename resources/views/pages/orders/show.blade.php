<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

new
#[Layout('layouts.storefront')]
class extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $this->order = $order->load('items');
    }

    public function getTitle(): string
    {
        return 'Bestelling #' . str_pad($this->order->id, 5, '0', STR_PAD_LEFT) . ' — NOVA';
    }
}

?>

<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <a href="{{ route('orders.index') }}" class="text-sm text-zinc-500 hover:text-white transition mb-2 inline-block" wire:navigate>
                ← Terug naar bestellingen
            </a>
            <h1 class="text-3xl font-bold text-white">Bestelling #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</h1>
        </div>
        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium
            {{ match($order->status->color()) {
                'yellow' => 'bg-yellow-500/10 text-yellow-400',
                'blue' => 'bg-blue-500/10 text-blue-400',
                'indigo' => 'bg-indigo-500/10 text-indigo-400',
                'green' => 'bg-green-500/10 text-green-400',
                'red' => 'bg-red-500/10 text-red-400',
                default => 'bg-zinc-500/10 text-zinc-400',
            } }}">
            {{ $order->status->label() }}
        </span>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Items --}}
        <div class="lg:col-span-2 rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Producten</h2>
            <div class="space-y-4">
                @foreach($order->items as $item)
                    <div class="flex items-center justify-between py-3 border-b border-zinc-800 last:border-0">
                        <div>
                            <p class="text-white font-medium">{{ $item->product_name }}</p>
                            <p class="text-sm text-zinc-400">{{ $item->quantity }}× {{ $item->formattedPrice() }}</p>
                        </div>
                        <span class="text-white font-semibold">{{ $item->formattedLineTotal() }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Summary --}}
        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Overzicht</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-zinc-400">
                        <span>Datum</span>
                        <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between text-zinc-400">
                        <span>Verzending</span>
                        <span class="text-green-400">Gratis</span>
                    </div>
                    <div class="border-t border-zinc-800 pt-3 flex justify-between text-white font-semibold text-base">
                        <span>Totaal</span>
                        <span>{{ $order->formattedTotal() }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Verzendadres</h2>
                <div class="text-sm text-zinc-400 space-y-1">
                    <p class="text-white">{{ $order->shipping_name }}</p>
                    <p>{{ $order->shipping_address }}</p>
                    <p>{{ $order->shipping_postal_code }} {{ $order->shipping_city }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
