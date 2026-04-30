<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsConfirmed
{
    /**
     * Handle an incoming request.
     * Social users (geen wachtwoord) slaan de bevestiging altijd over.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Social login users hebben geen wachtwoord — geen confirmatie nodig
        if (auth()->check() && is_null(auth()->user()->password)) {
            return $next($request);
        }

        $confirmedAt = time() - $request->session()->get('auth.password_confirmed_at', 0);

        if ($confirmedAt > config('auth.password_timeout', 10800)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Password confirmation required.'], 423);
            }

            return redirect()->guest(route('password.confirm'));
        }

        return $next($request);
    }
}
