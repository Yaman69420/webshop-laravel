<?php

use App\Models\Category;
use App\Actions\Admin\CreateCategoryAction;
use App\Actions\Admin\UpdateCategoryAction;
use App\Actions\Admin\DeleteCategoryAction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new
#[Layout('layouts.admin')]
#[Title('Categorieën — Admin')]
class extends Component
{
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Computed]
    public function categories()
    {
        return Category::withCount('products')->orderBy('name')->get();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            app(UpdateCategoryAction::class)->execute(Category::findOrFail($this->editingId), ['name' => $this->name]);
        } else {
            app(CreateCategoryAction::class)->execute(['name' => $this->name]);
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name']);
        unset($this->categories);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            app(DeleteCategoryAction::class)->execute(Category::findOrFail($this->deletingId));
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
        unset($this->categories);
    }
}

?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Categorieën</h1>
        <flux:button wire:click="create" variant="primary" class="bg-purple-600 hover:bg-purple-500">
            <flux:icon name="plus" class="size-4 mr-1" /> Nieuwe Categorie
        </flux:button>
    </div>

    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-zinc-400 border-b border-zinc-800">
                    <th class="px-4 py-3 font-medium">Naam</th>
                    <th class="px-4 py-3 font-medium">Slug</th>
                    <th class="px-4 py-3 font-medium">Producten</th>
                    <th class="px-4 py-3 font-medium text-right">Acties</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @foreach($this->categories as $category)
                    <tr class="text-zinc-300 hover:bg-zinc-800/50 transition">
                        <td class="px-4 py-3 font-medium text-white">{{ $category->name }}</td>
                        <td class="px-4 py-3 font-mono text-zinc-500 text-xs">{{ $category->slug }}</td>
                        <td class="px-4 py-3">{{ $category->products_count }}</td>
                        <td class="px-4 py-3 text-right space-x-1">
                            <flux:button wire:click="edit({{ $category->id }})" size="sm" variant="ghost">
                                <flux:icon name="pencil" class="size-4" />
                            </flux:button>
                            <flux:button wire:click="confirmDelete({{ $category->id }})" size="sm" variant="ghost" class="text-red-400">
                                <flux:icon name="trash" class="size-4" />
                            </flux:button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    <flux:modal wire:model="showModal" class="max-w-md">
        <form wire:submit="save" class="space-y-4">
            <flux:heading size="lg">{{ $editingId ? 'Categorie Bewerken' : 'Nieuwe Categorie' }}</flux:heading>
            <flux:input wire:model="name" label="Naam" required />
            <div class="flex justify-end gap-3 pt-4">
                <flux:button wire:click="$set('showModal', false)" variant="ghost">Annuleren</flux:button>
                <flux:button type="submit" variant="primary" class="bg-purple-600">Opslaan</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-sm">
        <flux:heading size="lg">Categorie Verwijderen</flux:heading>
        <p class="mt-2 text-sm text-zinc-400">Alle producten in deze categorie worden ook verwijderd. Weet je het zeker?</p>
        <div class="flex justify-end gap-3 mt-6">
            <flux:button wire:click="$set('showDeleteModal', false)" variant="ghost">Annuleren</flux:button>
            <flux:button wire:click="delete" variant="danger">Verwijderen</flux:button>
        </div>
    </flux:modal>
</div>
