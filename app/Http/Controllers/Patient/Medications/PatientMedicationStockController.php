<?php

namespace App\Http\Controllers\Patient\Medications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\Medications\StoreMedicationStockRequest;
use App\Http\Requests\Patient\Medications\UpdateMedicationStockRequest;
use App\Models\Medication;
use App\Models\MedicationStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientMedicationStockController extends Controller
{
    use AuthorizesPatientProfile;

    public function store(StoreMedicationStockRequest $request, Medication $medication): RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $medication->stocks()->create(array_merge($request->validated(), [
            'patient_id' => $medication->patient_id,
            'family_id' => $medication->family_id,
        ]));

        return redirect()->route('patient.medications');
    }

    public function update(
        UpdateMedicationStockRequest $request,
        Medication $medication,
        MedicationStock $stock,
    ): RedirectResponse {
        $this->authorizePatientProfile($request);

        if (! $stock->medication->is($medication)) {
            abort(404);
        }

        $stock->fill($request->validated())->save();

        return redirect()->back(fallback: route('patient.medications'));
    }

    public function destroy(Request $request, Medication $medication, MedicationStock $stock): RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $this->authorize('update', $medication);

        if (! $stock->medication->is($medication)) {
            abort(404);
        }

        MedicationStock::destroy($stock->getKey());

        return redirect()->route('patient.medications');
    }
}
