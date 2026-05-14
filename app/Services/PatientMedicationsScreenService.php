<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Resources\Medications\MedicationResource;
use App\Models\Patient;
use App\Support\InertiaPagination;

final class PatientMedicationsScreenService
{
    public function buildProps(Patient $patient): array
    {
        $paginator = $patient->medications()
            ->with(['schedules', 'stocks'])
            ->orderByDesc('id')
            ->paginate(InertiaPagination::PER_PAGE)
            ->withQueryString();

        return [
            'medications' => InertiaPagination::payload(
                $paginator,
                MedicationResource::collectForInertia($paginator->getCollection()),
            ),
        ];
    }
}
