<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// ──────────────────────────────────────────────
// Storefront Routes
// ──────────────────────────────────────────────
Volt::route('/', 'catalog.products')->name('home');
Volt::route('/products', 'catalog.products')->name('products.index');
Volt::route('/products/new', 'catalog.new-products')->name('products.new');
Volt::route('/products/{slug}', 'catalog.product-detail')->name('products.show');
Volt::route('/categories', 'catalog.categories')->name('categories.index');
Volt::route('/cart', 'cart.index')->name('cart.index');

// ──────────────────────────────────────────────
// Auth-protected Storefront Routes
// ──────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('/dashboard', '/')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Volt::route('/checkout', 'checkout.index')->name('checkout.index');
    Volt::route('/checkout/success', 'checkout.success')->name('checkout.success');
    Volt::route('/my-orders', 'orders.index')->name('orders.index');
    Volt::route('/my-orders/{order}', 'orders.show')->name('orders.show');
});

// ──────────────────────────────────────────────
// Admin Routes
// ──────────────────────────────────────────────
Route::middleware(['auth', 'is_admin'])->prefix('admin')->group(function () {
    Volt::route('/', 'admin.dashboard')->name('admin.dashboard');
    Volt::route('/products', 'admin.products')->name('admin.products');
    Volt::route('/categories', 'admin.categories')->name('admin.categories');
    Volt::route('/orders', 'admin.orders')->name('admin.orders');
    Volt::route('/customers', 'admin.customers')->name('admin.customers');
    Volt::route('/customers/{user}', 'admin.customer-detail')->name('admin.customers.show');
});

require __DIR__.'/settings.php';
