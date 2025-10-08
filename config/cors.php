<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_merge([
        'http://localhost:8000',
        'http://localhost:8081',
        'http://localhost:19000',
        'http://localhost:19001',
        'http://localhost:19002',
        'http://127.0.0.1:8000',
        'http://127.0.0.1:8081',
        'http://127.0.0.1:19000',
        'http://127.0.0.1:19001',
        'http://127.0.0.1:19002',
        'http://10.0.2.2:8081', // Android emulator
        'http://10.0.2.2:19000',
        'http://10.0.2.2:19001',
        'http://10.0.2.2:19002',
    ], explode(',', env('CORS_ALLOWED_ORIGINS', ''))),

    'allowed_origins_patterns' => [
        '/^http:\/\/192\.168\.\d+\.\d+:\d+$/',
        '/^http:\/\/10\.0\.\d+\.\d+:\d+$/',
        '/^exp:\/\/192\.168\.\d+\.\d+:\d+$/',
        '/^exp:\/\/10\.0\.\d+\.\d+:\d+$/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
