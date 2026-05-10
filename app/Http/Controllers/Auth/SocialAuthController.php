<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const ALLOWED_PROVIDERS = ['google', 'facebook', 'twitter-oauth-2'];

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, self::ALLOWED_PROVIDERS), 404);
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, self::ALLOWED_PROVIDERS), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable) {
            return redirect()->route('login')->withErrors(['social' => 'Unable to authenticate with ' . ucfirst($provider) . '.']);
        }

        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name'            => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'username'        => $this->generateUsername($socialUser->getNickname() ?? $socialUser->getName()),
                'email_verified_at' => now(),
                'social_provider' => $provider,
                'social_id'       => $socialUser->getId(),
                'social_token'    => $socialUser->token,
                'avatar'          => $socialUser->getAvatar(),
            ]
        );

        if (!$user->wasRecentlyCreated) {
            $user->update([
                'social_provider' => $provider,
                'social_id'       => $socialUser->getId(),
                'social_token'    => $socialUser->token,
            ]);
        }

        if ($user->is_banned) {
            return redirect()->route('login')->withErrors(['social' => 'Your account has been suspended.']);
        }

        Auth::login($user, true);
        $user->update(['is_online' => true, 'last_seen_at' => now()]);

        return redirect()->intended(route('chats.index'));
    }

    private function generateUsername(?string $base): string
    {
        $base = Str::slug($base ?? 'user', '');
        $base = substr($base ?: 'user', 0, 15);
        $username = $base;
        $counter  = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . $counter++;
        }

        return $username;
    }
}
