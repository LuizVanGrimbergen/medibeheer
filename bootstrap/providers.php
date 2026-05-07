<?php

use App\Providers\AppServiceProvider;
use App\Providers\RateLimitServiceProvider;
use App\Providers\RouteServiceProvider;

return [
    AppServiceProvider::class,
    RateLimitServiceProvider::class,
    RouteServiceProvider::class,
];
