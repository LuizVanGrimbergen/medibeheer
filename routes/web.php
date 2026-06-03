<?php

use App\Http\Controllers\Doctor\Invitations\DoctorInvitationEntryController;
use App\Http\Controllers\Family\Invitations\FamilyInvitationEntryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Legal\ShowCookiePolicyController;
use App\Http\Controllers\Legal\ShowPrivacyPolicyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seo\ShowRobotsTxtController;
use App\Http\Controllers\Seo\ShowSitemapController;
use App\Http\Controllers\Settings\ExportUserDataController;
use App\Http\Middleware\RedirectIfEmailUnverified;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::get('robots.txt', ShowRobotsTxtController::class)->name('seo.robots');
Route::get('sitemap.xml', ShowSitemapController::class)->name('seo.sitemap');

Route::get('/', HomeController::class)->name('home');

Route::get('privacy', ShowPrivacyPolicyController::class)->name('legal.privacy');
Route::get('cookies', ShowCookiePolicyController::class)->name('legal.cookies');

Route::get('family/invitation', FamilyInvitationEntryController::class)
    ->middleware('throttle:invitation-entry')
    ->name('family.invitation.entry');
Route::get('doctor/invitation', DoctorInvitationEntryController::class)
    ->middleware('throttle:invitation-entry')
    ->name('doctor.invitation.entry');

require __DIR__.'/web/patient.php';
require __DIR__.'/web/family.php';
require __DIR__.'/web/doctor.php';

Route::middleware([Authenticate::class, RedirectIfEmailUnverified::class])->group(function () {
    Broadcast::routes();

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
