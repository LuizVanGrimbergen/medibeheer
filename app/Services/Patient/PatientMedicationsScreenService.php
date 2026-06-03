<?php

declare(strict_types=1);

namespace App\Services\Patient;

use App\Http\Resources\Medications\MedicationPrescriptionResource;
use App\Http\Resources\Medications\MedicationResource;
use App\Models\Medication;
use App\Models\MedicationPrescription;
use App\Models\Patient;
use App\Support\InertiaPagination;
use Illuminate\Database\Eloquent\Builder;

final class PatientMedicationsScreenService
{
    private const array MEDICATION_LIST_WITH = ['schedules.weekdays', 'stocks', 'prescription'];

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

    public function buildPrescriptionsProps(Patient $patient): array
    {
        return [
            'prescriptions' => $this->paginatePrescriptions($patient),
            'medication_choices' => $this->activeMedicationChoicesFor($patient),
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

    private function paginatePrescriptions(Patient $patient): array
    {
        $patientId = $patient->getKey();

        $paginator = MedicationPrescription::query()
            ->where('patient_id', $patientId)
            ->whereNull('completed_at', '=', null)
            ->whereHas(
                'medication',
                fn (Builder $query) => $query->activeOnMedicationList(),
            )
            ->with(['medication'])
            ->orderByRaw('prescription_expiry_date IS NULL')
            ->orderBy('prescription_expiry_date')
            ->orderByDesc('id')
            ->paginate(InertiaPagination::PER_PAGE)
            ->withQueryString();

        return InertiaPagination::payload(
            $paginator,
            MedicationPrescriptionResource::collectForInertia($paginator->getCollection()),
        );
    }

    public function activeMedicationChoicesFor(Patient $patient): array
    {
        return $patient->medications()
            ->activeOnMedicationList()
            ->orderBy('name')
            ->get()
            ->map(fn (Medication $medication): array => [
                'id' => $medication->id,
                'name' => (string) $medication->name,
                'type_medication' => $medication->type_medication->value,
            ])
            ->values()
            ->all();
    }

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
