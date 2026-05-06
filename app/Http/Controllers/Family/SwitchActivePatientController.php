<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class SwitchActivePatientController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __invoke(Request $request, Patient $patient): RedirectResponse
    {
        $family = $this->authorizeFamilyProfile($request);

        abort_unless($family->patients()->whereKey($patient->id)->exists(), 404);

        $request->session()->put('family.active_patient_id', (int) $patient->id);

        return back();
    }
}

