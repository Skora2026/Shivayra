<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Store Owner (the single admin account)
    |--------------------------------------------------------------------------
    |
    | The admin panel has exactly one account. It is provisioned by
    | AdminSeeder from these values — idempotently, so re-seeding on any
    | environment always converges on this one owner and never duplicates
    | or locks anyone out.
    |
    */

    'owner' => [
        'email' => env('ADMIN_EMAIL', 'admin@shivayra.com'),
        'password' => env('ADMIN_PASSWORD', 'admin123'),
        'name' => env('ADMIN_NAME', 'Store Owner'),
    ],

];
