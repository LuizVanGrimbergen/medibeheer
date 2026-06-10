<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentTransportInvitation;
use App\Models\Family;
use App\Models\Patient;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

final class FamilyPendingTransportAppointmentsOverviewService
{
    private const int MAX_APPOINTMENTS = 20;

    public function forFamily(Family $family): array
    {
        $patientIds = $family
            ->patients()
            ->pluck('patients.id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if ($patientIds === []) {
            return [];
        }

        $now = CarbonImmutable::now();

        return AppointmentTransportInvitation::query()
            ->pending()
            ->where('family_id', $family->id)
            ->whereHas('appointment', function (Builder $query) use ($patientIds, $now): void {
                $query
                    ->whereIn('patient_id', $patientIds, 'and', false)
                    ->whereNull('family_id')
                    ->where('needs_transport', true)
                    ->whereStatus(AppointmentStatus::SCHEDULED)
                    ->where('starts_at', '>=', $now);
            })
            ->with(['appointment.patient.user'])
            ->get()
            ->sortBy(fn (AppointmentTransportInvitation $invitation) => $invitation->appointment->starts_at)
            ->take(self::MAX_APPOINTMENTS)
            ->map(fn (AppointmentTransportInvitation $invitation): array => $this->invitationPayload($invitation))
            ->values()
            ->all();
    }

    private function invitationPayload(AppointmentTransportInvitation $invitation): array
    {
        $appointment = $invitation->appointment;
        assert($appointment instanceof Appointment);

        $patient = $appointment->patient;
        assert($patient instanceof Patient);

        $patientName = $patient->user?->name ?? 'Patient';

        return [
            'invitation_id' => (int) $invitation->id,
            'id' => (int) $appointment->id,
            'patient_id' => (int) $patient->id,
            'patient_name' => (string) $patientName,
            'switch_url' => route('family.patients.switch', $patient, absolute: false),
            'appointments_url' => route('family.appointments', [
                'view' => 'planned',
                'appointment' => $appointment->id,
            ], absolute: false),
            'accept_url' => route('family.transport-invitations.accept', [
                'transportInvitation' => $invitation,
            ], absolute: false),
            'decline_url' => route('family.transport-invitations.decline', [
                'transportInvitation' => $invitation,
            ], absolute: false),
            'doctor_type' => $appointment->doctor_type->value,
            'provider_name' => (string) $appointment->provider_name,
            'street' => (string) $appointment->street,
            'house_number' => (string) $appointment->house_number,
            'postal_code' => (string) $appointment->postal_code,
            'city' => (string) $appointment->city,
            'starts_at' => $appointment->starts_at->toIso8601String(),
        ];
    }
}
