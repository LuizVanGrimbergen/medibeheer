<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctor\Concerns;

use App\Models\Doctor;
use App\Services\Doctor\DoctorPatientOverviewScreenService;
use App\Support\CalendarMonth;
use Illuminate\Http\Request;

trait ResolvesDoctorPatientOverview
{
    /** @return array<string, mixed>|null */
    protected function resolveDoctorPatientOverview(
        Doctor $doctor,
        Request $request,
        DoctorPatientOverviewScreenService $patientOverviewScreenService,
    ): ?array {
        $patientPublicId = $request->query('patient');

        if (! is_string($patientPublicId) || $patientPublicId === '') {
            return null;
        }

        $selectedPatient = $doctor->patients()
            ->whereHas('user', static fn ($query) => $query->where('public_id', $patientPublicId))
            ->first();

        if ($selectedPatient === null) {
            return null;
        }

        $this->authorize('view', $selectedPatient);

        return $patientOverviewScreenService->buildProps(
            $selectedPatient,
            CalendarMonth::fromRequest($request),
        );
    }
}
