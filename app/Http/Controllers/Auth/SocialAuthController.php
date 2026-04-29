<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to the social provider's OAuth page.
     */
    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'github']), 404);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from the social provider.
     */
    public function callback(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'github']), 404);

        $socialUser = Socialite::driver($provider)->user();

        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Gebruiker',
                'social_provider' => $provider,
                'social_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'email_verified_at' => now(),
            ]
        );

        // Update social info if not yet set
        if (! $user->social_provider) {
            $user->update([
                'social_provider' => $provider,
                'social_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('home'));
    }
}
