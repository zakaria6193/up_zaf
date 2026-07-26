<?php

namespace App\Http\Controllers\Auth;

use App\Auth\DualEloquentUserProvider;
use App\Http\Controllers\Controller;
use App\Models\BusinessUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth consent screen.
     */
    public function redirect(): RedirectResponse
    {
        if (! $this->googleConfigured()) {
            return redirect()->route('login')
                ->with('error', 'Google sign-in is not configured yet.');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the Google OAuth callback.
     */
    public function callback(): RedirectResponse
    {
        if (! $this->googleConfigured()) {
            return redirect()->route('login')
                ->with('error', 'Google sign-in is not configured yet.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')
                ->with('error', 'Google sign-in failed. Please try again.');
        }

        $user = BusinessUser::query()
            ->where(function ($query) use ($googleUser): void {
                $query->where('google_id', $googleUser->getId())
                    ->orWhere('email', $googleUser->getEmail());
            })
            ->first();

        if ($user) {
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            $user = BusinessUser::create([
                'name' => $googleUser->getName() ?: ($googleUser->getNickname() ?: 'Business Owner'),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => Str::password(32),
                'email_verified_at' => now(),
                'is_premium' => false,
                'trial_ends_at' => now()->addMinutes(max(1, (int) config('business.free_trial_minutes', 10))),
            ]);
        }

        Auth::login($user, remember: true);
        session([DualEloquentUserProvider::SESSION_KEY => BusinessUser::class]);

        return redirect()->intended(route('business.dashboard'));
    }

    protected function googleConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }
}
