<?php

use App\Actions\Admin\UpdateOrderStatusAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.admin')]
#[Title('Bestellingen — Admin')]
class extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Computed]
    public function orders()
    {
        return Order::with('user')
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);
    }

    public function updateStatus(int $orderId, string $status): void
    {
        $order = Order::findOrFail($orderId);
        app(UpdateOrderStatusAction::class)->execute($order, OrderStatus::from($status));
        unset($this->orders);
    }
}

?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Bestellingen</h1>
        <p class="text-sm text-zinc-400 mt-1">Beheer en volg alle bestellingen.</p>
    </div>

    <div class="mb-6">
        <flux:select wire:model.live="status" class="w-48">
            <option value="">Alle statussen</option>
            @foreach(OrderStatus::cases() as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </flux:select>
    </div>

    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-zinc-400 border-b border-zinc-800">
                        <th class="px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Klant</th>
                        <th class="px-4 py-3 font-medium">Totaal</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Datum</th>
                        <th class="px-4 py-3 font-medium text-right">Wijzigen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @foreach($this->orders as $order)
                        <tr class="text-zinc-300 hover:bg-zinc-800/50">
                            <td class="px-4 py-3 font-mono text-zinc-500">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-white">{{ $order->user->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $order->user->email }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $order->formattedTotal() }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium bg-{{ $order->status->color() }}-500/10 text-{{ $order->status->color() }}-400">
                                    {{ $order->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-zinc-500">{{ $order->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <flux:select wire:change="updateStatus({{ $order->id }}, $event.target.value)" class="w-36">
                                    @foreach(OrderStatus::cases() as $s)
                                        <option value="{{ $s->value }}" {{ $order->status === $s ? 'selected' : '' }}>{{ $s->label() }}</option>
                                    @endforeach
                                </flux:select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-zinc-800">{{ $this->orders->links() }}</div>
    </div>
</div>
