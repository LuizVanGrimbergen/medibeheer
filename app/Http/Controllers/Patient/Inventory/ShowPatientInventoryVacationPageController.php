<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowPatientInventoryVacationPageController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(Request $request): Response
    {
        $this->authorizePatientProfile($request);

        return Inertia::render('Patient/Inventory/Vacation', [
            'starts_on' => '',
            'ends_on' => '',
            'result' => null,
        ]);
    }
}
