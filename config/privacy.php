<?php

return [

    'policy_version' => env('PRIVACY_POLICY_VERSION', '2026-06-06'),

    'contact_email' => env('PRIVACY_CONTACT_EMAIL'),

    'retention' => [
        'security_activity_log_days' => (int) env('PRIVACY_SECURITY_ACTIVITY_LOG_RETENTION_DAYS', 90),
        'data_activity_log_days' => (int) env('PRIVACY_DATA_ACTIVITY_LOG_RETENTION_DAYS', 365),
        'session_days' => (int) env('PRIVACY_SESSION_RETENTION_DAYS', 30),
    ],

];
