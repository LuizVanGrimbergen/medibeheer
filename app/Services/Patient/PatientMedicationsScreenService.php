<?php

declare(strict_types=1);

namespace App\Services\Patient;

use App\Http\Resources\Medications\MedicationResource;
use App\Models\Patient;
use App\Support\InertiaPagination;

final class PatientMedicationsScreenService
{
    public function buildProps(Patient $patient): array
    {
        return $this->paginatedMedicationsForScreen($patient, ['schedules.weekdays', 'stocks']);
    }

    public function buildInventoryProps(Patient $patient): array
    {
        return $this->paginatedMedicationsForScreen($patient, ['stocks', 'schedules.weekdays']);
    }

    private function paginatedMedicationsForScreen(Patient $patient, array $with): array
    {
        $paginator = $patient->medications()
            ->with($with)
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
