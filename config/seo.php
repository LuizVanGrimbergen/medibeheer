<?php

return [

    'description' => env(
        'SEO_DESCRIPTION',
        'Medibeheer is een veilige webapp voor medicatiebeheer, voorschriften, innames, voorraad, afspraken en medicatieplannen. Voor patiënten, familie en zorgverleners in België en Nederland.',
    ),

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
