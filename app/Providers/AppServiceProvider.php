<?php

namespace App\Providers;

use App\Auth\DualEloquentUserProvider;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthentication();
        $this->configureTunnelAwareUrls();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Support authenticating both admin User and BusinessUser models.
     */
    protected function configureAuthentication(): void
    {
        Auth::provider('dual_eloquent', function ($app, array $config) {
            return new DualEloquentUserProvider($app['hash'], $config['model']);
        });

        Event::listen(Login::class, function (Login $event): void {
            session([DualEloquentUserProvider::SESSION_KEY => $event->user::class]);
        });

        Event::listen(Logout::class, function (): void {
            session()->forget(DualEloquentUserProvider::SESSION_KEY);
        });
    }

    /**
     * When the app is reached through a temporary share tunnel (Cloudflare / ngrok),
     * generate redirects and links for that public host. Local requests are unchanged.
     */
    protected function configureTunnelAwareUrls(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $request = request();

        if (! $request->headers->has('X-Forwarded-Host')) {
            return;
        }

        URL::forceRootUrl($request->getSchemeAndHttpHost());

        if ($request->headers->get('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}
