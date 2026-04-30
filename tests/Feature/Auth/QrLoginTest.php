<?php

use App\Actions\QrLogin\ConsumeQrLoginAction;
use App\Enums\QrLoginStatus;
use App\Models\QrLoginSession;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('starts qr session', function () {
    Volt::test('auth.qr-login')
        ->dispatch('start-qr-session')
        ->assertSet('qrStatus', 'pending')
        ->assertDispatched('qr-session-started');

    expect(QrLoginSession::count())->toBe(1);
});

it('hashes the qr token in the database', function () {
    $component = Volt::test('auth.qr-login')
        ->dispatch('start-qr-session');

    $plainToken = $component->get('token');
    $session = QrLoginSession::first();

    expect($plainToken)->not->toBe($session->token_hash);
    expect(hash('sha256', $plainToken))->toBe($session->token_hash);
});

it('expires qr session after two minutes', function () {
    $component = Volt::test('auth.qr-login')
        ->dispatch('start-qr-session');

    $this->travel(3)->minutes();

    // Trigger pollStatus
    $component->call('pollStatus');

    $component->assertSet('qrStatus', 'expired');
});

it('guest cannot scan qr code', function () {
    $component = Volt::test('auth.qr-login')->dispatch('start-qr-session');
    $token = $component->get('token');

    $this->get(route('qr-login.scan', ['token' => $token]))
        ->assertRedirect(route('login'));
});

it('authenticated user can scan qr code', function () {
    $user = User::factory()->create();
    $component = Volt::test('auth.qr-login')->dispatch('start-qr-session');
    $token = $component->get('token');

    $this->actingAs($user)
        ->get(route('qr-login.scan', ['token' => $token]))
        ->assertOk()
        ->assertViewIs('pages::auth.qr-scan');
});

it('authenticated user can confirm qr login', function () {
    $user = User::factory()->create();
    $component = Volt::test('auth.qr-login')->dispatch('start-qr-session');
    $token = $component->get('token');

    $this->actingAs($user)
        ->post(route('qr-login.confirm'), ['token' => $token]);

    $session = QrLoginSession::first();
    expect($session->status)->toBe(QrLoginStatus::Approved)
        ->and($session->user_id)->toBe($user->id)
        ->and($session->approved_at)->not->toBeNull();
});

it('authenticated user can deny qr login', function () {
    $user = User::factory()->create();
    $component = Volt::test('auth.qr-login')->dispatch('start-qr-session');
    $token = $component->get('token');

    $this->actingAs($user)
        ->post(route('qr-login.deny'), ['token' => $token]);

    $session = QrLoginSession::first();
    expect($session->status)->toBe(QrLoginStatus::Denied);
});

it('desktop can consume approved token and redirect', function () {
    $user = User::factory()->create();
    $component = Volt::test('auth.qr-login')->dispatch('start-qr-session');
    
    // Approve from mobile
    $session = QrLoginSession::first();
    $session->markApproved($user);

    // Consume from desktop via pollStatus
    $component->call('pollStatus')
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);

    $session->refresh();
    expect($session->status)->toBe(QrLoginStatus::Consumed)
        ->and($session->consumed_at)->not->toBeNull();
});

it('prevents consuming an already consumed token', function () {
    $user = User::factory()->create();
    $plainToken = Str::random(64);

    QrLoginSession::create([
        'token_hash' => hash('sha256', $plainToken),
        'status' => QrLoginStatus::Consumed,
        'user_id' => $user->id,
        'expires_at' => now()->addMinutes(2),
        'approved_at' => now()->subSeconds(30),
        'consumed_at' => now()->subSeconds(10),
    ]);

    $action = app(ConsumeQrLoginAction::class);
    $result = $action->execute($plainToken);

    expect($result)->toBeNull();
});

it('prevents consuming an expired token', function () {
    $user = User::factory()->create();
    $plainToken = Str::random(64);

    QrLoginSession::create([
        'token_hash' => hash('sha256', $plainToken),
        'status' => QrLoginStatus::Approved,
        'user_id' => $user->id,
        'expires_at' => now()->subMinutes(2),
        'approved_at' => now()->subMinutes(3),
    ]);

    $action = app(ConsumeQrLoginAction::class);
    $result = $action->execute($plainToken);

    expect($result)->toBeNull();
});

it('prevents consuming a pending token', function () {
    $plainToken = Str::random(64);

    QrLoginSession::create([
        'token_hash' => hash('sha256', $plainToken),
        'status' => QrLoginStatus::Pending,
        'expires_at' => now()->addMinutes(2),
    ]);

    $action = app(ConsumeQrLoginAction::class);
    $result = $action->execute($plainToken);

    expect($result)->toBeNull();
});
