<?php

use App\Enums\QrLoginStatus;
use App\Models\QrLoginSession;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Prune expired QR login sessions that are still marked as pending.
 */
Schedule::call(function () {
    QrLoginSession::where('expires_at', '<', now())
        ->where('status', QrLoginStatus::Pending)
        ->update(['status' => QrLoginStatus::Expired]);
})->everyMinute()->name('qr-login:prune-expired');

/**
 * Delete old QR login sessions that are no longer needed.
 */
Schedule::call(function () {
    QrLoginSession::where('created_at', '<', now()->subDay())
        ->whereIn('status', [
            QrLoginStatus::Expired,
            QrLoginStatus::Consumed,
            QrLoginStatus::Denied,
        ])
        ->delete();
})->daily()->name('qr-login:cleanup-old');
