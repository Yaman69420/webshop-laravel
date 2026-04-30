<?php

namespace App\Actions\QrLogin;

use App\Enums\QrLoginStatus;
use App\Models\User;
use App\Services\QrLoginService;
use Illuminate\Support\Facades\Log;

class DenyQrLoginAction
{
    public function __construct(
        private QrLoginService $qrLoginService,
    ) {}

    /**
     * Deny a QR login session from the mobile device.
     *
     * @return array{success: bool, message: string}
     */
    public function execute(string $token, User $user): array
    {
        $session = $this->qrLoginService->findByToken($token);

        if (! $session || $session->status !== QrLoginStatus::Pending) {
            return ['success' => false, 'message' => 'Deze QR-code is ongeldig of al gebruikt.'];
        }

        $session->markDenied();

        Log::info('QR login denied by mobile user', [
            'token_hash' => $session->token_hash,
            'user_id' => $user->id,
        ]);

        return ['success' => true, 'message' => 'Login is geweigerd.'];
    }
}
