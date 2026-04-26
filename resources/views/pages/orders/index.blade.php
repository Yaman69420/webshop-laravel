<?php

use App\Models\Order;
use App\Enums\OrderStatus;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new
#[Layout('layouts.storefront')]
#[Title('Mijn Bestellingen — NOVA')]
class extends Component
{
    use WithPagination;

    #[Computed]
    public function orders()
    {
        return Order::forUser(auth()->id())
            ->with('items')
            ->latest()
            ->paginate(10);
    }

    public function progressWidth(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::Pending   => '25%',
            OrderStatus::Paid      => '50%',
            OrderStatus::Shipped   => '75%',
            OrderStatus::Cancelled => '0%',
            OrderStatus::Refunded  => '0%',
            default                => '0%',
        };
    }
}

?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row gap-8">

        {{-- Account Sidebar --}}
        <aside class="w-full md:w-64 flex-shrink-0">
            <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
                {{-- Avatar --}}
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-zinc-800">
                    <div class="w-12 h-12 rounded-full bg-purple-600 flex items-center justify-center text-lg font-bold text-white flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-semibold text-white truncate">{{ auth()->user()->name }}</h2>
                        <p class="text-xs text-zinc-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                {{-- Nav --}}
                <nav class="space-y-1">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-zinc-400 hover:bg-white/5 hover:text-white rounded-lg transition" wire:navigate>
                        <flux:icon name="user" class="size-5" />
                        Profiel
                    </a>
                    <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-purple-400 bg-purple-500/10 rounded-lg font-medium" wire:navigate>
                        <flux:icon name="archive-box" class="size-5" />
                        Bestellingen
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-red-400 hover:bg-red-500/10 rounded-lg transition">
                            <flux:icon name="arrow-right-start-on-rectangle" class="size-5" />
                            Uitloggen
                        </button>
                    </form>
                </nav>
            </div>
        </aside>

        {{-- Orders List --}}
        <div class="flex-grow">
            <h1 class="text-2xl font-bold text-white mb-6">Mijn Bestellingen</h1>

            @if($this->orders->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-center rounded-xl border border-zinc-800 bg-zinc-900/50">
                    <flux:icon name="clipboard-document-list" class="size-16 text-zinc-700 mb-4" />
                    <h2 class="text-xl font-semibold text-zinc-400">Nog geen bestellingen</h2>
                    <p class="mt-2 text-zinc-500">Start met winkelen om hier je bestellingen te zien.</p>
                    <a href="{{ route('products.index') }}" class="mt-6 rounded-lg bg-purple-600 px-6 py-3 text-sm font-medium text-white hover:bg-purple-500 transition" wire:navigate>
                        Naar de shop
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($this->orders as $order)
                        @php $isActive = in_array($order->status, [OrderStatus::Pending, OrderStatus::Paid, OrderStatus::Shipped]); @endphp
                        <div class="rounded-xl border {{ $isActive ? 'border-purple-500/40' : 'border-zinc-800' }} bg-zinc-900/50 overflow-hidden relative">
                            @if($isActive)
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-purple-500 rounded-l-xl"></div>
                            @endif

                            {{-- Header --}}
                            <div class="px-6 py-4 border-b border-zinc-800 bg-zinc-900/80 flex flex-wrap justify-between items-center gap-4">
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <span class="font-mono font-bold text-white">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                            {{ match($order->status->color()) {
                                                'yellow' => 'bg-yellow-500/10 text-yellow-400',
                                                'indigo' => 'bg-indigo-500/10 text-indigo-400',
                                                'green'  => 'bg-green-500/10 text-green-400',
                                                'red'    => 'bg-red-500/10 text-red-400',
                                                default  => 'bg-zinc-500/10 text-zinc-400',
                                            } }}">
                                            {{ $order->status->label() }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-zinc-400">
                                        {{ $order->created_at->format('d F Y') }} &bull;
                                        {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }} &bull;
                                        Totaal: <strong class="text-white">{{ $order->formattedTotal() }}</strong>
                                    </p>
                                </div>
                                <a href="{{ route('orders.show', $order) }}" class="rounded-lg border border-zinc-700 px-4 py-2 text-sm text-zinc-300 hover:border-purple-500 hover:text-white transition" wire:navigate>
                                    Details bekijken
                                </a>
                            </div>

                            {{-- Product Thumbnails --}}
                            <div class="px-6 py-4">
                                <div class="flex gap-3 mb-4">
                                    @foreach($order->items->take(4) as $item)
                                        <div class="w-14 h-14 rounded-lg bg-zinc-800 overflow-hidden flex-shrink-0">
                                            @if($item->product?->image_path)
                                                <img src="{{ asset('storage/' . $item->product->image_path) }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover {{ !$isActive ? 'grayscale opacity-60' : 'opacity-80' }}">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center text-zinc-600">
                                                    <flux:icon name="photo" class="size-5" />
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if($order->items->count() > 4)
                                        <div class="w-14 h-14 rounded-lg bg-zinc-800 flex items-center justify-center text-xs text-zinc-400">
                                            +{{ $order->items->count() - 4 }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Progress Bar (alleen voor actieve orders) --}}
                                @if($isActive)
                                    <div class="mt-2">
                                        <div class="flex justify-between text-xs font-medium text-zinc-500 mb-2">
                                            <span class="{{ $order->status === OrderStatus::Pending || $order->status === OrderStatus::Paid || $order->status === OrderStatus::Shipped ? 'text-purple-400' : '' }}">Verwerkt</span>
                                            <span class="{{ $order->status === OrderStatus::Paid || $order->status === OrderStatus::Shipped ? 'text-purple-400' : '' }}">Betaald</span>
                                            <span class="{{ $order->status === OrderStatus::Shipped ? 'text-purple-400' : '' }}">Verzonden</span>
                                            <span>Bezorgd</span>
                                        </div>
                                        <div class="h-1.5 w-full bg-zinc-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-purple-500 rounded-full transition-all" style="width: {{ $this->progressWidth($order->status) }}"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $this->orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
