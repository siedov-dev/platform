<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'crmDepKey' => env('CRM_DEP_KEY', ''),

    'kusersKey' => env('KUSERS_KEY', ''),
    'kusersHost' => env('KUSERS_HOST', 'http://127.0.0.1:23100'),

    'natsToken' => env('NATS_TOKEN', ''),

    'gameManagerHost' => env('GAME_MANAGER_HOST', 'localhost'),
    'gameManagerPort' => env('GAME_MANAGER_PORT', '8095'),

    'tournamentsEnabled' => env('TOURNAMENTS_ENABLED', false),
    'referralsEnabled' => env('REFERRALS_ENABLED', false),
    'kIpInfoHost' => env('KIPINFO_HOST', 'http://127.0.0.1:23105'),
    'kBonusHost' => env('KBONUS_HOST', 'http://127.0.0.1:23102'),
    'kCmsHost' => env('KCMS_HOST', 'http://127.0.0.1:23098'),
    'kSocketHost' => env('KSOCKET_HOST', 'http://127.0.0.1:8082'),
    'kSlcHost' => env('KSLC_HOST', 'http://127.0.0.1:23104'),
    'kNatsHost' => env('KNATS_HOST', 'localhost'),
    'kPayHost' => env('KPAY_HOST', 'http://127.0.0.1:23106'),

    'ServicesToken' => env('SERVICES_TOKEN', ''),

    'customerIo' => [
        'enabled' => env('CUSTOMER_IO_ENABLED', false),
        'siteId' => env('CUSTOMER_IO_SITE_ID', ''),
        'key' => env('CUSTOMER_IO_KEY', ''),
        'apiKey' => env('CUSTOMER_IO_API_KEY', ''),
        'track_endpoint' => env('CUSTOMER_IO_TRACK_API_URL', 'https://api.customer.io/v1/'),
        'api_endpoint' => env('CUSTOMER_IO_API_URL', 'https://api.customer.io/v1/'),
    ],
];
