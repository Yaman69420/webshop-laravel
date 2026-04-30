<?php

namespace App\Actions\QrLogin;

use App\Enums\QrLoginStatus;
use App\Models\QrLoginSession;
use App\Services\QrLoginService;
use Illuminate\Http\Request;

class StartQrSessionAction
{
    public function __construct(
        private QrLoginService $qrLoginService,
    ) {}

    /**
     * Create a new QR login session and return the plain token.
     *
     * @return array{token: string, scan_url: string, expires_at: string}
     */
    public function execute(Request $request): array
    {
        // Invalidate any previous pending sessions from this IP
        QrLoginSession::where('ip_address', $request->ip())
            ->where('status', QrLoginStatus::Pending)
            ->update(['status' => QrLoginStatus::Expired]);

        $plainToken = $this->qrLoginService->generateToken();

        QrLoginSession::create([
            'token_hash' => $this->qrLoginService->hashToken($plainToken),
            'status' => QrLoginStatus::Pending,
            'browser_session_id' => $request->hasSession() ? $request->session()->getId() : 'test-session-id',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addMinutes(2),
        ]);

        $scanUrl = route('qr-login.scan', ['token' => $plainToken]);

        // Fix for localhost & Herd (.test): smartphones cannot resolve these.
        // Replace it with the machine's local IP address automatically and add port 8000.
        $parsedUrl = parse_url($scanUrl);
        $host = $parsedUrl['host'] ?? '';
        
        if (in_array($host, ['localhost', '127.0.0.1', '::1']) || str_ends_with($host, '.test')) {
            $localIp = env('LOCAL_IP', gethostbyname(gethostname()));
            $scanUrl = str_replace($host, $localIp, $scanUrl);
            
            // Add port 8000 if missing, because artisan serve runs on 8000
            if (!isset($parsedUrl['port'])) {
                $scanUrl = str_replace($localIp, $localIp . ':8000', $scanUrl);
            }
            
            // Force HTTP instead of HTTPS since artisan serve doesn't support SSL
            $scanUrl = str_replace('https://', 'http://', $scanUrl);
        }

        return [
            'token' => $plainToken,
            'scan_url' => $scanUrl,
            'expires_at' => now()->addMinutes(2)->toIso8601String(),
        ];
    }
}
