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

        Vite::usePreloadTagAttributes(function (string $src, string $url): array|false {
            $path = parse_url($url, PHP_URL_PATH) ?? '';

            if (str_ends_with($path, '.css')) {
                return false;
            }

            return [];
        });
    }
}
