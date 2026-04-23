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
                            <button wire:click="edit({{ $category->id }})" class="inline-flex items-center justify-center rounded-md p-1.5 text-zinc-400 hover:text-purple-400 hover:bg-zinc-800 transition" title="Bewerken">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                            </button>
                            <button wire:click="confirmDelete({{ $category->id }})" class="inline-flex items-center justify-center rounded-md p-1.5 text-zinc-400 hover:text-red-400 hover:bg-zinc-800 transition" title="Verwijderen">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                            </button>
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
