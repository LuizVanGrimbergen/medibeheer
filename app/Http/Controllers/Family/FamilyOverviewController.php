<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FamilyOverviewController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __invoke(Request $request): Response
    {
        $this->authorizeFamilyProfile($request);

        return Inertia::render('Family/Overview');
    }
}
