<?php

declare(strict_types=1);

namespace App\Http\Controllers\Family\Dashboard;

use App\Http\Controllers\Concerns\UpdatesMedicationStock;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Http\Requests\Patient\Medications\UpdateMedicationStockRequest;
use App\Models\Medication;
use App\Models\MedicationStock;
use Illuminate\Http\RedirectResponse;

final class FamilyMedicationStockController extends Controller
{
    use AuthorizesFamilyProfile;
    use UpdatesMedicationStock;

    public function update(
        UpdateMedicationStockRequest $request,
        Medication $medication,
        MedicationStock $stock,
    ): RedirectResponse {
        $this->authorizeFamilyProfile($request);

        return $this->performMedicationStockUpdate(
            $request,
            $stock,
            route('family.medications'),
        );
    }
}
