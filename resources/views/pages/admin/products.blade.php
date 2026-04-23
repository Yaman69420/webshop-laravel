<?php

use App\Models\Product;
use App\Models\Category;
use App\Actions\Catalog\CreateProductAction;
use App\Actions\Catalog\UpdateProductAction;
use App\Actions\Catalog\DeleteProductAction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new
#[Layout('layouts.admin')]
#[Title('Producten — Admin')]
class extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('required|numeric|min:0.01')]
    public string $price = '';

    #[Validate('required|integer|min:0')]
    public int $stock = 0;

    #[Validate('required|exists:categories,id')]
    public string $category_id = '';

    #[Validate('boolean')]
    public bool $is_active = true;

    #[Computed]
    public function products()
    {
        return Product::with('category')->latest()->paginate(15);
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'description', 'price', 'stock', 'category_id', 'is_active']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description ?? '';
        $this->price = number_format($product->price_in_cents / 100, 2, '.', '');
        $this->stock = $product->stock;
        $this->category_id = (string) $product->category_id;
        $this->is_active = $product->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'price_in_cents' => (int) round((float) $this->price * 100),
            'stock' => $this->stock,
            'category_id' => (int) $this->category_id,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            app(UpdateProductAction::class)->execute(Product::findOrFail($this->editingId), $data);
        } else {
            app(CreateProductAction::class)->execute($data);
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'description', 'price', 'stock', 'category_id', 'is_active']);
        unset($this->products);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            app(DeleteProductAction::class)->execute(Product::findOrFail($this->deletingId));
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
        unset($this->products);
    }
}

?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Producten</h1>
        <flux:button wire:click="create" variant="primary" class="bg-purple-600 hover:bg-purple-500">
            <flux:icon name="plus" class="size-4 mr-1" /> Nieuw Product
        </flux:button>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-zinc-400 border-b border-zinc-800">
                        <th class="px-4 py-3 font-medium">Product</th>
                        <th class="px-4 py-3 font-medium">Categorie</th>
                        <th class="px-4 py-3 font-medium">Prijs</th>
                        <th class="px-4 py-3 font-medium">Voorraad</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Acties</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @foreach($this->products as $product)
                        <tr class="text-zinc-300 hover:bg-zinc-800/50 transition">
                            <td class="px-4 py-3 font-medium text-white">{{ $product->name }}</td>
                            <td class="px-4 py-3 text-zinc-400">{{ $product->category->name }}</td>
                            <td class="px-4 py-3">{{ $product->formattedPrice() }}</td>
                            <td class="px-4 py-3">
                                <span class="{{ $product->stock > 0 ? 'text-green-400' : 'text-red-400' }}">{{ $product->stock }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($product->is_active)
                                    <span class="inline-flex rounded-full bg-green-500/10 px-2 py-0.5 text-xs font-medium text-green-400">Actief</span>
                                @else
                                    <span class="inline-flex rounded-full bg-zinc-500/10 px-2 py-0.5 text-xs font-medium text-zinc-400">Inactief</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                <flux:button wire:click="edit({{ $product->id }})" size="sm" variant="ghost">
                                    <flux:icon name="pencil" class="size-4" />
                                </flux:button>
                                <flux:button wire:click="confirmDelete({{ $product->id }})" size="sm" variant="ghost" class="text-red-400">
                                    <flux:icon name="trash" class="size-4" />
                                </flux:button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-zinc-800">
            {{ $this->products->links() }}
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <form wire:submit="save" class="space-y-4">
            <flux:heading size="lg">{{ $editingId ? 'Product Bewerken' : 'Nieuw Product' }}</flux:heading>

            <flux:input wire:model="name" label="Naam" required />
            <flux:textarea wire:model="description" label="Beschrijving" rows="3" />
            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="price" label="Prijs (€)" type="number" step="0.01" min="0.01" required />
                <flux:input wire:model="stock" label="Voorraad" type="number" min="0" required />
            </div>
            <flux:select wire:model="category_id" label="Categorie" required>
                <option value="">Kies categorie...</option>
                @foreach($this->categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </flux:select>
            <flux:checkbox wire:model="is_active" label="Actief" />

            <div class="flex justify-end gap-3 pt-4">
                <flux:button wire:click="$set('showModal', false)" variant="ghost">Annuleren</flux:button>
                <flux:button type="submit" variant="primary" class="bg-purple-600">Opslaan</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-sm">
        <flux:heading size="lg">Product Verwijderen</flux:heading>
        <p class="mt-2 text-sm text-zinc-400">Weet je zeker dat je dit product wilt verwijderen? Dit kan niet ongedaan worden gemaakt.</p>
        <div class="flex justify-end gap-3 mt-6">
            <flux:button wire:click="$set('showDeleteModal', false)" variant="ghost">Annuleren</flux:button>
            <flux:button wire:click="delete" variant="danger">Verwijderen</flux:button>
        </div>
    </flux:modal>
</div>
