<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Http\Resources\Appointments\PatientAppointmentResource;
use App\Models\Patient;
use App\Support\InertiaPagination;

final class PatientAppointmentsScreenService
{
    public function buildProps(Patient $patient): array
    {
        $plannedTotal = $patient->appointments()
            ->where('status', AppointmentStatus::SCHEDULED)
            ->count();
        $completedTotal = $patient->appointments()
            ->whereIn('status', [
                AppointmentStatus::DONE,
                AppointmentStatus::CANCELLED,
            ])
            ->count();

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
            ->where('status', AppointmentStatus::SCHEDULED)
            ->orderBy('starts_at');

        $paginator = $query->paginate(InertiaPagination::PER_PAGE)->withQueryString();

        return [
            'appointments' => InertiaPagination::payload(
                $paginator,
                PatientAppointmentResource::collectForInertia($paginator->getCollection()),
            ),
            'appointment_view' => 'planned',
            'appointment_tab_totals' => [
                'planned' => $plannedTotal,
                'completed' => $completedTotal,
            ],
            'linked_families' => $patient->families()
                ->with('user')
                ->orderBy('id')
                ->get()
                ->map(fn ($family) => [
                    'id' => (int) $family->id,
                    'name' => (string) ($family->user?->name ?? 'Familielid'),
                ])
                ->values()
                ->all(),
        ];
    }
}
