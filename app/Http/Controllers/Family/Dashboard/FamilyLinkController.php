<?php

namespace App\Http\Controllers\Family\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Services\Family\FamilyMedicationPlanProposalsScreenService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FamilyLinkController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __construct(
        private readonly FamilyMedicationPlanProposalsScreenService $medicationPlanProposalsScreenService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $family = $this->authorizeFamilyProfile($request);

        return Inertia::render(
            'Family/Link',
            $this->medicationPlanProposalsScreenService->indexProps($family),
        );
    }
}
