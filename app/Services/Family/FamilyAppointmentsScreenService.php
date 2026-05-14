<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Enums\AppointmentStatus;
use App\Http\Resources\Appointments\FamilyAppointmentResource;
use App\Models\Appointment;
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

        $paginator = $query->paginate(InertiaPagination::PER_PAGE)->withQueryString();

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
}
