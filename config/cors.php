<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | Configure aqui as regras de CORS para sua API ou aplicação.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'export/*', '*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    // 🚀 IMPORTANTE → expondo o header Content-Disposition
    'exposed_headers' => ['Content-Disposition'],

    'max_age' => 0,

    'supports_credentials' => false,

];
