<?php

namespace Tests\Feature\Auth;

use App\Enums\QrLoginStatus;
use App\Models\QrLoginSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class QrLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_session_can_be_started(): void
    {
        $response = $this->post(route('qr-login.start'));

        $response->assertOk()
            ->assertJsonStructure(['token', 'scan_url', 'expires_at']);

        $this->assertDatabaseCount('qr_login_sessions', 1);
    }

    public function test_qr_token_is_hashed_in_database(): void
    {
        $response = $this->post(route('qr-login.start'));

        $plainToken = $response->json('token');
        $session = QrLoginSession::first();

        $this->assertNotEquals($plainToken, $session->token_hash);
        $this->assertEquals(hash('sha256', $plainToken), $session->token_hash);
    }

    public function test_qr_session_status_returns_pending(): void
    {
        $response = $this->post(route('qr-login.start'));
        $token = $response->json('token');

        $statusResponse = $this->getJson(route('qr-login.status', ['token' => $token]));

        $statusResponse->assertOk()
            ->assertJson(['status' => 'pending']);
    }

    public function test_qr_session_expires_after_two_minutes(): void
    {
        $response = $this->post(route('qr-login.start'));
        $token = $response->json('token');

        // Fast-forward time past the 2-minute expiry
        $this->travel(3)->minutes();

        $statusResponse = $this->getJson(route('qr-login.status', ['token' => $token]));

        $statusResponse->assertOk()
            ->assertJson(['status' => 'expired']);
    }

    public function test_guest_cannot_scan_qr_code(): void
    {
        $response = $this->post(route('qr-login.start'));
        $token = $response->json('token');

        // Try to scan without being authenticated — should redirect to login
        $scanResponse = $this->get(route('qr-login.scan', ['token' => $token]));

        $scanResponse->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_scan_qr_code(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('qr-login.start'));
        $token = $response->json('token');

        $scanResponse = $this->actingAs($user)
            ->get(route('qr-login.scan', ['token' => $token]));

        $scanResponse->assertOk();
        $scanResponse->assertViewIs('pages::auth.qr-scan');
    }

    public function test_authenticated_user_can_confirm_qr_login(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('qr-login.start'));
        $token = $response->json('token');

        $this->actingAs($user)
            ->post(route('qr-login.confirm'), ['token' => $token]);

        $session = QrLoginSession::first();
        $this->assertEquals(QrLoginStatus::Approved, $session->status);
        $this->assertEquals($user->id, $session->user_id);
        $this->assertNotNull($session->approved_at);
    }

    public function test_authenticated_user_can_deny_qr_login(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('qr-login.start'));
        $token = $response->json('token');

        $this->actingAs($user)
            ->post(route('qr-login.deny'), ['token' => $token]);

        $session = QrLoginSession::first();
        $this->assertEquals(QrLoginStatus::Denied, $session->status);
    }

    public function test_desktop_can_consume_approved_token(): void
    {
        $user = User::factory()->create();

        // 1. Start session (as guest desktop)
        $startResponse = $this->post(route('qr-login.start'));
        $token = $startResponse->json('token');

        // 2. Approve from mobile (acting as authenticated user)
        $session = QrLoginSession::first();
        $session->markApproved($user);

        // 3. Consume from desktop (as guest)
        $consumeResponse = $this->postJson(route('qr-login.consume'), ['token' => $token]);

        $consumeResponse->assertOk()
            ->assertJsonStructure(['redirect']);

        // Verify the desktop user is now authenticated
        $this->assertAuthenticatedAs($user);

        // Verify token is consumed
        $session->refresh();
        $this->assertEquals(QrLoginStatus::Consumed, $session->status);
        $this->assertNotNull($session->consumed_at);
    }

    public function test_consumed_token_cannot_be_reused(): void
    {
        $user = User::factory()->create();
        $plainToken = Str::random(64);

        // Create a session that has already been consumed
        QrLoginSession::create([
            'token_hash' => hash('sha256', $plainToken),
            'status' => QrLoginStatus::Consumed,
            'user_id' => $user->id,
            'expires_at' => now()->addMinutes(2),
            'approved_at' => now()->subSeconds(30),
            'consumed_at' => now()->subSeconds(10),
        ]);

        // Attempting to consume an already-consumed token should fail
        $this->postJson(route('qr-login.consume'), ['token' => $plainToken])
            ->assertStatus(422);

        $this->assertGuest();
    }

    public function test_expired_token_cannot_be_consumed(): void
    {
        $user = User::factory()->create();

        $startResponse = $this->post(route('qr-login.start'));
        $token = $startResponse->json('token');

        $session = QrLoginSession::first();
        $session->markApproved($user);

        // Fast-forward past expiry
        $this->travel(3)->minutes();

        $consumeResponse = $this->postJson(route('qr-login.consume'), ['token' => $token]);
        $consumeResponse->assertStatus(422);

        $this->assertGuest();
    }

    public function test_pending_token_cannot_be_consumed(): void
    {
        $startResponse = $this->post(route('qr-login.start'));
        $token = $startResponse->json('token');

        // Try to consume without approval
        $consumeResponse = $this->postJson(route('qr-login.consume'), ['token' => $token]);
        $consumeResponse->assertStatus(422);

        $this->assertGuest();
    }
}
