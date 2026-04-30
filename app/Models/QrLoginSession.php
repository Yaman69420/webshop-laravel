<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'token_hash',
    'status',
    'user_id',
    'browser_session_id',
    'ip_address',
    'user_agent',
    'expires_at',
    'approved_at',
    'consumed_at',
])]
class QrLoginSession extends Model
{
    /**
     * Valid status transitions for the QR login flow.
     * pending → approved | denied | expired
     * approved → consumed
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_DENIED = 'denied';

    public const STATUS_CONSUMED = 'consumed';

    public const STATUS_EXPIRED = 'expired';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'approved_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to only pending sessions.
     *
     * @param  Builder<QrLoginSession>  $query
     * @return Builder<QrLoginSession>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to only sessions that have not expired.
     *
     * @param  Builder<QrLoginSession>  $query
     * @return Builder<QrLoginSession>
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Check whether this session has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Mark the session as approved by the mobile user.
     *
     * @security Only call after verifying the user is authenticated and the session is still pending + not expired.
     */
    public function markApproved(User $user): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'user_id' => $user->id,
            'approved_at' => now(),
        ]);
    }

    /**
     * Mark the session as consumed (desktop user has been logged in).
     *
     * @security Only call after verifying the session is approved + not expired.
     */
    public function markConsumed(): void
    {
        $this->update([
            'status' => self::STATUS_CONSUMED,
            'consumed_at' => now(),
        ]);
    }

    /**
     * Mark the session as denied by the mobile user.
     */
    public function markDenied(): void
    {
        $this->update([
            'status' => self::STATUS_DENIED,
        ]);
    }

    /**
     * Find a QR login session by its plain-text token.
     *
     * @security Hashes the token before lookup — the raw token is never stored.
     */
    public static function findByToken(string $plainToken): ?self
    {
        return static::where('token_hash', hash('sha256', $plainToken))->first();
    }
}
