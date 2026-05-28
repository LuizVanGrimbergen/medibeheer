<?php

return [
    'verify_email' => [
        'subject' => 'Bevestig uw e-mailadres',
        'greeting' => 'Welkom bij Medibeheer!',
        'line' => 'Bevestig uw e-mailadres om uw account te activeren.',
        'action' => 'E-mailadres bevestigen',
        'footer' => 'Als u dit account niet hebt aangemaakt, kunt u dit negeren.',
    ],
    'reset_password' => [
        'subject' => 'Reset uw wachtwoord',
        'greeting' => 'Hallo!',
        'line' => 'U ontvangt deze e-mail omdat we een wachtwoord reset hebben ontvangen voor uw account.',
        'action' => 'Wachtwoord resetten',
        'footer' => 'Als u geen reset hebt aangevraagd, hoeft u niets te doen.',
    ],
    'family_invitation' => [
        'subject' => 'Uitnodiging om mee te kijken in Medibeheer',
        'greeting' => 'Hallo!',
        'line' => 'Iemand die u kent nodigt u uit om als familielid mee te kijken. Gebruik onderstaande code in de Medibeheer-app nadat u bent ingelogd met dit e-mailadres.',
        'expires' => 'Deze code is geldig tot :datetime.',
        'footer' => 'Als u deze uitnodiging niet verwacht, kunt u deze e-mail negeren.',
        'salutation' => 'Met vriendelijke groet,',
    ],
    'medication_plan_proposal' => [
        'subject' => 'Medicatieplan om te beoordelen in Medibeheer',
        'greeting' => 'Hallo!',
        'line' => 'Een familielid heeft een medicatieplan (:medication) met u gedeeld.',
        'action' => 'Medicatieplan bekijken',
        'review_hint' => 'Log in met dit e-mailadres op de Familie-pagina om het plan te bekijken, accepteren of weigeren.',
        'expires' => 'U kunt reageren tot :datetime.',
        'footer' => 'Als u dit medicatieplan niet verwacht, kunt u deze e-mail negeren.',
        'salutation' => 'Met vriendelijke groet,',
    ],
];
