<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class CartCount extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->count = app(CartService::class)->count();
    }

    #[On('cart-updated')]
    public function refresh(): void
    {
        $this->count = app(CartService::class)->count();
    }

    public function render()
    {
        return view('livewire.cart-count');
    }
}
