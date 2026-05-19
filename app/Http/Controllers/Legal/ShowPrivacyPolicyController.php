<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ShowPrivacyPolicyController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Legal/Privacy', [
            'policyVersion' => config('privacy.policy_version'),
        ]);
    }
}
