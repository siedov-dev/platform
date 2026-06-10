<?php

return [
    'class' => \App\Notifications\OneSignalApi::class,
    'appId' => env('NOTIFIER_APP_ID', ''),
    'key' => env('NOTIFIER_KEY', ''),
];
