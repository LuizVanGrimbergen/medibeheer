<?php

// navigation controllers
use App\Http\Controllers\Patient\DestroyPatientFamilyInvitationController;
use App\Http\Controllers\Patient\PatientAppointmentController;
use App\Http\Controllers\Patient\PatientDashboardController;
use App\Http\Controllers\Patient\PatientFamilyController;
use App\Http\Controllers\Patient\PatientInventoryController;
use App\Http\Controllers\Patient\PatientMedicationController;
use App\Http\Controllers\Patient\StorePatientFamilyInvitationController;
use App\Http\Middleware\EnsurePatient;
use App\Http\Middleware\RedirectIfEmailUnverified;
use Illuminate\Auth\Middleware\Authenticate;
/* invitation routes */
use Illuminate\Routing\Middleware\ThrottleRequests;
// appointments controller
use Illuminate\Support\Facades\Route;

Route::middleware([
    Authenticate::class,
    RedirectIfEmailUnverified::class,
    EnsurePatient::class,
    ThrottleRequests::using('authenticated-area'),
])
    ->prefix('patient')
    ->name('patient.')

    ->group(function (): void {

        /* navigation routes */
        Route::get('/', PatientDashboardController::class)->name('dashboard');
        Route::get('medications', PatientMedicationController::class)->name('medications');
        Route::get('inventory', PatientInventoryController::class)->name('inventory');
        Route::get('family', PatientFamilyController::class)->name('family');

        /* Family Invitations routes */
        Route::post('family/invitations', StorePatientFamilyInvitationController::class)
            ->middleware('throttle:family-invitation-send')
            ->name('family.invitations.store');

        Route::delete('family/invitations/{familyInvitation}', DestroyPatientFamilyInvitationController::class)
            ->middleware('throttle:family-invitation-revoke')
            ->name('family.invitations.destroy');

        /* Appointments routes */
        Route::resource('appointments', PatientAppointmentController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names([
            'index' => 'appointments',
            'store' => 'appointments.store',
            'update' => 'appointments.update',
            'destroy' => 'appointments.destroy',
        ]);

    });
