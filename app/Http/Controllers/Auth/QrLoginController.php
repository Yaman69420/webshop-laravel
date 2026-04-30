<?php

namespace App\Http\Controllers\Auth;

use App\Actions\QrLogin\ConfirmQrLoginAction;
use App\Actions\QrLogin\ConsumeQrLoginAction;
use App\Actions\QrLogin\DenyQrLoginAction;
use App\Actions\QrLogin\StartQrSessionAction;
use App\Enums\QrLoginStatus;
use App\Http\Controllers\Controller;
use App\Services\QrLoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class QrLoginController extends Controller
{
    /**
     * Create a new QR login session and return the token + scan URL.
     */
    public function start(Request $request, StartQrSessionAction $action): JsonResponse
    {
        return response()->json($action->execute($request));
    }

    /**
     * Return the current status of a QR login session (for desktop polling).
     */
    public function status(Request $request, QrLoginService $service): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $session = $service->findByToken($request->input('token'));

        if (! $session) {
            return response()->json(['status' => 'invalid'], 404);
        }

        return response()->json([
            'status' => $service->resolveStatus($session)->value,
        ]);
    }

    /**
     * Show the mobile confirmation page for the scanned QR code.
     */
    public function scan(Request $request, QrLoginService $service): View|RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $session = $service->findByToken($request->query('token'));

        if (! $session || $session->status !== QrLoginStatus::Pending) {
            return redirect()->route('home')->with('status', 'Deze QR-code is ongeldig of al gebruikt.');
        }

        if ($session->isExpired()) {
            $session->update(['status' => QrLoginStatus::Expired]);

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
     */
    public function confirm(Request $request, ConfirmQrLoginAction $action): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $result = $action->execute($request->input('token'), $request->user());

        return redirect()->route('home')->with('status', $result['message']);
    }

    /**
     * Deny the QR login from the mobile device.
     */
    public function deny(Request $request, DenyQrLoginAction $action): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $result = $action->execute($request->input('token'), $request->user());

        return redirect()->route('home')->with('status', $result['message']);
    }

    /**
     * Consume an approved QR login token and authenticate the desktop user.
     */
    public function consume(Request $request, ConsumeQrLoginAction $action): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $user = $action->execute($request->input('token'));

        if (! $user) {
            return response()->json(['error' => 'Token kan niet worden gebruikt.'], 422);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return response()->json([
            'redirect' => route('home'),
        ]);
    }
}
