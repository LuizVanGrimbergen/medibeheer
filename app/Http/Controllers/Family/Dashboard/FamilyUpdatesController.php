<?php

declare(strict_types=1);

namespace App\Http\Controllers\Family\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class FamilyUpdatesController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __invoke(Request $request): Response
    {
        $this->authorizeFamilyProfile($request);

        return Inertia::render('Family/Updates');
    }
}
