<?php

return [
    'verify_email' => [
        'subject' => 'Confirm your email address',
        'greeting' => 'Welcome to Medibeheer!',
        'line' => 'Confirm your email address to activate your account.',
        'action' => 'Confirm email address',
        'footer' => 'If you did not create this account, you can ignore this email.',
    ],
    'reset_password' => [
        'subject' => 'Reset your password',
        'greeting' => 'Hello!',
        'line' => 'You are receiving this email because we received a password reset request for your account.',
        'action' => 'Reset password',
        'footer' => 'If you did not request a password reset, no further action is required.',
    ],
    'family_invitation' => [
        'subject' => 'Invitation to follow along in Medibeheer',
        'greeting' => 'Hello!',
        'line' => 'Someone you know invited you to join as a family member. Enter the code below in the Medibeheer app after signing in with this email address.',
        'expires' => 'This code is valid until :datetime.',
        'footer' => 'If you did not expect this invitation, you can ignore this email.',
        'salutation' => 'Kind regards,',
    ],
    'medication_plan_proposal' => [
        'subject' => 'Medication plan to review in Medibeheer',
        'greeting' => 'Hello!',
        'line' => 'A family member shared a medication plan (:medication) with you.',
        'action' => 'Review medication plan',
        'review_hint' => 'Sign in with this email address on the Family page to review, accept, or decline the plan.',
        'expires' => 'You can respond until :datetime.',
        'footer' => 'If you did not expect this medication plan, you can ignore this email.',
        'salutation' => 'Kind regards,',
    ],
];
