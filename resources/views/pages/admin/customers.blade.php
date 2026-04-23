<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Livewire\Volt\Component;

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
    <h1 class="text-2xl font-bold text-white mb-6">Klanten</h1>

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
                        <td class="px-4 py-3 text-white font-medium">{{ $customer->name }}</td>
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
