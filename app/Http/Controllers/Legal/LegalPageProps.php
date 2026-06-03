<?php

namespace App\Http\Controllers\Legal;

final class LegalPageProps
{
    /** @return array{policyVersion: string, contactEmail: string, retention: array<string, int>} */
    public static function forInertia(): array
    {
        /** @var string|null $contactEmail */
        $contactEmail = config('privacy.contact_email');

        if (! is_string($contactEmail) || $contactEmail === '') {
            $contactEmail = config('mail.from.address');
        }

        /** @var array<string, int> $retention */
        $retention = config('privacy.retention');

        return [
            'policyVersion' => (string) config('privacy.policy_version'),
            'contactEmail' => (string) $contactEmail,
            'retention' => $retention,
        ];
    }
}
