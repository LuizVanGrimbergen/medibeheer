<?php

use App\Http\Controllers\Family\FamilyOverviewController;
use App\Http\Controllers\Family\FamilyUpdatesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'family_member'])
    ->prefix('family')
    ->name('family.')
    ->group(function (): void {
        Route::get('/', FamilyOverviewController::class)->name('overview');
        Route::get('updates', FamilyUpdatesController::class)->name('updates');
    });
