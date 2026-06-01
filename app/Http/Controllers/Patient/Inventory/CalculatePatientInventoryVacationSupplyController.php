<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\Inventory\CalculatePatientInventoryVacationSupplyRequest;
use App\Services\Medications\MedicationVacationSupplyService;
use Carbon\CarbonImmutable;
use Inertia\Inertia;
use Inertia\Response;

final class CalculatePatientInventoryVacationSupplyController extends Controller
{
    use AuthorizesPatientProfile;

    public function __construct(
        private readonly MedicationVacationSupplyService $medicationVacationSupplyService,
    ) {}

    public function __invoke(CalculatePatientInventoryVacationSupplyRequest $request): Response
    {
        $patient = $this->authorizePatientProfile($request);

        $validated = $request->validated();

        $result = $this->medicationVacationSupplyService->buildPickupList(
            $patient,
            CarbonImmutable::parse($validated['starts_on'])->startOfDay(),
            CarbonImmutable::parse($validated['ends_on'])->startOfDay(),
        );

        return Inertia::render('Patient/Inventory/Vacation', [
            'starts_on' => $validated['starts_on'],
            'ends_on' => $validated['ends_on'],
            'result' => $result,
        ]);
    }
}
