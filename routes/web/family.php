<?php

use App\Http\Controllers\Family\FamilyOverviewController;
use App\Http\Controllers\Family\FamilyUpdatesController;
use App\Http\Middleware\EnsureFamilyMember;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

Route::middleware([
    Authenticate::class,
    EnsureEmailIsVerified::class,
    EnsureFamilyMember::class,
    ThrottleRequests::using('authenticated-area'),
])
    ->prefix('family')
    ->name('family.')
    ->group(function (): void {
        Route::get('/', FamilyOverviewController::class)->name('overview');
        Route::get('updates', FamilyUpdatesController::class)->name('updates');
    });
