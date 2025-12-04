<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Define the rate limiting configuration for your API.
    |
    */

    'rate_limit' => [
        'enabled' => false,
        'window' => 60,
        'max_requests' => 60,
        'authenticated_max_requests' => 120,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Versioning
    |--------------------------------------------------------------------------
    |
    | Default API version and supported versions.
    |
    */

    'versioning' => [
        'default' => 'v1',
        'supported' => ['v1'],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to be applied to API routes.
    |
    */

    'middleware' => [
        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ],

];
