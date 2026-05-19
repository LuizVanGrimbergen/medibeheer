<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\ExportUserDataController;
use App\Http\Middleware\RedirectIfEmailUnverified;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

require __DIR__.'/web/patient.php';
require __DIR__.'/web/family.php';
require __DIR__.'/web/doctor.php';

Route::middleware([Authenticate::class, RedirectIfEmailUnverified::class])->group(function () {
    Route::get('settings', [ProfileController::class, 'edit'])->name('settings.edit');
    Route::patch('settings', [ProfileController::class, 'update'])->name('settings.update');
    Route::get('settings/export', ExportUserDataController::class)
        ->middleware(['throttle:data-export'])
        ->name('settings.export');
    Route::delete('settings', [ProfileController::class, 'destroy'])
        ->middleware(['throttle:account-delete'])
        ->name('settings.destroy');
});

require __DIR__.'/auth.php';
