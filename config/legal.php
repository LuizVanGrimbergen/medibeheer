<?php

return [

    'terms_version' => env('LEGAL_TERMS_VERSION', '2026-06-08'),

    'controller' => [
        'name' => env('LEGAL_CONTROLLER_NAME', 'Medibeheer'),
        'address' => env('LEGAL_CONTROLLER_ADDRESS'),
        'kbo' => env('LEGAL_CONTROLLER_KBO'),
    ],

    'supported_document_locales' => ['nl', 'en'],

];
