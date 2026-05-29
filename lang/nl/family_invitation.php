<?php

return [
    'accept' => [
        'invalid' => 'Deze uitnodiging is ongeldig of verlopen.',
    ],
    'validation' => [
        'not_self' => 'U kunt uzelf niet als familielid uitnodigen.',
        'wrong_role' => 'Deze uitnodiging kan alleen naar geregistreerde familielid-accounts worden gestuurd.',
        'duplicate_pending' => 'Er is al een openstaande uitnodiging voor dit e-mailadres. Trek die eerst in of wacht tot die verloopt voordat u opnieuw uitnodigt.',
        'already_linked' => 'Dit familielid is al gekoppeld aan uw profiel.',
    ],
    'flash' => [
        'sent' => 'De uitnodiging is verstuurd.',
        'revoked' => 'De uitnodiging is ingetrokken.',
        'linked' => 'U bent gekoppeld aan het patiëntprofiel.',
        'member_unlinked' => 'De koppeling met het familielid is verwijderd.',
        'mail_failed' => 'De uitnodiging kon niet worden verzonden. Controleer uw mailinstellingen (MAIL_MAILER, SMTP) of probeer het later opnieuw.',
    ],
    'entry' => [
        'wrong_account' => 'Log in met een familielid-account om deze uitnodiging te accepteren.',
    ],
];
