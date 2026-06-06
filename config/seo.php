<?php

return [

    'description' => env(
        'SEO_DESCRIPTION',
        'Medibeheer is een veilige webapp voor medicatiebeheer, voorschriften, innames, voorraad, afspraken en check-ins. Voor patiënten, familie en zorgverleners in België en Nederland.',
    ),

    'pages' => [
        'home' => [
            'title' => 'Medicatiebeheer voor patiënten, familie en zorgverleners',
            'description' => env(
                'SEO_DESCRIPTION',
                'Medibeheer is een veilige webapp voor medicatiebeheer, voorschriften, innames, voorraad, afspraken en check-ins. Voor patiënten, familie en zorgverleners in België en Nederland.',
            ),
        ],
        'login' => [
            'title' => 'Log in',
            'description' => 'Log in bij Medibeheer om medicatie, innames, voorraad en afspraken veilig te beheren als patiënt, familie of zorgverlener.',
        ],
        'register' => [
            'title' => 'Registreren',
            'description' => 'Maak een gratis Medibeheer-account aan voor veilig medicatiebeheer, innames en afspraken, voor patiënten, familie en zorgverleners.',
        ],
        'password.request' => [
            'title' => 'Wachtwoord vergeten',
            'description' => 'Wachtwoord vergeten? Vraag een veilige resetlink aan voor uw Medibeheer-account.',
        ],
        'legal.privacy' => [
            'title' => 'Privacybeleid',
            'description' => 'Lees hoe Medibeheer uw persoons- en gezondheidsgegevens verwerkt, bewaart en beschermt conform de AVG/GDPR.',
        ],
        'legal.cookies' => [
            'title' => 'Cookiebeleid',
            'description' => 'Informatie over cookies, lokale opslag en push-instellingen in de Medibeheer webapp.',
        ],
    ],

    'locale' => env('SEO_LOCALE', 'nl_BE'),

    'og_image' => '/images/medibeheer-pwa-512.png',

    'indexable_route_names' => [
        'home',
        'login',
        'register',
        'password.request',
        'legal.privacy',
        'legal.cookies',
    ],

    'sitemap_route_names' => [
        'home',
        'login',
        'register',
        'password.request',
        'legal.privacy',
        'legal.cookies',
    ],

    'sitemap_priorities' => [
        'home' => ['changefreq' => 'weekly', 'priority' => '1.0'],
        'login' => ['changefreq' => 'monthly', 'priority' => '0.8'],
        'register' => ['changefreq' => 'monthly', 'priority' => '0.8'],
        'password.request' => ['changefreq' => 'yearly', 'priority' => '0.3'],
        'legal.privacy' => ['changefreq' => 'yearly', 'priority' => '0.4'],
        'legal.cookies' => ['changefreq' => 'yearly', 'priority' => '0.4'],
    ],

    'robots_disallow_paths' => [
        '/patient/',
        '/family/',
        '/doctor/',
        '/settings',
        '/verify-email',
        '/confirm-password',
        '/reset-password',
        '/family/invitation',
        '/doctor/invitation',
        '/logout',
    ],

];
