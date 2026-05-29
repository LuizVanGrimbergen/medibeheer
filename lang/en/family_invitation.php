<?php

return [
    'accept' => [
        'invalid' => 'This invitation is invalid or has expired.',
    ],
    'validation' => [
        'not_self' => 'You cannot invite yourself as a family member.',
        'wrong_role' => 'This invitation can only be sent to registered family member accounts.',
        'duplicate_pending' => 'There is already a pending invitation for this email address. Revoke it or wait until it expires before inviting again.',
        'already_linked' => 'This family member is already linked to your profile.',
    ],
    'flash' => [
        'sent' => 'The invitation has been sent.',
        'revoked' => 'The invitation has been withdrawn.',
        'linked' => 'You are now linked to the patient profile.',
        'member_unlinked' => 'The link with the family member has been removed.',
        'mail_failed' => 'The invitation could not be sent. Check your mail settings (MAIL_MAILER, SMTP) or try again later.',
    ],
    'entry' => [
        'wrong_account' => 'Sign in with a family member account to accept this invitation.',
    ],
];
