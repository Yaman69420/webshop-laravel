<?php

use App\Models\Order;
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
            ->latest()
            ->paginate(10);
    }
}

?>

<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-white mb-8">Mijn Bestellingen</h1>

    @if($this->orders->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
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
                <a href="{{ route('orders.show', $order) }}" wire:navigate
                   class="block rounded-xl border border-zinc-800 bg-zinc-900/50 p-6 transition hover:border-purple-500/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-mono text-sm text-zinc-500">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                            <p class="mt-1 text-white font-medium">{{ $order->formattedTotal() }}</p>
                        </div>
                        <div class="text-right">
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
                            <p class="mt-1 text-xs text-zinc-500">{{ $order->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $this->orders->links() }}
        </div>
    @endif
</div>
