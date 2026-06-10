<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
        '/',
        '/get_visa',
        '/piastrix_pay_url',
        '/pay/status',
        '/*/pay/status',
        '/pay/success',
        '/*/pay/success',
        '/pay/fail',
        '/*/pay/fail',
        '/social-login',
        '/denomination',
        '/push/subscribe',
        '/set-nomination-user',
        '/planet-callback',
        '/GetUserData',
        'GetUserData',
        '/RefreshToken',
        'RefreshToken',
        'MakePayment',
        '/MakePayment',
        'GetPaymentInfo',
        '/GetPaymentInfo',
        'GetPromoInfo',
        '/GetPromoInfo',
        'sport-transaction',
        '/sport-transaction',
        'check-play-sport',
        'check-play-sport',
        '/check-play-sport',
        'sport-transaction',
        '/sport-transaction',

        'sport-transaction',
        'pay-from-invest',
        'GetVegasBonus',
        '/perfect-success',
        '/perfect-error',
        '/join-with-bonus',
        '/reg-with-bonus',
        '/reg/preland',

        '/pay/billLine/deposit',
        '/pay/billLine/process',
        '/pay/billLine/success',
        '/pay/billLine/fail',

        '/setNotificationStatus',

        '/user-verification',
    ];
}
