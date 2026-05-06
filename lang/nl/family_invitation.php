<?php

return [
    'accept' => [
        'invalid' => 'De code is ongeldig of verlopen.',
    ],
    'validation' => [
        'not_self' => 'Je kunt jezelf niet als familielid uitnodigen.',
        'duplicate_pending' => 'Er is al een openstaande uitnodiging voor dit e-mailadres. Trek die eerst in of wacht tot die verloopt voordat je opnieuw uitnodigt.',
    ],
    'flash' => [
        'sent' => 'De uitnodiging is verstuurd.',
        'revoked' => 'De uitnodiging is ingetrokken.',
        'linked' => 'Je bent gekoppeld aan het patiëntprofiel.',
        'mail_failed' => 'De uitnodiging is opgeslagen, maar de e-mail kon niet worden verzonden. Controleer je mailinstellingen (MAIL_MAILER, SMTP) of probeer het later opnieuw.',
    ],
];
