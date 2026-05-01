<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('settings', [ProfileController::class, 'edit'])->name('settings.edit');
    Route::patch('settings', [ProfileController::class, 'update'])->name('settings.update');
    Route::delete('settings', [ProfileController::class, 'destroy'])
        ->middleware(['throttle:5,1'])
        ->name('settings.destroy');
});

require __DIR__.'/auth.php';
