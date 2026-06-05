<?php

return [
    'doctor_types' => [
        'dentist' => 'Tandarts',
        'hospital' => 'Ziekenhuis',
        'general_practitioner' => 'Huisarts',
        'specialist' => 'Specialist',
        'fallback' => 'Afspraak',
    ],

    'notification' => [
        'two_days_before' => [
            'title' => 'Afspraak over 2 dagen',
            'body_patient' => ':type · :provider · :date om :time',
            'body_family' => ':patient · :type · :provider · :date om :time',
        ],
        'two_hours_before' => [
            'title' => 'Afspraak over 2 uur',
            'body_patient' => ':type · :provider · vandaag om :time',
            'body_family' => ':patient · :type · :provider · vandaag om :time',
        ],
    ],
];
