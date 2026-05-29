<?php

return [
    'accept' => [
        'invalid' => 'This invitation is invalid or has expired.',
    ],
    'validation' => [
        'not_self' => 'You cannot invite yourself as a doctor.',
        'wrong_role' => 'This invitation can only be sent to registered doctor accounts.',
        'duplicate_pending' => 'There is already a pending invitation for this email address. Revoke it or wait until it expires before inviting again.',
        'already_linked' => 'This doctor is already linked to your profile.',
    ],
    'flash' => [
        'sent' => 'The invitation has been sent.',
        'revoked' => 'The invitation has been withdrawn.',
        'linked' => 'You are now linked to the patient profile.',
        'doctor_unlinked' => 'The link with the doctor has been removed.',
        'patient_unlinked' => 'The link with the patient has been removed.',
        'mail_failed' => 'The invitation could not be sent. Check your mail settings (MAIL_MAILER, SMTP) or try again later.',
    ],
    'entry' => [
        'wrong_account' => 'Sign in with a doctor account to accept this invitation.',
    ],
];
