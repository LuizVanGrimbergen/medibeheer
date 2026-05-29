<?php

use App\Http\Controllers\Family\Dashboard\FamilyAppointmentsController;
use App\Http\Controllers\Family\Dashboard\FamilyLinkController;
use App\Http\Controllers\Family\Dashboard\FamilyMedicationsController;
use App\Http\Controllers\Family\Dashboard\FamilyMedicationStockController;
use App\Http\Controllers\Family\Dashboard\FamilyOverviewController;
use App\Http\Controllers\Family\Dashboard\FamilyUpdatesController;
use App\Http\Controllers\Family\Dashboard\FamilyWellbeingController;
use App\Http\Controllers\Family\Dashboard\SwitchActivePatientController;
use App\Http\Controllers\Family\Invitations\AcceptAppointmentTransportInvitationController;
use App\Http\Controllers\Family\Invitations\AcceptIncomingFamilyInvitationController;
use App\Http\Controllers\Family\Invitations\DeclineAppointmentTransportInvitationController;
use App\Http\Controllers\Family\MedicationPlans\DuplicateFamilyMedicationPlanProposalController;
use App\Http\Controllers\Family\MedicationPlans\FamilyMedicationPlanProposalController;
use App\Http\Controllers\Family\MedicationPlans\PublishFamilyMedicationPlanProposalController;
use App\Http\Controllers\Family\MedicationPlans\RevokeFamilyMedicationPlanProposalController;
use App\Http\Controllers\Family\MedicationPlans\ShowPublishFamilyMedicationPlanProposalController;
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

        Route::get('koppel', FamilyLinkController::class)->name('link');

        /* Family Appointments */
        Route::get('appointments', FamilyAppointmentsController::class)->name('appointments');

        /* Family Medications */
        Route::get('medications', FamilyMedicationsController::class)->name('medications');
        Route::put('medications/{medication}/stocks/{stock}', [FamilyMedicationStockController::class, 'update'])
            ->scopeBindings()
            ->name('medications.stocks.update');

        /* Family Medication Plans */
        Route::get('medication-plans', fn () => redirect()->route('family.link'))
            ->name('medication-plans.index');
        Route::get('medication-plans/create', [FamilyMedicationPlanProposalController::class, 'create'])
            ->name('medication-plans.create');
        Route::post('medication-plans', [FamilyMedicationPlanProposalController::class, 'store'])
            ->name('medication-plans.store');
        Route::get('medication-plans/{medication_plan_proposal}/edit', [FamilyMedicationPlanProposalController::class, 'edit'])
            ->name('medication-plans.edit');
        Route::put('medication-plans/{medication_plan_proposal}', [FamilyMedicationPlanProposalController::class, 'update'])
            ->name('medication-plans.update');
        Route::get('medication-plans/{medication_plan_proposal}/items/create', [FamilyMedicationPlanProposalController::class, 'createItem'])
            ->name('medication-plans.items.create');
        Route::post('medication-plans/{medication_plan_proposal}/items', [FamilyMedicationPlanProposalController::class, 'storeItem'])
            ->name('medication-plans.items.store');
        Route::post('medication-plans/{medication_plan_proposal}/duplicate', DuplicateFamilyMedicationPlanProposalController::class)
            ->name('medication-plans.duplicate');
        Route::get('medication-plans/{medication_plan_proposal}/publish', ShowPublishFamilyMedicationPlanProposalController::class)
            ->name('medication-plans.publish');
        Route::post('medication-plans/{medication_plan_proposal}/publish', PublishFamilyMedicationPlanProposalController::class)
            ->middleware('throttle:medication-plan-proposal-publish')
            ->name('medication-plans.publish.store');
        Route::post('medication-plans/{medication_plan_proposal}/revoke', RevokeFamilyMedicationPlanProposalController::class)
            ->name('medication-plans.revoke');

        Route::get('updates', FamilyUpdatesController::class)->name('updates');

        /* Family Wellbeing */
        Route::get('wellbeing', FamilyWellbeingController::class)->name('wellbeing');

        /* Family Invitations */
        Route::post('invitations/{incomingFamilyInvitation}/accept-incoming', AcceptIncomingFamilyInvitationController::class)
            ->middleware('throttle:family-invitation-accept')
            ->name('invitations.incoming.accept');

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
