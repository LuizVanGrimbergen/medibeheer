<?php

use App\Http\Controllers\Family\AcceptFamilyInvitationController;
use App\Http\Controllers\Family\FamilyOverviewController;
use App\Http\Controllers\Family\FamilyUpdatesController;
use App\Http\Controllers\Family\SwitchActivePatientController;
use App\Http\Middleware\EnsureFamilyMember;
use App\Http\Middleware\RedirectIfEmailUnverified;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Routing\Middleware\ThrottleRequests;
/* invitation routes */
use Illuminate\Support\Facades\Route;

Route::middleware([
    Authenticate::class,
    RedirectIfEmailUnverified::class,
    EnsureFamilyMember::class,
    ThrottleRequests::using('authenticated-area'),
])
    ->prefix('family')
    ->name('family.')
    ->group(function (): void {
        Route::get('/', FamilyOverviewController::class)->name('overview');
        Route::get('updates', FamilyUpdatesController::class)->name('updates');

        /* Family Invitations */
        Route::post('invitations/accept', AcceptFamilyInvitationController::class)
            ->middleware('throttle:family-invitation-accept')
            ->name('invitations.accept');

        /* Active Patient */
        Route::post('patients/{patient}/switch', SwitchActivePatientController::class)
            ->name('patients.switch');
    });
