<?php

// navigation controllers
use App\Http\Controllers\Patient\Appointments\PatientAppointmentController;
use App\Http\Controllers\Patient\Appointments\ShowPatientAppointmentCancelPageController;
use App\Http\Controllers\Patient\Appointments\ShowPatientAppointmentCompletePageController;
use App\Http\Controllers\Patient\Appointments\ShowPatientAppointmentScheduleNextPageController;
use App\Http\Controllers\Patient\DailyCheckins\PatientDailyCheckinController;
use App\Http\Controllers\Patient\Dashboard\PatientDashboardController;
use App\Http\Controllers\Patient\Doctors\DestroyPatientDoctorInvitationController;
use App\Http\Controllers\Patient\Doctors\DestroyPatientLinkedDoctorController;
use App\Http\Controllers\Patient\Doctors\PatientDoctorsController;
use App\Http\Controllers\Patient\Doctors\StorePatientDoctorInvitationController;
use App\Http\Controllers\Patient\Family\DestroyPatientFamilyInvitationController;
use App\Http\Controllers\Patient\Family\DestroyPatientLinkedFamilyMemberController;
use App\Http\Controllers\Patient\Family\PatientFamilyController;
use App\Http\Controllers\Patient\Family\StorePatientFamilyInvitationController;
use App\Http\Controllers\Patient\Inventory\PatientInventoryController;
use App\Http\Controllers\Patient\MedicationPlans\AcceptPatientMedicationPlanProposalController;
use App\Http\Controllers\Patient\MedicationPlans\DeclinePatientMedicationPlanProposalController;
use App\Http\Controllers\Patient\MedicationPlans\ShowPatientMedicationPlanProposalReviewController;
use App\Http\Controllers\Patient\Medications\AckPatientPushMedicationMarkController;
use App\Http\Controllers\Patient\Medications\MarkPatientMedicationIntakeFromPushController;
use App\Http\Controllers\Patient\Medications\PatientMedicationController;
use App\Http\Controllers\Patient\Medications\PatientMedicationIntakeController;
use App\Http\Controllers\Patient\Medications\PatientMedicationScheduleController;
use App\Http\Controllers\Patient\Medications\PatientMedicationStockController;
use App\Http\Controllers\Patient\Medications\ShowPatientPushMedicationMarkSuccessController;
use App\Http\Controllers\Patient\PushSubscriptions\DestroyPatientPushSubscriptionController;
use App\Http\Controllers\Patient\PushSubscriptions\StorePatientPushSubscriptionController;
use App\Http\Middleware\EnsurePatient;
use App\Http\Middleware\RedirectIfEmailUnverified;
use Illuminate\Auth\Middleware\Authenticate;
/* invitation routes */
use Illuminate\Routing\Middleware\ThrottleRequests;
// appointments controller
use Illuminate\Support\Facades\Route;

Route::match(
    ['get', 'post'],
    'patient/medication-intakes/mark-from-push/{medicationSchedule}',
    MarkPatientMedicationIntakeFromPushController::class,
)
    ->middleware(['signed', 'throttle:medication-intake-from-push'])
    ->name('patient.medication-intakes.mark-from-push');

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
        Route::resource('medications', PatientMedicationController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->names([
                'index' => 'medications',
                'store' => 'medications.store',
                'update' => 'medications.update',
                'destroy' => 'medications.destroy',
            ]);

        Route::resource('medications.schedules', PatientMedicationScheduleController::class)
            ->only(['store', 'update', 'destroy'])
            ->scoped();

        Route::resource('medications.stocks', PatientMedicationStockController::class)
            ->only(['store', 'update', 'destroy'])
            ->scoped();

        /* Medication plan proposals */
        Route::get('medication-plans/{medication_plan_proposal}/review', ShowPatientMedicationPlanProposalReviewController::class)
            ->name('medication-plans.review');
        Route::post('medication-plans/{medication_plan_proposal}/accept', AcceptPatientMedicationPlanProposalController::class)
            ->middleware('throttle:medication-plan-proposal-redeem')
            ->name('medication-plans.accept');
        Route::post('medication-plans/{medication_plan_proposal}/decline', DeclinePatientMedicationPlanProposalController::class)
            ->middleware('throttle:medication-plan-proposal-redeem')
            ->name('medication-plans.decline');

        Route::get('inventory', PatientInventoryController::class)->name('inventory');
        Route::get('family', PatientFamilyController::class)->name('family');
        Route::get('doctors', PatientDoctorsController::class)->name('doctors');

        /* Doctor Invitations routes */
        Route::post('doctors/invitations', StorePatientDoctorInvitationController::class)
            ->middleware('throttle:doctor-invitation-send')
            ->name('doctors.invitations.store');

        Route::delete('doctors/invitations/{doctorInvitation}', DestroyPatientDoctorInvitationController::class)
            ->middleware('throttle:doctor-invitation-revoke')
            ->name('doctors.invitations.destroy');

        Route::delete('doctors/links/{linkedDoctor}', DestroyPatientLinkedDoctorController::class)
            ->middleware('throttle:patient-care-team-unlink')
            ->name('doctors.links.destroy');

        /* Family Invitations routes */
        Route::post('family/invitations', StorePatientFamilyInvitationController::class)
            ->middleware('throttle:family-invitation-send')
            ->name('family.invitations.store');

        Route::delete('family/invitations/{familyInvitation}', DestroyPatientFamilyInvitationController::class)
            ->middleware('throttle:family-invitation-revoke')
            ->name('family.invitations.destroy');

        Route::delete('family/members/{linkedFamilyMember}', DestroyPatientLinkedFamilyMemberController::class)
            ->middleware('throttle:patient-care-team-unlink')
            ->name('family.members.destroy');

        /* Appointments routes */
        Route::get('appointments/{appointment}/complete', ShowPatientAppointmentCompletePageController::class)
            ->name('appointments.complete');
        Route::get('appointments/{appointment}/cancel', ShowPatientAppointmentCancelPageController::class)
            ->name('appointments.cancel');

        Route::get('appointments/schedule-next', ShowPatientAppointmentScheduleNextPageController::class)
            ->name('appointments.schedule-next');

        Route::resource('appointments', PatientAppointmentController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->names([
                'index' => 'appointments',
                'store' => 'appointments.store',
                'update' => 'appointments.update',
                'destroy' => 'appointments.destroy',
            ]);

        /* Daily check-ins routes */
        Route::post('daily-checkins', [PatientDailyCheckinController::class, 'store'])
            ->name('daily-checkins.store');

        /* Medication Intakes routes */
        Route::post('medication-intakes', [PatientMedicationIntakeController::class, 'store'])
            ->name('medication-intakes.store');

        /* Medication Push routes */
        Route::get('medication-push-mark/success', ShowPatientPushMedicationMarkSuccessController::class)
            ->name('medication-push-mark.success');

        Route::post('medication-push-mark/ack', AckPatientPushMedicationMarkController::class)
            ->name('medication-push-mark.ack');

        Route::post('push-subscriptions', StorePatientPushSubscriptionController::class)
            ->name('push-subscriptions.store');

        Route::delete('push-subscriptions', DestroyPatientPushSubscriptionController::class)
            ->name('push-subscriptions.destroy');

    });
