<?php

namespace App\Http\Controllers\Patient\Doctors;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class PatientDoctorsController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->to(route('patient.family', absolute: false).'#doctors');
    }
}
