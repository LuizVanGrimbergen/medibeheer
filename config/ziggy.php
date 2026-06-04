<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Skip route() in @routes output
    |--------------------------------------------------------------------------
    |
    | The route helper ships in the Vite bundle via ZiggyVue. JSON output in
    | the layout avoids a large render-blocking inline script.
    |
    */

    'skip-route-function' => true,

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
