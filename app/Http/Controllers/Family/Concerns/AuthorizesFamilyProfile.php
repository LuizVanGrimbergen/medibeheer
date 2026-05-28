<?php

namespace App\Http\Controllers\Family\Concerns;

use App\Models\Family;
use App\Models\MedicationPlanProposal;
use Illuminate\Http\Request;

trait AuthorizesFamilyProfile
{
    protected function authorizeFamilyProfile(Request $request): Family
    {
        $user = $request->user();

        abort_unless($user !== null && $user->isFamilyMember(), 403);

        $family = $user->familyOrCreate();

        $this->authorize('view', $family);

        return $family;
    }

    protected function authorizeMedicationPlanProposalForFamily(
        Family $family,
        MedicationPlanProposal $proposal,
    ): void {
        abort_unless((int) $proposal->family_id === (int) $family->id, 404);
    }
}
