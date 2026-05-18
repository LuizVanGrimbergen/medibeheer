<?php

use App\Http\Controllers\Family\Dashboard\FamilyAppointmentsController;
use App\Http\Controllers\Family\Dashboard\FamilyMedicationStockController;
use App\Http\Controllers\Family\Dashboard\FamilyOverviewController;
use App\Http\Controllers\Family\Dashboard\FamilyUpdatesController;
use App\Http\Controllers\Family\Dashboard\FamilyWellbeingController;
use App\Http\Controllers\Family\Dashboard\SwitchActivePatientController;
use App\Http\Controllers\Family\Invitations\AcceptAppointmentTransportInvitationController;
use App\Http\Controllers\Family\Invitations\AcceptFamilyInvitationController;
use App\Http\Controllers\Family\Invitations\DeclineAppointmentTransportInvitationController;
use App\Http\Middleware\EnsureFamilyMember;
use App\Http\Middleware\RedirectIfEmailUnverified;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Routing\Middleware\ThrottleRequests;
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

        /* Family Appointments */
        Route::get('appointments', FamilyAppointmentsController::class)->name('appointments');

        /* Family Medication Stock */
        Route::put('medications/{medication}/stocks/{stock}', [FamilyMedicationStockController::class, 'update'])
            ->scopeBindings()
            ->name('medications.stocks.update');

        Route::get('updates', FamilyUpdatesController::class)->name('updates');

        /* Family Wellbeing */
        Route::get('wellbeing', FamilyWellbeingController::class)->name('wellbeing');

        /* Family Invitations */
        Route::post('invitations/accept', AcceptFamilyInvitationController::class)
            ->middleware('throttle:family-invitation-accept')
            ->name('invitations.accept');

        /* Appointment Transport Invitations */
        Route::post('transport-invitations/{transportInvitation}/accept', AcceptAppointmentTransportInvitationController::class)
            ->middleware('throttle:family-transport-invitation')
            ->name('transport-invitations.accept');

        Route::post('transport-invitations/{transportInvitation}/decline', DeclineAppointmentTransportInvitationController::class)
            ->middleware('throttle:family-transport-invitation')
            ->name('transport-invitations.decline');

        /* Active Patient */
        Route::post('patients/{patient}/switch', SwitchActivePatientController::class)
            ->middleware('throttle:family-active-patient-switch')
            ->name('patients.switch');
    });
