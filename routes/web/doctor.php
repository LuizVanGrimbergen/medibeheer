<?php

use App\Http\Controllers\Doctor\DoctorDashboardController;
use App\Http\Controllers\Doctor\DoctorPatientsController;
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
    });
