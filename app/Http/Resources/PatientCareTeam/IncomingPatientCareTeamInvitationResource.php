<?php

namespace App\Http\Resources\PatientCareTeam;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class IncomingPatientCareTeamInvitationResource extends JsonResource
{
    public function __construct(
        mixed $resource,
        private readonly string $acceptRouteName,
        private readonly string $acceptRouteParameter,
    ) {
        parent::__construct($resource);
    }

    public static function doctorCollection(Collection $invitations): AnonymousResourceCollection
    {
        return self::collectionWithAcceptRoute(
            $invitations,
            'doctor.invitations.incoming.accept',
            'incomingDoctorInvitation',
        );
    }

    /** @param Collection<int, mixed> $invitations */
    public static function familyCollection(Collection $invitations): AnonymousResourceCollection
    {
        return self::collectionWithAcceptRoute(
            $invitations,
            'family.invitations.incoming.accept',
            'incomingFamilyInvitation',
        );
    }

    /** @param Collection<int, mixed> $invitations */
    private static function collectionWithAcceptRoute(
        Collection $invitations,
        string $acceptRouteName,
        string $acceptRouteParameter,
    ): AnonymousResourceCollection {
        return self::collection(
            $invitations->map(
                static fn (mixed $invitation): self => new self(
                    $invitation,
                    $acceptRouteName,
                    $acceptRouteParameter,
                ),
            ),
        );
    }

    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'patient_name' => (string) $this->patient->user->name,
            'expires_at' => $this->expires_at->toISOString(),
            'accept_url' => route(
                $this->acceptRouteName,
                [$this->acceptRouteParameter => $this->public_id],
                absolute: false,
            ),
        ];
    }
}
