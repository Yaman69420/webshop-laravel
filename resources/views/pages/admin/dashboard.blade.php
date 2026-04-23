<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
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
    <h1 class="text-2xl font-bold text-white mb-8">Dashboard</h1>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-8">
        <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
            <p class="text-sm text-zinc-400">Totale Omzet</p>
            <p class="mt-2 text-2xl font-bold text-white">€{{ number_format($this->totalRevenue / 100, 2, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
            <p class="text-sm text-zinc-400">Bestellingen</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ $this->totalOrders }}</p>
        </div>
        <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
            <p class="text-sm text-zinc-400">Producten</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ $this->totalProducts }}</p>
        </div>
        <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
            <p class="text-sm text-zinc-400">Klanten</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ $this->totalCustomers }}</p>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Recente Bestellingen</h2>

        @if($this->recentOrders->isEmpty())
            <p class="text-zinc-500 text-sm">Nog geen bestellingen.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-zinc-400 border-b border-zinc-800">
                            <th class="pb-3 font-medium">#</th>
                            <th class="pb-3 font-medium">Klant</th>
                            <th class="pb-3 font-medium">Totaal</th>
                            <th class="pb-3 font-medium">Status</th>
                            <th class="pb-3 font-medium">Datum</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        @foreach($this->recentOrders as $order)
                            <tr class="text-zinc-300">
                                <td class="py-3 font-mono text-zinc-500">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td class="py-3">{{ $order->user->name }}</td>
                                <td class="py-3">{{ $order->formattedTotal() }}</td>
                                <td class="py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
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
                                </td>
                                <td class="py-3 text-zinc-500">{{ $order->created_at->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
