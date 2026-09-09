<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maintenance mode IP allowlist (env fallback)
    |--------------------------------------------------------------------------
    |
    | Comma-separated IP addresses that can bypass maintenance mode on the
    | public site. The primary allowlist is managed in Site Settings.
    |
    */

    'allowlist_ips' => array_values(array_filter(array_map(
        trim(...),
        explode(',', env('MAINTENANCE_ALLOWLIST_IPS', '')),
    ))),

];
