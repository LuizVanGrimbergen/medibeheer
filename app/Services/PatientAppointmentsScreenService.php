<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Http\Resources\PatientAppointmentResource;
use App\Models\Patient;
use App\Support\InertiaPagination;
use Illuminate\Http\Request;

final class PatientAppointmentsScreenService
{
    public function buildProps(Request $request, Patient $patient): array
    {
        $view = $this->normalizedView($request);

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
                'transportInvitations' => fn ($q) => $q->select('id', 'appointment_id', 'family_id'),
            ])
            ->withExists([
                'transportInvitations as has_pending_transport_invitation' => fn ($q) => $q->pending(),
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
                ])
                ->orderByDesc('starts_at');
        }

        $paginator = $query->paginate(InertiaPagination::PER_PAGE)->withQueryString();

        return [
            'appointments' => InertiaPagination::payload(
                $paginator,
                PatientAppointmentResource::collectForInertia($paginator->getCollection()),
            ),
            'appointment_view' => $view,
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
