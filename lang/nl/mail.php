<?php

return [
    'verify_email' => [
        'subject' => 'Bevestig je e-mailadres',
        'greeting' => 'Welkom bij Medibeheer!',
        'line' => 'Bevestig je e-mailadres om je account te activeren.',
        'action' => 'E-mailadres bevestigen',
        'footer' => 'Als je dit account niet hebt aangemaakt, moet je dit negeren.',
    ],
    'reset_password' => [
        'subject' => 'Reset je wachtwoord',
        'greeting' => 'Hallo!',
        'line' => 'Je ontvangt deze e-mail omdat we een wachtwoord reset hebben ontvangen voor jouw account.',
        'action' => 'Wachtwoord resetten',
        'footer' => 'Als je geen reset hebt aangevraagd, hoef je niets te doen.',
    ],
    'family_invitation' => [
        'subject' => 'Uitnodiging om mee te kijken in Medibeheer',
        'greeting' => 'Hallo!',
        'line' => 'Iemand die je kent nodigt je uit om als familielid mee te kijken. Gebruik onderstaande code in de Medibeheer-app nadat je bent ingelogd met dit e-mailadres.',
        'expires' => 'Deze code is geldig tot :datetime.',
        'footer' => 'Als je deze uitnodiging niet verwacht, kun je deze e-mail negeren.',
        'salutation' => 'Met vriendelijke groet,',
    ],
];
