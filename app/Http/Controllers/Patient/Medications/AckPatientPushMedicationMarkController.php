<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient\Medications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Support\Medications\PatientRecentPushMedicationMarkStore;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class AckPatientPushMedicationMarkController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(
        Request $request,
        PatientRecentPushMedicationMarkStore $recentPushMarkStore,
    ): Response {
        $patient = $this->authorizePatientProfile($request);

        $recentPushMarkStore->forget($patient->id);

        return response()->noContent();
    }
}
