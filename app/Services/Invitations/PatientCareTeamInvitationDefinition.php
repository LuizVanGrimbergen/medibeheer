<?php

declare(strict_types=1);

namespace App\Services\Invitations;

use App\Enums\SecurityActivityDescription;
use App\Mail\DoctorInvitationAcceptedMail;
use App\Mail\DoctorInvitationMail;
use App\Mail\FamilyInvitationAcceptedMail;
use App\Mail\FamilyInvitationMail;
use App\Models\DoctorInvitation;
use App\Models\FamilyInvitation;

final readonly class PatientCareTeamInvitationDefinition
{
    public function __construct(
        public string $invitationModelClass,
        public string $configKey,
        public string $translationKey,
        public SecurityActivityDescription $createdActivity,
        public SecurityActivityDescription $revokedActivity,
        public SecurityActivityDescription $acceptedActivity,
        public string $invitationMailableClass,
        public string $acceptedMailableClass,
    ) {}

    public static function doctor(): self
    {
        return new self(
            invitationModelClass: DoctorInvitation::class,
            configKey: 'doctor_invitation',
            translationKey: 'doctor_invitation',
            createdActivity: SecurityActivityDescription::DOCTOR_INVITATION_CREATED,
            revokedActivity: SecurityActivityDescription::DOCTOR_INVITATION_REVOKED,
            acceptedActivity: SecurityActivityDescription::DOCTOR_INVITATION_ACCEPTED,
            invitationMailableClass: DoctorInvitationMail::class,
            acceptedMailableClass: DoctorInvitationAcceptedMail::class,
        );
    }

    public static function family(): self
    {
        return new self(
            invitationModelClass: FamilyInvitation::class,
            configKey: 'family_invitation',
            translationKey: 'family_invitation',
            createdActivity: SecurityActivityDescription::FAMILY_INVITATION_CREATED,
            revokedActivity: SecurityActivityDescription::FAMILY_INVITATION_REVOKED,
            acceptedActivity: SecurityActivityDescription::FAMILY_INVITATION_ACCEPTED,
            invitationMailableClass: FamilyInvitationMail::class,
            acceptedMailableClass: FamilyInvitationAcceptedMail::class,
        );
    }
}
