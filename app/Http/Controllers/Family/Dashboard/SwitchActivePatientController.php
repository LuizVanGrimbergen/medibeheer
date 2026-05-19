<?php

namespace App\Http\Controllers\Family\Dashboard;

use App\Enums\SecurityActivityDescription;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Models\Patient;
use App\Services\Audit\SecurityActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class SwitchActivePatientController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __construct(
        private readonly SecurityActivityLogger $securityActivityLogger,
    ) {}

    public function __invoke(Request $request, Patient $patient): RedirectResponse
    {
        $family = $this->authorizeFamilyProfile($request);

        abort_unless($family->patients()->whereKey($patient->id)->exists(), 404);

        $request->session()->put('family.active_patient_id', (int) $patient->id);

        $this->securityActivityLogger->record(
            SecurityActivityDescription::FAMILY_ACTIVE_PATIENT_SWITCHED,
            causer: $family->user,
            subject: $patient,
            properties: [
                'patient_id' => $patient->id,
                'family_id' => $family->id,
            ],
        );

        return back();
    }
}
