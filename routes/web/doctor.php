<?php

use App\Http\Controllers\Doctor\DoctorDashboardController;
use App\Http\Controllers\Doctor\DoctorPatientsController;
use App\Http\Controllers\Doctor\Invitations\AcceptIncomingDoctorInvitationController;
use App\Http\Controllers\Doctor\Patients\DestroyDoctorLinkedPatientController;
use App\Http\Middleware\EnsureDoctor;
use App\Http\Middleware\RedirectIfEmailUnverified;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

Route::middleware([
    Authenticate::class,
    RedirectIfEmailUnverified::class,
    EnsureDoctor::class,
    ThrottleRequests::using('authenticated-area'),
])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function (): void {
        Route::get('/', DoctorDashboardController::class)->name('dashboard');
        Route::get('patients', DoctorPatientsController::class)->name('patients');

        Route::delete('patients/links/{linkedPatient}', DestroyDoctorLinkedPatientController::class)
            ->middleware('throttle:patient-care-team-unlink')
            ->name('patients.links.destroy');

        Route::post('invitations/{incomingDoctorInvitation}/accept-incoming', AcceptIncomingDoctorInvitationController::class)
            ->middleware('throttle:doctor-invitation-accept')
            ->name('invitations.incoming.accept');
    });
