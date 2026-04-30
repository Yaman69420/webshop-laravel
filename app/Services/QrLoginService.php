<?php

namespace App\Services;

use App\Enums\QrLoginStatus;
use App\Models\QrLoginSession;
use Illuminate\Support\Str;

class QrLoginService
{
    /**
     * Generate a cryptographically random token.
     *
     * @security Uses Str::random() which relies on /dev/urandom.
     */
    public function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Hash a plain-text token for storage.
     *
     * @security Only the hash is stored — the raw token is never persisted.
     */
    public function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    /**
     * Find a QR login session by its plain-text token.
     */
    public function findByToken(string $plainToken): ?QrLoginSession
    {
        return QrLoginSession::where('token_hash', $this->hashToken($plainToken))->first();
    }

    /**
     * Resolve the effective status of a session, auto-expiring if needed.
     */
    public function resolveStatus(QrLoginSession $session): QrLoginStatus
    {
        if ($session->status === QrLoginStatus::Pending && $session->isExpired()) {
            $session->update(['status' => QrLoginStatus::Expired]);

            return QrLoginStatus::Expired;
        }

        return $session->status;
    }
}
