<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

// ──────────────────────────────────────────────
// Storefront Routes
// ──────────────────────────────────────────────
Route::livewire('/', 'pages::catalog.products')->name('home');
Route::livewire('/products', 'pages::catalog.products')->name('products.index');
Route::livewire('/products/new', 'pages::catalog.new-products')->name('products.new');
Route::livewire('/products/{slug}', 'pages::catalog.product-detail')->name('products.show');
Route::livewire('/categories', 'pages::catalog.categories')->name('categories.index');
Route::livewire('/cart', 'pages::cart.index')->name('cart.index');

// ──────────────────────────────────────────────
// Auth-protected Storefront Routes
// ──────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::livewire('/checkout', 'pages::checkout.index')->name('checkout.index');
    Route::livewire('/checkout/success', 'pages::checkout.success')->name('checkout.success');
    Route::livewire('/my-orders', 'pages::orders.index')->name('orders.index');
    Route::livewire('/my-orders/{order}', 'pages::orders.show')->name('orders.show');
});

// ──────────────────────────────────────────────
// Admin Routes
// ──────────────────────────────────────────────
Route::middleware(['auth', 'is_admin'])->prefix('admin')->group(function () {
    Route::livewire('/', 'pages::admin.dashboard')->name('admin.dashboard');
    Route::livewire('/products', 'pages::admin.products')->name('admin.products');
    Route::livewire('/categories', 'pages::admin.categories')->name('admin.categories');
    Route::livewire('/orders', 'pages::admin.orders')->name('admin.orders');
    Route::livewire('/customers', 'pages::admin.customers')->name('admin.customers');
    Route::livewire('/customers/{user}', 'pages::admin.customer-detail')->name('admin.customers.show');
});

require __DIR__.'/settings.php';
