<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
        'theme',
        'bill_first_name',
        'bill_last_name',
        'bill_phone',
        'bill_address1',
        'bill_city',
        'bill_zip_code',
        'bill_country',
        'fingerprint',
    ];
}
