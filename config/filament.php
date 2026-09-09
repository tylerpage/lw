<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Broadcasting
    |--------------------------------------------------------------------------
    |
    | Filament loads Laravel Echo in the admin panel when this config is set.
    | Laravel Cloud injects VITE_REVERB_* when a WebSocket cluster is attached.
    |
    */

    'broadcasting' => [

        'echo' => env('VITE_REVERB_APP_KEY') ? [
            'broadcaster' => 'reverb',
            'key' => env('VITE_REVERB_APP_KEY'),
            'wsHost' => env('VITE_REVERB_HOST'),
            'wsPort' => env('VITE_REVERB_PORT', 443),
            'wssPort' => env('VITE_REVERB_PORT', 443),
            'authEndpoint' => '/broadcasting/auth',
            'disableStats' => true,
            'encrypted' => true,
            'forceTLS' => (env('VITE_REVERB_SCHEME', 'https') === 'https'),
        ] : null,

    ],

];
