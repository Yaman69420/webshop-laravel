<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.admin')]
#[Title('Klanten — Admin')]
class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Computed]
    public function customers()
    {
        return User::where('is_admin', false)
            ->withCount('orders')
            ->withSum('orders', 'total_in_cents')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            }))
            ->latest()
            ->paginate(15);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
}

?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Klanten</h1>
        <p class="text-sm text-zinc-400 mt-1">Beheer je klanten en hun bestelgeschiedenis.</p>
    </div>

    {{-- Search --}}
    <div class="mb-6 flex items-center gap-3">
        <div class="relative w-full max-w-sm">
            <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-zinc-400" />
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Zoek op naam of e-mail..."
                class="w-full rounded-lg border border-zinc-700 bg-zinc-900 pl-9 pr-4 py-2 text-sm text-white placeholder-zinc-500 focus:border-purple-500 focus:outline-none"
            />
        </div>
    </div>

    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-zinc-400 border-b border-zinc-800">
                    <th class="px-4 py-3 font-medium">Klant</th>
                    <th class="px-4 py-3 font-medium">E-mailadres</th>
                    <th class="px-4 py-3 font-medium">Bestellingen</th>
                    <th class="px-4 py-3 font-medium">Totaal uitgegeven</th>
                    <th class="px-4 py-3 font-medium">Lid sinds</th>
                    <th class="px-4 py-3 font-medium text-right">Bekijken</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @foreach($this->customers as $customer)
                    @php
                        $initials = collect(explode(' ', $customer->name))
                            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                            ->take(2)
                            ->implode('');
                    @endphp
                    <tr class="text-zinc-300 hover:bg-zinc-800/50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border border-zinc-700 bg-zinc-800 text-xs font-bold text-zinc-300">
                                    {{ $initials }}
                                </div>
                                <span class="font-medium text-white">{{ $customer->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-zinc-400">{{ $customer->email }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-400">
                                {{ $customer->orders_count }} {{ $customer->orders_count === 1 ? 'bestelling' : 'bestellingen' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-medium text-white">
                            €{{ number_format(($customer->orders_sum_total_in_cents ?? 0) / 100, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-zinc-500">{{ $customer->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.customers.show', $customer) }}" wire:navigate class="text-purple-400 hover:text-purple-300 text-sm transition">
                                Details →
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-zinc-800">{{ $this->customers->links() }}</div>
    </div>
</div>
