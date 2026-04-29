<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Layout('layouts.admin')]
#[Title('Dashboard — Admin')]
class extends Component
{
    #[Computed]
    public function totalRevenue(): int
    {
        return Order::sum('total_in_cents');
    }

    #[Computed]
    public function totalOrders(): int
    {
        return Order::count();
    }

    #[Computed]
    public function totalProducts(): int
    {
        return Product::count();
    }

    #[Computed]
    public function totalCustomers(): int
    {
        return User::where('is_admin', false)->count();
    }

    #[Computed]
    public function recentOrders()
    {
        return Order::with('user')->latest()->take(5)->get();
    }
}

?>

<div>
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Dashboard</h1>
        <p class="text-sm text-zinc-400 mt-1">Welkom terug! Hier is een overzicht van je webshop.</p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4 mb-8">

        {{-- Omzet --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/50 p-5">
            <div class="absolute right-0 top-0 p-4 opacity-10">
                <flux:icon name="banknotes" class="size-16 text-green-400" />
            </div>
            <p class="text-sm font-medium text-zinc-400 mb-1">Totale Omzet</p>
            <p class="text-2xl font-bold text-white">€{{ number_format($this->totalRevenue / 100, 2, ',', '.') }}</p>
        </div>

        {{-- Bestellingen --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/50 p-5">
            <div class="absolute right-0 top-0 p-4 opacity-10">
                <flux:icon name="shopping-cart" class="size-16 text-purple-400" />
            </div>
            <p class="text-sm font-medium text-zinc-400 mb-1">Bestellingen</p>
            <p class="text-2xl font-bold text-white">{{ $this->totalOrders }}</p>
        </div>

        {{-- Producten --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/50 p-5">
            <div class="absolute right-0 top-0 p-4 opacity-10">
                <flux:icon name="cube" class="size-16 text-blue-400" />
            </div>
            <p class="text-sm font-medium text-zinc-400 mb-1">Producten</p>
            <p class="text-2xl font-bold text-white">{{ $this->totalProducts }}</p>
        </div>

        {{-- Klanten --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/50 p-5">
            <div class="absolute right-0 top-0 p-4 opacity-10">
                <flux:icon name="users" class="size-16 text-amber-400" />
            </div>
            <p class="text-sm font-medium text-zinc-400 mb-1">Klanten</p>
            <p class="text-2xl font-bold text-white">{{ $this->totalCustomers }}</p>
        </div>

    </div>

    {{-- Recent Orders Panel --}}
    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-white">Recente Bestellingen</h2>
            <a href="{{ route('admin.orders') }}" wire:navigate class="text-sm text-purple-400 hover:text-purple-300 transition">
                Bekijk alles
            </a>
        </div>

        @if($this->recentOrders->isEmpty())
            <p class="text-sm text-zinc-500">Nog geen bestellingen.</p>
        @else
            <div class="space-y-4">
                @foreach($this->recentOrders as $order)
                    @php
                        $initials = collect(explode(' ', $order->user->name))
                            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                            ->take(2)
                            ->implode('');
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-zinc-700 bg-zinc-800 text-xs font-bold text-zinc-300">
                            {{ $initials }}
                        </div>
                        <div class="min-w-0 flex-grow">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-white">{{ $order->user->name }}</span>
                                <span class="text-sm font-semibold text-white">{{ $order->formattedTotal() }}</span>
                            </div>
                            <div class="mt-0.5 flex items-center justify-between">
                                <span class="text-xs text-zinc-500">{{ $order->created_at->diffForHumans() }}</span>
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-medium
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
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
