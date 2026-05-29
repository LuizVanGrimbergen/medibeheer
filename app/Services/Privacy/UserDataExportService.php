<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Models\Appointment;
use App\Models\DailyCheckin;
use App\Models\Doctor;
use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Models\Medication;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\MedicationStock;
use App\Models\Patient;
use App\Models\User;
use App\Models\UserConsent;
use Illuminate\Support\Carbon;

final class UserDataExportService
{
    public function exportForUser(User $user): array
    {
        $user->loadMissing(['patient', 'family', 'doctor']);

        return [
            'exported_at' => Carbon::now()->toIso8601String(),
            'policy_version' => config('privacy.policy_version'),
            'account' => $this->accountPayload($user),
            'consents' => $this->consentsPayload($user),
            'patient' => $this->patientPayload($user->patient),
            'family' => $this->familyPayload($user->family),
            'doctor' => $this->doctorPayload($user->doctor),
        ];
    }

    private function accountPayload(User $user): array
    {
        return [
            'public_id' => $user->public_id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }

    private function consentsPayload(User $user): array
    {
        return UserConsent::query()
            ->where('user_id', $user->getKey())
            ->orderBy('accepted_at', 'desc')
            ->get()
            ->map(fn (UserConsent $consent): array => [
                'type' => $consent->type->value,
                'policy_version' => $consent->policy_version,
                'accepted_at' => $consent->accepted_at->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    private function patientPayload(?Patient $patient): ?array
    {
        if ($patient === null) {
            return null;
        }

        $patient->load([
            'medications' => fn ($query) => $query->withTrashed()->with([
                'schedules' => fn ($scheduleQuery) => $scheduleQuery->withTrashed()->with('weekdays'),
                'stocks' => fn ($stockQuery) => $stockQuery->withTrashed(),
            ]),
            'medicationIntakes',
            'appointments',
            'dailyCheckins.selectedSymptoms',
            'familyInvitations',
            'families',
            'doctors.user',
        ]);

        return [
            'streak_count' => $patient->streak_count,
            'created_at' => $patient->created_at?->toIso8601String(),
            'medications' => $patient->medications
                ->map(fn (Medication $medication): array => $this->medicationPayload($medication))
                ->values()
                ->all(),
            'medication_intakes' => $patient->medicationIntakes
                ->map(fn (MedicationIntake $intake): array => $this->medicationIntakePayload($intake))
                ->values()
                ->all(),
            'appointments' => $patient->appointments
                ->map(fn (Appointment $appointment): array => $this->appointmentPayload($appointment))
                ->values()
                ->all(),
            'daily_checkins' => $patient->dailyCheckins
                ->map(fn (DailyCheckin $checkin): array => $this->dailyCheckinPayload($checkin))
                ->values()
                ->all(),
            'family_invitations' => $patient->familyInvitations
                ->map(fn (FamilyInvitation $invitation): array => [
                    'id' => $invitation->id,
                    'invited_email' => $invitation->invited_email,
                    'expires_at' => $invitation->expires_at?->toIso8601String(),
                    'accepted_at' => $invitation->accepted_at?->toIso8601String(),
                    'created_at' => $invitation->created_at?->toIso8601String(),
                ])
                ->values()
                ->all(),
            'linked_family_ids' => $patient->families->pluck('id')->values()->all(),
            'linked_doctor_public_ids' => $patient->doctors
                ->map(fn (Doctor $doctor): ?string => $doctor->user?->public_id)
                ->filter()
                ->values()
                ->all(),
        ];
    }

    private function medicationPayload(Medication $medication): array
    {
        return [
            'id' => $medication->id,
            'name' => $medication->name,
            'dose' => $medication->dose,
            'dose_unit' => $medication->dose_unit?->value,
            'type_medication' => $medication->type_medication?->value,
            'strength' => $medication->strength,
            'note' => $medication->note,
            'deleted_at' => $medication->deleted_at?->toIso8601String(),
            'created_at' => $medication->created_at?->toIso8601String(),
            'schedules' => $medication->schedules
                ->map(fn (MedicationSchedule $schedule): array => [
                    'id' => $schedule->id,
                    'meal_timing' => $schedule->meal_timing?->value,
                    'intake_frequency' => $schedule->intake_frequency,
                    'times_per_day' => $schedule->times_per_day,
                    'dose_quantity' => $schedule->dose_quantity,
                    'dose_time' => $schedule->dose_time,
                    'snooze_time' => $schedule->snooze_time,
                    'start_date' => $schedule->start_date?->toDateString(),
                    'end_date' => $schedule->end_date?->toDateString(),
                    'weekdays' => $schedule->intakeWeekdays,
                    'deleted_at' => $schedule->deleted_at?->toIso8601String(),
                ])
                ->values()
                ->all(),
            'stocks' => $medication->stocks
                ->map(fn (MedicationStock $stock): array => [
                    'id' => $stock->id,
                    'current_stock' => $stock->current_stock,
                    'deleted_at' => $stock->deleted_at?->toIso8601String(),
                    'updated_at' => $stock->updated_at?->toIso8601String(),
                ])
                ->values()
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function medicationIntakePayload(MedicationIntake $intake): array
    {
        return [
            'id' => $intake->id,
            'medication_id' => $intake->medication_id,
            'medication_schedule_id' => $intake->medication_schedule_id,
            'intake_date' => $intake->intake_date->toDateString(),
            'taken_at' => $intake->taken_at?->toIso8601String(),
            'dose_time' => $intake->dose_time,
            'created_at' => $intake->created_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function appointmentPayload(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'doctor_type' => $appointment->doctor_type?->value,
            'provider_name' => $appointment->provider_name,
            'street' => $appointment->street,
            'house_number' => $appointment->house_number,
            'postal_code' => $appointment->postal_code,
            'city' => $appointment->city,
            'starts_at' => $appointment->starts_at?->toIso8601String(),
            'needs_transport' => $appointment->needs_transport,
            'notes' => $appointment->notes,
            'doctor_visit_summary' => $appointment->doctor_visit_summary,
            'cancellation_reason' => $appointment->cancellation_reason,
            'status' => $appointment->status?->value,
            'created_at' => $appointment->created_at?->toIso8601String(),
        ];
    }

    private function dailyCheckinPayload(DailyCheckin $checkin): array
    {
        return [
            'id' => $checkin->id,
            'checkin_date' => $checkin->checkin_date->toDateString(),
            'mood_score' => $checkin->mood_score->value,
            'symptoms' => $checkin->symptomValues(),
            'note' => $checkin->note,
            'created_at' => $checkin->created_at?->toIso8601String(),
        ];
    }

    private function familyPayload(?Family $family): ?array
    {
        if ($family === null) {
            return null;
        }

        $family->load('patients.user');

        return [
            'linked_patient_public_ids' => $family->patients
                ->map(fn (Patient $patient): ?string => $patient->user?->public_id)
                ->filter()
                ->values()
                ->all(),
            'created_at' => $family->created_at?->toIso8601String(),
        ];
    }

    private function doctorPayload(?Doctor $doctor): ?array
    {
        if ($doctor === null) {
            return null;
        }

        $doctor->load('patients.user');

        return [
            'linked_patient_public_ids' => $doctor->patients
                ->map(fn (Patient $patient): ?string => $patient->user?->public_id)
                ->filter()
                ->values()
                ->all(),
            'created_at' => $doctor->created_at?->toIso8601String(),
        ];
    }
}
