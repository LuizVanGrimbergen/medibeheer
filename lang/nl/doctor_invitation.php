<?php

return [
    'accept' => [
        'invalid' => 'Deze uitnodiging is ongeldig of verlopen.',
    ],
    'validation' => [
        'not_self' => 'U kunt uzelf niet als arts uitnodigen.',
        'wrong_role' => 'Deze uitnodiging kan alleen naar geregistreerde arts-accounts worden gestuurd.',
        'duplicate_pending' => 'Er is al een openstaande uitnodiging voor dit e-mailadres. Trek die eerst in of wacht tot die verloopt voordat u opnieuw uitnodigt.',
        'already_linked' => 'Deze arts is al gekoppeld aan uw profiel.',
    ],
    'flash' => [
        'sent' => 'De uitnodiging is verstuurd.',
        'revoked' => 'De uitnodiging is ingetrokken.',
        'linked' => 'U bent gekoppeld aan het patiëntprofiel.',
        'doctor_unlinked' => 'De koppeling met de arts is verwijderd.',
        'patient_unlinked' => 'De koppeling met de patiënt is verwijderd.',
        'mail_failed' => 'De uitnodiging kon niet worden verzonden. Controleer uw mailinstellingen (MAIL_MAILER, SMTP) of probeer het later opnieuw.',
    ],
    'entry' => [
        'wrong_account' => 'Log in met een arts-account om deze uitnodiging te accepteren.',
    ],
];
