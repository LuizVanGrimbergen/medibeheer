<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Family;
use App\Models\Patient;
use Carbon\CarbonImmutable;

final class FamilyAcceptedTransportAppointmentsOverviewService
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

        return Appointment::query()
            ->whereIn('patient_id', $patientIds, 'and', false)
            ->where('family_id', $family->id)
            ->where('needs_transport', true)
            ->whereStatus(AppointmentStatus::SCHEDULED)
            ->where('starts_at', '>=', $now)
            ->with(['patient.user'])
            ->orderBy('starts_at')
            ->limit(self::MAX_APPOINTMENTS)
            ->get()
            ->map(fn (Appointment $appointment): array => $this->appointmentPayload($appointment))
            ->all();
    }

    private function appointmentPayload(Appointment $appointment): array
    {
        $patient = $appointment->patient;
        assert($patient instanceof Patient);

        $patientName = $patient->user?->name ?? 'Patient';

        return [
            'id' => (int) $appointment->id,
            'patient_id' => (int) $patient->id,
            'patient_name' => (string) $patientName,
            'switch_url' => route('family.patients.switch', $patient, absolute: false),
            'appointments_url' => route('family.appointments', [
                'view' => 'planned',
                'appointment' => $appointment->id,
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
