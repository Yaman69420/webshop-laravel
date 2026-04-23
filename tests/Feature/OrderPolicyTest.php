<?php

use App\Models\Order;
use App\Models\User;

it('allows user to view their own order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('orders.show', $order))
        ->assertOk();
});

it('denies user from viewing another users order', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->get(route('orders.show', $order))
        ->assertForbidden();
});

it('allows admin to view any order via admin panel', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.orders'))
        ->assertOk();
});
