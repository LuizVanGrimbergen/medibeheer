<?php

return [
    'accept' => [
        'invalid' => 'The code is invalid or has expired.',
    ],
    'validation' => [
        'not_self' => 'You cannot invite yourself as a family member.',
        'duplicate_pending' => 'There is already a pending invitation for this email address. Revoke it or wait until it expires before inviting again.',
    ],
    'flash' => [
        'sent' => 'The invitation has been sent.',
        'revoked' => 'The invitation has been withdrawn.',
        'linked' => 'You are now linked to the patient profile.',
        'mail_failed' => 'The invitation was saved, but the email could not be sent. Check your mail settings (MAIL_MAILER, SMTP) or try again later.',
    ],
];
