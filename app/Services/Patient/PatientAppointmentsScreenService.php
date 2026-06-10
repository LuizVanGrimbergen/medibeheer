<?php

declare(strict_types=1);

namespace App\Services\Patient;

use App\Enums\AppointmentStatus;
use App\Http\Resources\Appointments\PatientAppointmentResource;
use App\Models\Appointment;
use App\Models\Patient;
use App\Support\InertiaPagination;
use Illuminate\Http\Request;

final class PatientAppointmentsScreenService
{
    /** @return array{planned: int, completed: int} */
    public function tabTotalsFor(Patient $patient): array
    {
        $plannedTotal = $patient->appointments()
            ->whereStatus(AppointmentStatus::SCHEDULED)
            ->count();
        $completedTotal = $patient->appointments()
            ->whereStatusIn([
                AppointmentStatus::DONE,
                AppointmentStatus::CANCELLED,
            ])
            ->count();

        return [
            'planned' => $plannedTotal,
            'completed' => $completedTotal,
        ];
    }

    /** @return array{data: list<array<string, mixed>>, meta: array<string, mixed>} */
    public function paginatedAppointmentsFor(Patient $patient, Request $request): array
    {
        $deepLinkAppointmentId = $this->resolveDeepLinkAppointmentId($request, $patient);

        $page = $this->resolvePage($request, $patient, $deepLinkAppointmentId);

        $query = $patient->appointments()
            ->with('transportFamily.user')
            ->with([
                'transportInvitations' => fn ($q) => $q->select(
                    'id',
                    'appointment_id',
                    'family_id',
                    'invited_at',
                    'accepted_at',
                    'declined_at',
                    'cancelled_at',
                ),
            ])
            ->withExists([
                'transportInvitations as has_pending_transport_invitation' => fn ($q) => $q->pending(),
            ])
            ->whereStatus(AppointmentStatus::SCHEDULED)
            ->orderBy('starts_at');

        $paginator = $query
            ->paginate(InertiaPagination::PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();

        return InertiaPagination::payload(
            $paginator,
            PatientAppointmentResource::collectForInertia($paginator->getCollection()),
        );
    }

    /** @return list<array{id: int, name: string}> */
    public function linkedFamiliesFor(Patient $patient): array
    {
        return $patient->families()
            ->with('user')
            ->orderBy('id')
            ->get()
            ->map(fn ($family) => [
                'id' => (int) $family->id,
                'name' => (string) ($family->user?->name ?? 'Familielid'),
            ])
            ->values()
            ->all();
    }

    private function resolveDeepLinkAppointmentId(Request $request, Patient $patient): ?int
    {
        $raw = $request->query('appointment');

        if (! is_string($raw) || ! ctype_digit($raw)) {
            return null;
        }

        $appointmentId = (int) $raw;

        $exists = Appointment::query()
            ->whereKey($appointmentId)
            ->where('patient_id', $patient->id)
            ->whereStatus(AppointmentStatus::SCHEDULED)
            ->exists();

        return $exists ? $appointmentId : null;
    }

    private function resolvePage(
        Request $request,
        Patient $patient,
        ?int $deepLinkAppointmentId,
    ): int {
        if ($deepLinkAppointmentId === null) {
            return $this->normalizedPage($request);
        }

        $appointment = Appointment::query()->find($deepLinkAppointmentId);

        if ($appointment === null) {
            return $this->normalizedPage($request);
        }

        $earlierCount = $patient->appointments()
            ->whereStatus(AppointmentStatus::SCHEDULED)
            ->where('starts_at', '<', $appointment->starts_at)
            ->count('*');

        return intdiv($earlierCount, InertiaPagination::PER_PAGE) + 1;
    }

    private function normalizedPage(Request $request): int
    {
        $raw = $request->query('page', 1);

        if (is_string($raw) && ctype_digit($raw)) {
            $page = (int) $raw;

            if ($page >= 1) {
                return $page;
            }
        }

        if (is_int($raw) && $raw >= 1) {
            return $raw;
        }

        return 1;
    }
}
