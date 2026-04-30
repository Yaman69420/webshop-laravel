<?php

namespace App\Actions\QrLogin;

use App\Enums\QrLoginStatus;
use App\Models\QrLoginSession;
use App\Models\User;
use App\Services\QrLoginService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsumeQrLoginAction
{
    public function __construct(
        private QrLoginService $qrLoginService,
    ) {}

    /**
     * Consume an approved QR login token and return the authenticated user.
     *
     * @security Uses a DB transaction with row locking to atomically check status
     *           and mark consumed. Prevents double-consumption.
     */
    public function execute(string $token): ?User
    {
        $tokenHash = $this->qrLoginService->hashToken($token);

        try {
            $session = DB::transaction(function () use ($tokenHash): ?QrLoginSession {
                $session = QrLoginSession::where('token_hash', $tokenHash)
                    ->lockForUpdate()
                    ->first();

                if (! $session) {
                    return null;
                }

                if ($session->status !== QrLoginStatus::Approved) {
                    return null;
                }

                if ($session->isExpired()) {
                    $session->update(['status' => QrLoginStatus::Expired]);

                    return null;
                }

                $session->markConsumed();

                return $session;
            });
        } catch (\Throwable $e) {
            Log::error('QR login consume failed', ['error' => $e->getMessage()]);

            return null;
        }

        return $session?->user;
    }
}
