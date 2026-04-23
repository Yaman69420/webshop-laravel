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
use Livewire\WithFileUploads;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;

new
#[Layout('layouts.admin')]
#[Title('Producten — Admin')]
class extends Component
{
    use WithPagination;
    use WithFileUploads;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;
    public ?string $existingImage = null;

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

    #[Validate('nullable|image|max:2048')]
    public $image = null;

    public bool $removeImage = false;

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
        $this->reset(['editingId', 'name', 'description', 'price', 'stock', 'category_id', 'is_active', 'image', 'existingImage', 'removeImage']);
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
        $this->existingImage = $product->image_path;
        $this->image = null;
        $this->removeImage = false;
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

        // Handle image upload
        if ($this->image) {
            // Delete old image if replacing
            if ($this->editingId) {
                $product = Product::findOrFail($this->editingId);
                if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                    Storage::disk('public')->delete($product->image_path);
                }
            }
            $data['image_path'] = $this->image->store('products', 'public');
        } elseif ($this->removeImage && $this->editingId) {
            $product = Product::findOrFail($this->editingId);
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = null;
        }

        if ($this->editingId) {
            $product = $product ?? Product::findOrFail($this->editingId);
            app(UpdateProductAction::class)->execute($product, $data);
        } else {
            app(CreateProductAction::class)->execute($data);
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'description', 'price', 'stock', 'category_id', 'is_active', 'image', 'existingImage', 'removeImage']);
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
            $product = Product::findOrFail($this->deletingId);
            // Delete image file
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            app(DeleteProductAction::class)->execute($product);
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
                        <th class="px-4 py-3 font-medium w-12">Foto</th>
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
                            <td class="px-4 py-2">
                                @if($product->image_path)
                                    <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="size-10 rounded-lg object-cover border border-zinc-700">
                                @else
                                    <div class="size-10 rounded-lg bg-zinc-800 border border-zinc-700 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-zinc-600"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" /></svg>
                                    </div>
                                @endif
                            </td>
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
                                <button wire:click="edit({{ $product->id }})" class="inline-flex items-center justify-center rounded-md p-1.5 text-zinc-400 hover:text-purple-400 hover:bg-zinc-800 transition" title="Bewerken">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                </button>
                                <button wire:click="confirmDelete({{ $product->id }})" class="inline-flex items-center justify-center rounded-md p-1.5 text-zinc-400 hover:text-red-400 hover:bg-zinc-800 transition" title="Verwijderen">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </button>
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

            {{-- Image Upload --}}
            <div>
                <label class="block text-sm font-medium text-zinc-300 mb-2">Productafbeelding</label>

                {{-- Current / Preview --}}
                @if($image)
                    <div class="mb-3 relative inline-block">
                        <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="h-32 w-32 rounded-xl object-cover border border-purple-500/50">
                        <button type="button" wire:click="$set('image', null)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-0.5 hover:bg-red-400 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                @elseif($existingImage && !$removeImage)
                    <div class="mb-3 relative inline-block">
                        <img src="{{ Storage::url($existingImage) }}" alt="Huidige afbeelding" class="h-32 w-32 rounded-xl object-cover border border-zinc-700">
                        <button type="button" wire:click="$set('removeImage', true)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-0.5 hover:bg-red-400 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                @endif

                <div class="flex items-center gap-3">
                    <label class="cursor-pointer inline-flex items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-800 px-4 py-2 text-sm text-zinc-300 hover:border-purple-500/50 hover:text-white transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                        {{ $image ? 'Andere foto kiezen' : ($existingImage && !$removeImage ? 'Foto wijzigen' : 'Foto uploaden') }}
                        <input type="file" wire:model="image" accept="image/*" class="hidden">
                    </label>
                    @if($removeImage)
                        <button type="button" wire:click="$set('removeImage', false)" class="text-sm text-zinc-500 hover:text-white transition">Verwijdering ongedaan maken</button>
                    @endif
                </div>

                <div wire:loading wire:target="image" class="mt-2 text-sm text-purple-400">
                    Afbeelding uploaden...
                </div>

                @error('image')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

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
