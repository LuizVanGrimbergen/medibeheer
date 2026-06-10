<?php

declare(strict_types=1);

namespace App\Services\Appointments\PushReminders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Support\Appointments\AppointmentClock;
use App\Support\Appointments\PushReminders\AppointmentDoctorTypeLabel;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class CandidatesQuery
{
    public function eachTwoDaysBefore(callable $onCandidate, ?CarbonInterface $now = null): void
    {
        $now = AppointmentClock::now($now);
        $targetDate = AppointmentClock::today($now)->addDays(2);

        $this->scheduledAppointmentsQuery()
            ->whereDate('starts_at', '=', $targetDate->toDateString(), 'and')
            ->with(['patient.user', 'patient.families.user'])
            ->chunkById(100, function (EloquentCollection $appointments) use ($onCandidate): void {
                foreach ($appointments as $appointment) {
                    $patient = $appointment->patient;

                    if ($patient === null) {
                        continue;
                    }

                    $onCandidate($patient, $appointment, $this->payloadFor($appointment));
                }
            });
    }

    public function eachTwoHoursBefore(callable $onCandidate, ?CarbonInterface $now = null): void
    {
        $now = AppointmentClock::now($now);
        $targetStartsAt = $now->addHours(2)->startOfMinute();
        $targetEnd = $targetStartsAt->addMinute();

        $this->scheduledAppointmentsQuery()
            ->where('starts_at', '>=', $targetStartsAt->format('Y-m-d H:i:s'), 'and')
            ->where('starts_at', '<', $targetEnd->format('Y-m-d H:i:s'), 'and')
            ->with(['patient.user', 'patient.families.user'])
            ->chunkById(100, function (EloquentCollection $appointments) use ($onCandidate, $targetStartsAt): void {
                foreach ($appointments as $appointment) {
                    if (AppointmentClock::startsAtLocal($appointment)->format('Y-m-d H:i') !== $targetStartsAt->format('Y-m-d H:i')) {
                        continue;
                    }

                    $patient = $appointment->patient;

                    if ($patient === null) {
                        continue;
                    }

                    $onCandidate($patient, $appointment, $this->payloadFor($appointment));
                }
            });
    }

    /**
     * @return array{
     *     appointment_id: int,
     *     provider_name: string,
     *     doctor_type: string,
     *     doctor_type_label: string,
     *     starts_at_date: string,
     *     starts_at_time: string,
     * }
     */
    public function payloadFor(Appointment $appointment): array
    {
        $startsAt = AppointmentClock::startsAtLocal($appointment);

        return [
            'appointment_id' => (int) $appointment->id,
            'provider_name' => (string) ($appointment->provider_name ?? ''),
            'doctor_type' => (string) ($appointment->doctor_type?->value ?? ''),
            'doctor_type_label' => AppointmentDoctorTypeLabel::forType(
                $appointment->doctor_type ?? '',
            ),
            'starts_at_date' => $startsAt->translatedFormat('j F Y'),
            'starts_at_time' => $startsAt->format('H:i'),
        ];
    }

    private function scheduledAppointmentsQuery(): Builder
    {
        return Appointment::query()
            ->whereStatus(AppointmentStatus::SCHEDULED);
    }
}
