<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Http\Resources\PatientAppointmentResource;
use App\Models\Patient;
use App\Support\InertiaPagination;

final class PatientAppointmentsScreenService
{
    public function buildProps(Patient $patient): array
    {
        $paginator = $patient->appointments()
            ->with('transportFamily.user')
            ->with([
                'transportInvitations' => fn ($query) => $query->select('id', 'appointment_id', 'family_id'),
            ])
            ->withExists([
                'transportInvitations as has_pending_transport_invitation' => fn ($query) => $query->pending(),
            ])
            ->where('status', AppointmentStatus::SCHEDULED)
            ->orderBy('starts_at')
            ->paginate(InertiaPagination::PER_PAGE)
            ->withQueryString();

        return [
            'appointments' => InertiaPagination::payload(
                $paginator,
                PatientAppointmentResource::collectForInertia($paginator->getCollection()),
            ),
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

