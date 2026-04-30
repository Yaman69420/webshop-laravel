<?php

namespace App\Actions\QrLogin;

use App\Enums\QrLoginStatus;
use App\Models\User;
use App\Services\QrLoginService;
use Illuminate\Support\Facades\Log;

class ConfirmQrLoginAction
{
    public function __construct(
        private QrLoginService $qrLoginService,
    ) {}

    /**
     * Confirm a QR login session from the mobile device.
     *
     * @security Re-validates the session is still pending and not expired before approving.
     *
     * @return array{success: bool, message: string}
     */
    public function execute(string $token, User $user): array
    {
        $session = $this->qrLoginService->findByToken($token);

        if (! $session || $session->status !== QrLoginStatus::Pending) {
            Log::warning('QR login confirm attempt on non-pending session', [
                'token_hash' => $session?->token_hash,
                'status' => $session?->status,
                'user_id' => $user->id,
            ]);

            return ['success' => false, 'message' => 'Deze QR-code is ongeldig of al gebruikt.'];
        }

        if ($session->isExpired()) {
            $session->update(['status' => QrLoginStatus::Expired]);

            return ['success' => false, 'message' => 'Deze QR-code is verlopen.'];
        }

        $session->markApproved($user);

        return ['success' => true, 'message' => 'Login is bevestigd! Het andere apparaat wordt nu ingelogd.'];
    }
}
