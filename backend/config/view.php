<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | Use storage_path instead of realpath so fresh/ephemeral environments
    | (like Render cron jobs) always have a valid cache path at boot time.
    */

    'compiled' => env('VIEW_COMPILED_PATH', storage_path('framework/views')),

];
