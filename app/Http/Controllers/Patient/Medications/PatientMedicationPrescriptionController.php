<?php

namespace App\Http\Controllers\Patient\Medications;

use App\Enums\MedicationPrescriptionPickupStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\Prescriptions\StorePatientMedicationPrescriptionsRequest;
use App\Http\Requests\Patient\Prescriptions\UpdatePatientMedicationPrescriptionRequest;
use App\Models\Medication;
use App\Models\MedicationPrescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PatientMedicationPrescriptionController extends Controller
{
    use AuthorizesPatientProfile;

    public function store(
        StorePatientMedicationPrescriptionsRequest $request,
        Medication $medication,
    ): RedirectResponse {
        $this->authorizePatientProfile($request);

        $validated = $request->validated();

        DB::transaction(function () use ($medication, $validated): void {
            $prescriptionExpiryDates = $validated['prescription_expiry_dates'];
            $lastIndex = count($prescriptionExpiryDates) - 1;

            foreach ($prescriptionExpiryDates as $index => $prescriptionExpiryDate) {
                $medication->prescriptions()->create([
                    'patient_id' => $medication->patient_id,
                    'family_id' => $medication->family_id,
                    'prescription_expiry_date' => $prescriptionExpiryDate,
                    'is_last_in_batch' => $index === $lastIndex,
                    'pickup_status' => MedicationPrescriptionPickupStatus::PENDING,
                ]);
            }
        });

        return redirect()->route('patient.prescriptions');
    }

    public function update(
        UpdatePatientMedicationPrescriptionRequest $request,
        MedicationPrescription $medicationPrescription,
    ): RedirectResponse {
        $this->authorizePatientProfile($request);

        $validated = $request->validated();
        $updates = [];

        if (array_key_exists('completed', $validated)) {
            $updates['completed_at'] = $validated['completed']
                ? Carbon::now()
                : null;
        }

        if (array_key_exists('pickup_status', $validated)) {
            $pickupStatus = MedicationPrescriptionPickupStatus::from(
                $validated['pickup_status'],
            );

            $updates['pickup_status'] = $pickupStatus;
            $updates['completed_at'] = $pickupStatus === MedicationPrescriptionPickupStatus::PICKED_UP
                ? Carbon::now()
                : null;
        }

        $medicationPrescription->update($updates);

        return redirect()->route('patient.prescriptions');
    }
}
