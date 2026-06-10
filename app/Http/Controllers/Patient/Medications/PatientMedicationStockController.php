<?php

namespace App\Http\Controllers\Patient\Medications;

use App\Http\Controllers\Concerns\UpdatesMedicationStock;
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
    use UpdatesMedicationStock;

    public function store(StoreMedicationStockRequest $request, Medication $medication): RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $medication->stocks()->updateOrCreate(
            ['medication_id' => $medication->id],
            $request->validated(),
        );

        return redirect()->route('patient.medications');
    }

    public function update(
        UpdateMedicationStockRequest $request,
        Medication $medication,
        MedicationStock $stock,
    ): RedirectResponse {
        $this->authorizePatientProfile($request);

        return $this->performMedicationStockUpdate(
            $request,
            $stock,
            route('patient.medications'),
        );
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
