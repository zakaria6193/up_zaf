<?php

namespace App\Providers;

use App\Models\User;
use App\Responses\LoginResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            LoginResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        // Custom authentication logic to support both User (admin/manager) and BusinessUser
        Fortify::authenticateUsing(function (Request $request) {
            // Determine which guard to use based on hidden field, referrer URL, or current URL
            $isAdminLogin = $request->input('login_type') === 'admin'
                || str_contains($request->header('referer', ''), '/adminos')
                || $request->is('adminos/*')
                || $request->is('adminos');

            if ($isAdminLogin) {
                // Admin login - check users table
                $user = User::where('email', $request->email)->first();

                if ($user && Hash::check($request->password, $user->password)) {
                    return $user;
                }
            } else {
                // Business login - check business_users table
                $user = \App\Models\BusinessUser::where('email', $request->email)
                    ->orWhere('phone', $request->email)
                    ->first();

                if ($user && Hash::check($request->password, $user->password)) {
                    return $user;
                }
            }

            return null;
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(function () {
            // Determine if this is admin or business login based on URL
            $isAdmin = request()->is('adminos/*') || request()->is('adminos');

            return $isAdmin
                ? Inertia::render('Admin/Login')
                : Inertia::render('Business/Login');
        });
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
