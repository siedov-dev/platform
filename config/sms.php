<?php

return [
    // Possible values: BSG, SMSC, atomic
    'service' => 'BSG',

    'BSG' => [
        'key' => env('SMS_BSG_KEY'),
        'sender' => env('SMS_BSG_SENDER'),
    ],

    'SMSC' => [
        'login' => env('SMS_SMSC_LOGIN'),
        'password' => env('SMS_SMSC_PASSWORD'),
        'sender' => env('SMS_SMSC_SENDER'),
    ],

    'atomic' => [
        'publicKey' => env('SMS_ATOMIC_PUBLIC_KEY'),
        'privateKey' => env('SMS_ATOMIC_PRIVATE_KEY'),
        'sender' => env('SMS_ATOMIC_SENDER'),
    ]
];
