<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'pay' => [
            'driver' => 'daily',
            'path' => storage_path('logs/payment/pay.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'measure' => [
            'driver' => 'daily',
            'path' => storage_path('logs/measure.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'payout' => [
            'driver' => 'daily',
            'path' => storage_path('logs/payment/payout.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'vegas' => [
            'driver' => 'daily',
            'path' => storage_path('logs/vegas/vegas.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'pragmatik' => [
            'driver' => 'daily',
            'path' => storage_path('logs/pragmatik/api.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'slotegrator' => [
            'driver' => 'daily',
            'path' => storage_path('logs/sloterg/api.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'qt' => [
            'driver' => 'daily',
            'path' => storage_path('logs/qt/qt.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'login' => [
            'driver' => 'daily',
            'path' => storage_path('logs/login.log'),
            'level' => 'debug',
            'days' => 7,
        ],

        'telegram' => [
            'driver' => 'daily',
            'path' => storage_path('logs/telegram/tg.log'),
            'level' => 'debug',
            'days' => 7,
        ],

        'mail' => [
            'driver' => 'daily',
            'path' => storage_path('logs/mail/mail.log'),
            'level' => 'debug',
            'days' => 7,
        ],

        'viber' => [
            'driver' => 'daily',
            'path' => storage_path('logs/viber/viber.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 7,
        ],

        'retention' => [
            'driver' => 'daily',
            'path' => storage_path('logs/retention/retention.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 7,
        ],

        'stage' => [
            'driver' => 'daily',
            'path' => storage_path('logs/stage/stage.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 7,
        ],

        'softswiss' => [
            'driver' => 'daily',
            'path' => storage_path('logs/softswiss/softswiss.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'pay_api' => [
            'driver' => 'daily',
            'path' => storage_path('logs/payment/api.log'),
            'level' => 'debug',
        ],

    ],

];
