<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\QrLoginSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QrLoginController extends Controller
{
    /**
     * Create a new QR login session and return the token + scan URL.
     *
     * @security Token is cryptographically random (64 chars). Only the SHA-256 hash is stored.
     */
    public function start(Request $request): JsonResponse
    {
        $plainToken = Str::random(64);

        QrLoginSession::create([
            'token_hash' => hash('sha256', $plainToken),
            'status' => QrLoginSession::STATUS_PENDING,
            'browser_session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addMinutes(2),
        ]);

        return response()->json([
            'token' => $plainToken,
            'scan_url' => route('qr-login.scan', ['token' => $plainToken]),
            'expires_at' => now()->addMinutes(2)->toIso8601String(),
        ]);
    }

    /**
     * Return the current status of a QR login session (for desktop polling).
     *
     * @security Expired sessions are reported as expired even if DB status is still pending.
     */
    public function status(string $token): JsonResponse
    {
        $session = QrLoginSession::findByToken($token);

        if (! $session) {
            return response()->json(['status' => 'invalid'], 404);
        }

        $status = $session->status;

        // Auto-expire pending sessions that have passed their TTL
        if ($status === QrLoginSession::STATUS_PENDING && $session->isExpired()) {
            $session->update(['status' => QrLoginSession::STATUS_EXPIRED]);
            $status = QrLoginSession::STATUS_EXPIRED;
        }

        return response()->json(['status' => $status]);
    }

    /**
     * Show the mobile confirmation page for the scanned QR code.
     *
     * @security Requires authenticated user. Validates token is pending and not expired.
     */
    public function scan(Request $request): View|RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $session = QrLoginSession::findByToken($request->query('token'));

        if (! $session || $session->status !== QrLoginSession::STATUS_PENDING) {
            return redirect()->route('home')->with('status', 'Deze QR-code is ongeldig of al gebruikt.');
        }

        if ($session->isExpired()) {
            $session->update(['status' => QrLoginSession::STATUS_EXPIRED]);

            return redirect()->route('home')->with('status', 'Deze QR-code is verlopen.');
        }

        return view('pages::auth.qr-scan', [
            'token' => $request->query('token'),
            'ip_address' => $session->ip_address,
            'user_agent' => $session->user_agent,
        ]);
    }

    /**
     * Confirm the QR login from the mobile device.
     *
     * @security Re-validates token is still pending + not expired to guard against race conditions.
     *           Links the authenticated user to the QR session.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $session = QrLoginSession::findByToken($request->input('token'));

        if (! $session || $session->status !== QrLoginSession::STATUS_PENDING) {
            Log::warning('QR login confirm attempt on non-pending session', [
                'token_hash' => $session?->token_hash,
                'status' => $session?->status,
                'user_id' => $request->user()->id,
            ]);

            return redirect()->route('home')->with('status', 'Deze QR-code is ongeldig of al gebruikt.');
        }

        if ($session->isExpired()) {
            $session->update(['status' => QrLoginSession::STATUS_EXPIRED]);

            return redirect()->route('home')->with('status', 'Deze QR-code is verlopen.');
        }

        $session->markApproved($request->user());

        return redirect()->route('home')->with('status', 'Login is bevestigd! Het andere apparaat wordt nu ingelogd.');
    }

    /**
     * Deny the QR login from the mobile device.
     */
    public function deny(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $session = QrLoginSession::findByToken($request->input('token'));

        if (! $session || $session->status !== QrLoginSession::STATUS_PENDING) {
            return redirect()->route('home')->with('status', 'Deze QR-code is ongeldig of al gebruikt.');
        }

        $session->markDenied();

        Log::info('QR login denied by mobile user', [
            'token_hash' => $session->token_hash,
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('home')->with('status', 'Login is geweigerd.');
    }

    /**
     * Consume an approved QR login token and authenticate the desktop user.
     *
     * @security Uses a DB transaction to atomically check status and mark consumed.
     *           Prevents double-consumption. Regenerates session after login.
     */
    public function consume(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $tokenHash = hash('sha256', $request->input('token'));

        try {
            /**
             * @security Use a DB transaction with row locking to atomically check status
             *           and mark consumed. Returns the session only if successfully consumed.
             */
            $session = DB::transaction(function () use ($tokenHash): ?QrLoginSession {
                $session = QrLoginSession::where('token_hash', $tokenHash)
                    ->lockForUpdate()
                    ->first();

                if (! $session) {
                    return null;
                }

                // Only approved, non-expired sessions can be consumed
                if ($session->status !== QrLoginSession::STATUS_APPROVED) {
                    return null;
                }

                if ($session->isExpired()) {
                    $session->update(['status' => QrLoginSession::STATUS_EXPIRED]);

                    return null;
                }

                $session->markConsumed();

                return $session;
            });
        } catch (\Throwable $e) {
            Log::error('QR login consume failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Er is een fout opgetreden.'], 500);
        }

        if (! $session) {
            return response()->json(['error' => 'Token kan niet worden gebruikt.'], 422);
        }

        // Authenticate the desktop user with the same user that approved on mobile
        Auth::login($session->user, remember: true);
        $request->session()->regenerate();

        return response()->json([
            'redirect' => route('home'),
        ]);
    }
}
