<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Enums\AppointmentStatus;
use App\Http\Resources\Appointments\FamilyAppointmentResource;
use App\Models\Appointment;
use App\Models\AppointmentTransportInvitation;
use App\Models\Family;
use App\Support\InertiaPagination;
use Illuminate\Http\Request;

final class FamilyAppointmentsScreenService
{
    public function buildProps(Request $request, Family $family, ?int $activePatientId): array
    {
        $view = $this->normalizedView($request);
        $patientId = $activePatientId;

        if ($patientId === null) {
            return [
                'appointments' => InertiaPagination::empty(),
                'appointment_view' => $view,
                'appointment_tab_totals' => [
                    'planned' => 0,
                    'completed' => 0,
                ],
            ];
        }

        $baseQuery = Appointment::query()->where('patient_id', $patientId);
        $plannedTotal = (clone $baseQuery)->where('status', AppointmentStatus::SCHEDULED)->count('*');
        $completedTotal = (clone $baseQuery)->whereIn('status', [
            AppointmentStatus::DONE,
            AppointmentStatus::CANCELLED,
        ], 'and', false)->count('*');

        $query = Appointment::query()
            ->where('patient_id', $patientId)
            ->with([
                'transportFamily.user',
                'transportInvitations' => fn ($q) => $q->where('family_id', $family->id),
            ]);

        if ($view === 'planned') {
            $query
                ->where('status', AppointmentStatus::SCHEDULED)
                ->orderBy('starts_at');
        }

        if ($view === 'completed') {
            $query
                ->whereIn('status', [
                    AppointmentStatus::DONE,
                    AppointmentStatus::CANCELLED,
                ], 'and', false)
                ->orderByDesc('starts_at');
        }

        $deepLinkAppointmentId = $this->resolveDeepLinkAppointmentId(
            $request,
            $patientId,
            $family->id,
            $view,
        );

        $page = $this->resolvePage(
            $request,
            $patientId,
            $view,
            $deepLinkAppointmentId,
        );

        $paginator = $query
            ->paginate(InertiaPagination::PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();

        return [
            'appointments' => InertiaPagination::payload(
                $paginator,
                FamilyAppointmentResource::collectForInertia($paginator->items(), $family),
            ),
            'appointment_view' => $view,
            'appointment_tab_totals' => [
                'planned' => $plannedTotal,
                'completed' => $completedTotal,
            ],
        ];
    }

    private function normalizedView(Request $request): string
    {
        $raw = $request->query('view', 'planned');

        if (! is_string($raw)) {
            return 'planned';
        }

        if (! in_array($raw, ['planned', 'completed'], true)) {
            return 'planned';
        }

        return $raw;
    }

    private function resolveDeepLinkAppointmentId(
        Request $request,
        int $patientId,
        int $familyId,
        string $view,
    ): ?int {
        if ($view !== 'planned') {
            return null;
        }

        $raw = $request->query('appointment');

        if (! is_string($raw) || ! ctype_digit($raw)) {
            return null;
        }

        $appointmentId = (int) $raw;

        $appointment = Appointment::query()
            ->whereKey($appointmentId)
            ->where('patient_id', $patientId)
            ->where('status', AppointmentStatus::SCHEDULED)
            ->first();

        if ($appointment === null) {
            return null;
        }

        if ((int) $appointment->family_id === $familyId) {
            return $appointmentId;
        }

        if ($appointment->family_id !== null || ! $appointment->needs_transport) {
            return null;
        }

        $hasPendingInvitation = AppointmentTransportInvitation::query()
            ->pending()
            ->where('appointment_id', $appointmentId)
            ->where('family_id', $familyId)
            ->exists();

        if (! $hasPendingInvitation) {
            return null;
        }

        return $appointmentId;
    }

    private function resolvePage(
        Request $request,
        int $patientId,
        string $view,
        ?int $deepLinkAppointmentId,
    ): int {
        if ($deepLinkAppointmentId === null || $view !== 'planned') {
            return $this->normalizedPage($request);
        }

        $appointment = Appointment::query()->find($deepLinkAppointmentId);

        if ($appointment === null) {
            return $this->normalizedPage($request);
        }

        $earlierCount = Appointment::query()
            ->where('patient_id', $patientId)
            ->where('status', AppointmentStatus::SCHEDULED)
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
