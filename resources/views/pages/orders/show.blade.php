<?php

use App\Models\Order;
use App\Enums\OrderStatus;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Gate;
use Livewire\Volt\Component;

new
#[Layout('layouts.storefront')]
class extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        Gate::authorize('view', $order);

        $this->order = $order->load('items.product');
    }

    public function getTitle(): string
    {
        return 'Bestelling #' . str_pad($this->order->id, 5, '0', STR_PAD_LEFT) . ' — NOVA';
    }
}

?>

<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4">
        <div>
            <a href="{{ route('orders.index') }}" class="flex items-center gap-2 text-sm text-zinc-400 hover:text-white transition mb-4 w-fit" wire:navigate>
                <flux:icon name="arrow-left" class="size-4" />
                Terug naar overzicht
            </a>
            <h1 class="text-2xl font-bold text-white flex items-center gap-3 flex-wrap">
                Bestelling #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium
                    {{ match($order->status->color()) {
                        'yellow' => 'bg-yellow-500/10 text-yellow-400',
                        'indigo' => 'bg-indigo-500/10 text-indigo-400',
                        'green'  => 'bg-green-500/10 text-green-400',
                        'red'    => 'bg-red-500/10 text-red-400',
                        default  => 'bg-zinc-500/10 text-zinc-400',
                    } }}">
                    {{ $order->status->label() }}
                </span>
            </h1>
            <p class="text-zinc-400 mt-1 text-sm">Geplaatst op {{ $order->created_at->format('d F Y \o\m H:i') }}</p>
        </div>
    </div>

    {{-- Progress Timeline --}}
    <div class="rounded-xl border border-purple-500/20 bg-zinc-900/50 p-6 mb-8 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-transparent pointer-events-none"></div>

        <h2 class="font-semibold text-white mb-6">Zending Status</h2>

        @php
            $steps = [
                ['label' => 'Besteld',  'icon' => 'check',       'status' => OrderStatus::Pending],
                ['label' => 'Betaald',  'icon' => 'banknotes',   'status' => OrderStatus::Paid],
                ['label' => 'Verzonden','icon' => 'truck',        'status' => OrderStatus::Shipped],
                ['label' => 'Bezorgd', 'icon' => 'home',         'status' => null],
            ];

            $statusOrder = [
                OrderStatus::Pending->value  => 0,
                OrderStatus::Paid->value     => 1,
                OrderStatus::Shipped->value  => 2,
            ];

            $currentStep = $statusOrder[$order->status->value] ?? -1;
        @endphp

        {{-- Desktop timeline --}}
        <div class="hidden md:block relative">
            {{-- Background line --}}
            <div class="absolute left-0 top-5 w-full h-0.5 bg-zinc-800"></div>
            {{-- Progress line --}}
            <div class="absolute left-0 top-5 h-0.5 bg-purple-500 transition-all"
                 style="width: {{ match($order->status) {
                     OrderStatus::Pending  => '12%',
                     OrderStatus::Paid     => '45%',
                     OrderStatus::Shipped  => '79%',
                     default               => '0%',
                 } }}">
            </div>

            <div class="flex justify-between relative">
                @foreach($steps as $i => $step)
                    @php $done = $i <= $currentStep; $active = $i === $currentStep; @endphp
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center mb-3 z-10
                            {{ $done
                                ? 'bg-purple-600 text-white shadow-[0_0_15px_rgba(168,85,247,0.4)]'
                                : 'border-2 border-zinc-700 bg-zinc-900 text-zinc-600' }}">
                            <flux:icon name="{{ $step['icon'] }}" class="size-5 {{ $active ? 'animate-pulse' : '' }}" />
                        </div>
                        <span class="text-sm font-medium {{ $done ? 'text-white' : 'text-zinc-500' }}">{{ $step['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Mobile timeline (vertical) --}}
        <div class="md:hidden space-y-4">
            @foreach($steps as $i => $step)
                @php $done = $i <= $currentStep; $active = $i === $currentStep; @endphp
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                        {{ $done
                            ? 'bg-purple-600 text-white'
                            : 'border-2 border-zinc-700 bg-zinc-900 text-zinc-600' }}">
                        <flux:icon name="{{ $step['icon'] }}" class="size-5" />
                    </div>
                    <span class="text-sm font-medium {{ $done ? 'text-white' : 'text-zinc-500' }}">{{ $step['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Items --}}
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-800 bg-zinc-900">
                    <h2 class="font-semibold text-white">Artikelen ({{ $order->items->count() }})</h2>
                </div>
                <div class="divide-y divide-zinc-800">
                    @foreach($order->items as $item)
                        <div class="flex gap-4 p-4 hover:bg-white/[0.02] transition">
                            <div class="w-20 h-20 rounded-lg bg-zinc-800 flex-shrink-0 overflow-hidden">
                                @if($item->product?->image_path)
                                    <img src="{{ asset('storage/' . $item->product->image_path) }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-zinc-600">
                                        <flux:icon name="photo" class="size-6" />
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow flex flex-col justify-center">
                                <p class="font-semibold text-white">{{ $item->product_name }}</p>
                                <p class="text-sm text-zinc-400 mt-1">Aantal: {{ $item->quantity }}</p>
                            </div>
                            <div class="flex flex-col items-end justify-center">
                                <span class="font-medium text-white">{{ $item->formattedLineTotal() }}</span>
                                <span class="text-xs text-zinc-500 mt-1">{{ $item->quantity }}× {{ $item->formattedPrice() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">

            {{-- Cost Summary --}}
            <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
                <h2 class="font-semibold text-white mb-4">Overzicht</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-zinc-400">
                        <span>Datum</span>
                        <span>{{ $order->created_at->format('d M Y') }}</span>
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

            {{-- Shipping Address --}}
            <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
                <h2 class="font-semibold text-white mb-4">Verzendadres</h2>
                <div class="text-sm text-zinc-400 space-y-1">
                    <p class="text-white font-medium">{{ $order->shipping_name }}</p>
                    <p>{{ $order->shipping_address }}</p>
                    <p>{{ $order->shipping_postal_code }} {{ $order->shipping_city }}</p>
                </div>
            </div>

            {{-- Payment --}}
            <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
                <h2 class="font-semibold text-white mb-4">Betaling</h2>
                <div class="flex items-center gap-3 text-sm text-zinc-400">
                    <flux:icon name="credit-card" class="size-5 flex-shrink-0" />
                    <div>
                        <p class="text-white">Betaald via Stripe</p>
                        <p class="text-xs text-green-400 mt-0.5">{{ $order->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
