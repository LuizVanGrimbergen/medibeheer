<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Http\Requests\Patient\Medications\UpdateMedicationStockRequest;
use App\Models\MedicationStock;
use Illuminate\Http\RedirectResponse;

trait UpdatesMedicationStock
{
    protected function performMedicationStockUpdate(
        UpdateMedicationStockRequest $request,
        MedicationStock $stock,
        string $fallbackRoute,
    ): RedirectResponse {
        $stock->fill($request->validated())->save();

        return redirect()->back(fallback: $fallbackRoute);
    }
}
