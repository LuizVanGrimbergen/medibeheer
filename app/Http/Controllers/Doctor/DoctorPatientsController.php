<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Doctor\Concerns\AuthorizesDoctorProfile;
use App\Models\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorPatientsController extends Controller
{
    use AuthorizesDoctorProfile;

    public function __invoke(Request $request): Response
    {
        $doctor = $this->authorizeDoctorProfile($request);

        $patients = $doctor->patients()
            ->with('user')
            ->orderBy('patients.id')
            ->get()
            ->map(static function (Patient $patient): array {
                $user = $patient->user;

                return [
                    'public_id' => $user->public_id,
                    'name' => $user->name,
                ];
            });

        return Inertia::render('Doctor/Patients/Index', [
            'patients' => $patients,
        ]);
    }
}
