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


}
