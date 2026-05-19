<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ShowCookiePolicyController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Legal/Cookies', [
            'policyVersion' => config('privacy.policy_version'),
        ]);
    }
}
