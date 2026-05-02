<?php

use App\Http\Controllers\Patient\PatientAppointmentController;
use App\Http\Controllers\Patient\PatientDashboardController;
use App\Http\Controllers\Patient\PatientFamilyController;
use App\Http\Controllers\Patient\PatientInventoryController;
use App\Http\Controllers\Patient\PatientMedicationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'patient'])
    ->prefix('patient')
    ->name('patient.')
    ->group(function (): void {
        Route::get('/', PatientDashboardController::class)->name('dashboard');
        Route::get('medications', PatientMedicationController::class)->name('medications');
        Route::get('inventory', PatientInventoryController::class)->name('inventory');
        Route::get('appointments', PatientAppointmentController::class)->name('appointments');
        Route::get('family', PatientFamilyController::class)->name('family');
    });
