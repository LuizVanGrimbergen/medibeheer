<?php

return [
    'publish' => [
        'not_draft' => 'This medication plan can no longer be published.',
        'mail_failed' => 'The plan could not be sent. Check the email address and your mail settings.',
        'validation' => [
            'patient_email_required' => 'Enter the patient’s email address.',
            'patient_email_invalid' => 'Enter a valid email address.',
        ],
    ],
    'unnamed_medication' => 'Medication',
    'review' => [
        'unavailable' => 'This medication plan is no longer available.',
    ],
    'flash' => [
        'draft_saved' => 'The medication plan draft has been saved.',
        'mail_sent' => 'The medication plan has been emailed to the patient.',
        'published' => 'The medication plan has been sent to the patient.',
        'revoked' => 'The request for this medication plan has been withdrawn.',
        'accepted' => 'The medication plan has been added to your medication list.',
        'declined' => 'You declined the medication plan.',
    ],
    'status' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'accepted' => 'Imported',
        'declined' => 'Declined',
        'revoked' => 'Revoked',
    ],
];
