<?php

namespace App\Providers;

use App\Models\Patient;
use App\Models\User;
use App\Policies\PatientPolicy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**************************************/
    /*            Registration */
    /**************************************/

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Intentionally left empty.
    }

    /**************************************/
    /*            Bootstrapping */
    /**************************************/

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('local') || $this->app->runningUnitTests()) {
            Model::shouldBeStrict();
        }

        if ($this->app->environment('production')) {
            DB::prohibitDestructiveCommands();
        }

        RateLimiter::for('confirm-password', $this->passwordActionRateLimiter());
        RateLimiter::for('update-password', $this->passwordActionRateLimiter());

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Patient::class, PatientPolicy::class);

        RedirectIfAuthenticated::redirectUsing(function (Request $request): string {
            $user = $request->user();

            if ($user instanceof User) {
                return $user->defaultAuthenticatedHomeUrl();
            }

            return route('dashboard', absolute: false);
        });

        Vite::prefetch(concurrency: 3);
    }

    private function passwordActionRateLimiter(): \Closure
    {
        return function (Request $request): Limit {
            $key = $request->user()?->id ?? $request->ip();

            return Limit::perMinute(5)->by($key);
        };
    }
}
