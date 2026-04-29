<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.admin')]
#[Title('Klanten — Admin')]
class extends Component
{
    use WithPagination;

    #[Computed]
    public function customers()
    {
        return User::where('is_admin', false)
            ->withCount('orders')
            ->latest()
            ->paginate(15);
    }
}

?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Klanten</h1>
        <p class="text-sm text-zinc-400 mt-1">Overzicht van alle geregistreerde klanten.</p>
    </div>

    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-zinc-400 border-b border-zinc-800">
                    <th class="px-4 py-3 font-medium">Naam</th>
                    <th class="px-4 py-3 font-medium">E-mail</th>
                    <th class="px-4 py-3 font-medium">Bestellingen</th>
                    <th class="px-4 py-3 font-medium">Lid sinds</th>
                    <th class="px-4 py-3 font-medium text-right">Bekijken</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @foreach($this->customers as $customer)
                    <tr class="text-zinc-300 hover:bg-zinc-800/50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @php
                                    $initials = collect(explode(' ', $customer->name))
                                        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                        ->take(2)
                                        ->implode('');
                                @endphp
                                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border border-zinc-700 bg-zinc-800 text-xs font-bold text-zinc-300">
                                    {{ $initials }}
                                </div>
                                <span class="font-medium text-white">{{ $customer->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-zinc-400">{{ $customer->email }}</td>
                        <td class="px-4 py-3">{{ $customer->orders_count }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $customer->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.customers.show', $customer) }}" wire:navigate class="text-purple-400 hover:text-purple-300 text-sm">
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
