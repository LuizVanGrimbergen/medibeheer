<?php

declare(strict_types=1);

namespace App\Services\Patient;

use App\Http\Resources\Medications\MedicationResource;
use App\Models\Patient;
use App\Support\InertiaPagination;

final class PatientMedicationsScreenService
{
    private const array MEDICATION_LIST_WITH = ['schedules.weekdays', 'stocks'];

    public function buildProps(Patient $patient): array
    {
        return [
            'active_medications' => $this->paginateActiveMedications($patient, self::MEDICATION_LIST_WITH),
        ];
    }

    public function buildInventoryProps(Patient $patient): array
    {
        return [
            'medications' => $this->paginateActiveMedications($patient, self::MEDICATION_LIST_WITH),
        ];
    }

    public function buildFamilyMedicationsProps(Patient $patient, int $page = 1): array
    {
        return [
            'medications' => $this->paginateMedicationRegister($patient, self::MEDICATION_LIST_WITH, $page),
        ];
    }

    private function paginateActiveMedications(Patient $patient, array $with): array
    {
        $paginator = $patient->medications()
            ->activeOnMedicationList()
            ->with($with)
            ->orderByDesc('id')
            ->paginate(InertiaPagination::PER_PAGE)
            ->withQueryString();

        return InertiaPagination::payload(
            $paginator,
            MedicationResource::collectForInertia($paginator->getCollection()),
        );
    }

    /** @return list<string> */
    public function activeMedicationNamesFor(Patient $patient): array
    {
        return $patient->medications()
            ->activeOnMedicationList()
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /** @param list<string> $with */
    private function paginateMedicationRegister(Patient $patient, array $with, int $page = 1): array
    {
        $paginator = $patient->medications()
            ->withTrashed()
            ->with($with)
            ->orderByDesc('id')
            ->paginate(InertiaPagination::PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();

        return InertiaPagination::payload(
            $paginator,
            MedicationResource::collectForInertia($paginator->getCollection()),
        );
    }
}
