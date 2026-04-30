<?php

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
        ->where('status', QrLoginSession::STATUS_PENDING)
        ->update(['status' => QrLoginSession::STATUS_EXPIRED]);
})->everyMinute()->name('qr-login:prune-expired');
