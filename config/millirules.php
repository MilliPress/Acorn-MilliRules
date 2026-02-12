<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Control how the MilliRules middleware is registered. The middleware
    | executes rules after route matching and applies response modifications
    | (headers, redirects) to the outgoing HTTP response.
    |
    */

    'middleware' => [

        // Set to false to disable automatic middleware registration.
        'enabled' => true,

        // Middleware groups to attach to (e.g. ['web', 'api']).
        'groups' => ['web'],
    ],

];
