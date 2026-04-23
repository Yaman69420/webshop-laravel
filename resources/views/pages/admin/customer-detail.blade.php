<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new
#[Layout('layouts.admin')]
class extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user->loadCount('orders');
    }

    #[Computed]
    public function orders()
    {
        return $this->user->orders()->latest()->take(10)->get();
    }

    public function getTitle(): string
    {
        return $this->user->name . ' — Admin';
    }
}

?>

<div>
    <a href="{{ route('admin.customers') }}" class="text-sm text-zinc-500 hover:text-white transition mb-4 inline-block" wire:navigate>← Terug naar klanten</a>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Info --}}
        <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-500/10 text-purple-400 text-lg font-bold">
                    {{ $user->initials() }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">{{ $user->name }}</h1>
                    <p class="text-sm text-zinc-400">{{ $user->email }}</p>
                </div>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between text-zinc-400">
                    <span>Lid sinds</span>
                    <span class="text-white">{{ $user->created_at->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between text-zinc-400">
                    <span>Bestellingen</span>
                    <span class="text-white">{{ $user->orders_count }}</span>
                </div>
            </div>
        </div>

        {{-- Orders --}}
        <div class="lg:col-span-2 rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Recente Bestellingen</h2>
            @if($this->orders->isEmpty())
                <p class="text-zinc-500 text-sm">Geen bestellingen.</p>
            @else
                <div class="space-y-3">
                    @foreach($this->orders as $order)
                        <div class="flex items-center justify-between py-2 border-b border-zinc-800 last:border-0">
                            <div>
                                <span class="font-mono text-xs text-zinc-500">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                                <span class="ml-2 text-white">{{ $order->formattedTotal() }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium bg-{{ $order->status->color() }}-500/10 text-{{ $order->status->color() }}-400">
                                    {{ $order->status->label() }}
                                </span>
                                <span class="text-xs text-zinc-500">{{ $order->created_at->format('d M Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
