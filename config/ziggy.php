<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route groups
    |--------------------------------------------------------------------------
    |
    | Named groups limit the Ziggy payload on public pages that only need a
    | handful of client-side routes (see resources/views/app.blade.php).
    |
    */

    'groups' => [
        'public' => [
            'home',
            'login',
            'register',
            'logout',
            'legal.*',
            'password.*',
            'verification.*',
        ],
    ],

];
