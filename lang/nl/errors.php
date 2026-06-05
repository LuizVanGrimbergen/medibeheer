<?php

return [
    'back_home' => 'Terug naar start',
    'retry_now' => 'Opnieuw proberen',

    '401' => [
        'title' => 'Niet ingelogd',
        'message' => 'Log in om verder te gaan.',
    ],

    '403' => [
        'title' => 'Geen toegang',
        'message' => 'Je hebt geen toestemming om deze pagina te bekijken.',
    ],

    '404' => [
        'title' => 'Pagina niet gevonden',
        'message' => 'Deze pagina bestaat niet of is verplaatst.',
    ],

    '419' => [
        'title' => 'Pagina verlopen',
        'message' => 'Je sessie is verlopen. Vernieuw de pagina en probeer het opnieuw.',
    ],

    '429' => [
        'title' => 'Te veel verzoeken',
        'message' => 'Je hebt te veel verzoeken verstuurd. Wacht even en probeer het opnieuw.',
        'throttle' => 'Te veel verzoeken. Probeer het opnieuw over :seconds seconden.',
    ],

    '500' => [
        'title' => 'Er ging iets mis',
        'message' => 'Er is een fout opgetreden. Probeer het later opnieuw.',
    ],

    '503' => [
        'title' => 'Tijdelijk niet beschikbaar',
        'message' => 'Medibeheer is tijdelijk niet bereikbaar. Probeer het zo opnieuw.',
    ],
];
