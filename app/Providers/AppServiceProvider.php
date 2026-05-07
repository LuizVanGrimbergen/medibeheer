<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        RedirectIfAuthenticated::redirectUsing(function (Request $request): string {
            $user = $request->user();

            if ($user instanceof User) {
                return $user->defaultAuthenticatedHomeUrl();
            }

            return route('home', absolute: false);
        });

        Vite::prefetch(concurrency: 3);
    }
}
