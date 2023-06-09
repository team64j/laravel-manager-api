<?php

return [
    'uri' => 'manager/api',

    'guard' => [
        'driver' => 'jwt',
        'provider' => 'manager-api',
        'hash' => false,
        'input_key' => 'access_token',
        'storage_key' => 'access_token',
    ],

    'provider' => [
        'driver' => 'eloquent',
        'model' => Team64j\LaravelManagerApi\Models\User::class,
    ],
];
