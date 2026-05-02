<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        if ($user->isPatient()) {
            return redirect()->route('patient.dashboard');
        }

        return Inertia::render('Dashboard');
    }
}
