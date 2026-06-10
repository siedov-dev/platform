<?php

namespace App\Helpers;

use App\Services\IpInfo\IpInfo;
use App\MUser;
use App\UserWallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\URL;

class Localization
{
    private static $_locale;

    const LOCALES = [
        'ru' => ['ru_RU.utf-8', 'ru-RU', 'Russian', 'ru_RU'],
        'ua' => ['uk_UA.utf8', 'uk-UA', 'Ukrainian', 'ua_UA'],
        'hi' => ['hi_IN.utf8', 'hi-IN'],
        'en' => 'en_US',
        'kz' => ['kk_KZ', 'kz_KZ', 'kk_KZ.UTF-8', 'kk_KZ.utf8'],
        'uz' => ['uz_UZ', 'uz_UZ.UTF-8'],
    ];


    const LANGS = [
        'UA' => 'ua',
        'RU' => 'ru',
        'EN' => 'en',
        'US' => 'en',
        'GB' => 'en',
        'HI' => 'hi',
        'KZ' => 'kz',
        'UZ' => 'uz',
    ];

    const DISABLED = [
        'api',
    ];

    public static function locale()
    {
        if (self::$_locale !== null) return self::$_locale;
        $locale = strtolower(request()->segment(1, ''));

        $locales = config('app.available_locales');
        if (!is_array($locales)) $locales = ['en', 'ru', 'ua', 'hi', 'kz'];

        if ($locale && in_array($locale, $locales)) {
            return $locale;
        }

        return '';
    }

    public static function setAppLocale(string $locale)
    {
        if (!in_array($locale, self::getAvailableLocales())) $locale = 'en';

        self::$_locale = $locale;
        App::setLocale($locale);
        if (array_key_exists($locale, self::LOCALES)) {
            setlocale(LC_TIME, self::LOCALES[$locale]);
            Carbon::setLocale($locale);
        }
        URL::defaults(['locale' => App::getLocale()]);
    }

    /**
     * det current active currency
     * @return int
     */
    public static function getCurrency()
    {
        /** @var MUser $user */
        $user = Auth::user();
        if ($user !== null) return $user->currency;
        $locale = App::getLocale();
        return self::getLocaleCurrency($locale);
    }

    /**
     * get currency for locale
     * @param $locale
     * @return int
     */
    public static function getLocaleCurrency($locale): int
    {
        if ($locale == 'ru') return UserWallet::CURRENCY_RUB;
        if ($locale == 'ua') return UserWallet::CURRENCY_UAH;
        if ($locale == 'kz') return UserWallet::CURRENCY_KZT;
        if ($locale == 'hi') return UserWallet::CURRENCY_INR;
        if ($locale == 'de') return UserWallet::CURRENCY_EUR;
        if ($locale == 'fr') return UserWallet::CURRENCY_EUR;
        if ($locale == 'it') return UserWallet::CURRENCY_EUR;
        if ($locale == 'pt') return UserWallet::CURRENCY_EUR;
        if ($locale == 'es') return UserWallet::CURRENCY_EUR;
        if ($locale == 'sk') return UserWallet::CURRENCY_EUR;
        if ($locale == 'uz') return UserWallet::CURRENCY_UZS;

        return UserWallet::CURRENCY_INR;
    }

    public static function getLocalNames()
    {
        return [
            'ru' => 'Русский',
            'ua' => 'Українська',
            'en' => 'English',
            'hi' => 'Hindi',
            'kz' => 'Қазақстан',
            'de' => 'Deutsch',
            'fr' => 'Français',
            'it' => 'Italiano',
            'pt' => 'Português',
            'es' => 'Español',
            'sk' => 'Slovenský',
            'uz' => 'Uzbek',
        ];
    }

    public static function getConfigLocale($locale = ""): string
    {
        if ($locale === "") $locale = App::getLocale();
        if (!in_array($locale, ['en', 'kz', 'hi', 'ru', 'ua'])) return 'en';
        return $locale;
    }

    public static function getAvailableLocales(): array
    {
        $locales = config('app.available_locales');
        if (!is_array($locales)) $locales = ['en', 'ru', 'ua', 'hi', 'kz', 'de', 'fr', 'it', 'pt', 'es', 'sk', 'uz'];
        return $locales;
    }

    public static function getLocale(Request $request): string
    {
        $locale = \Illuminate\Support\Facades\Request::segment(1);

        if (in_array($locale, self::DISABLED)) return '';

        $locale = explode('/', $locale)[0];
        $is_empty_local = empty($locale);

        $locales = self::getAvailableLocales();

        if (!in_array($locale, $locales)) {
            $is_empty_local = true;
            $locale = Cookie::get('locale');
            if (empty($locale) || !in_array($locale, $locales)) {
                if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                    $lang = 'en';
                    foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $lang_data) {
                        $lang = substr($lang_data, 0, 2);
                        if (in_array($lang, $locales)) break;
                        $lang = 'en';
                    }
                    $locale = $lang;
                } else {
                    $info = IpInfo::getInfo(\request()->ip());
                    $code = strtoupper($info ? $info->country_code : 'EN');
                    $locale = array_key_exists($code, self::LANGS) ? self::LANGS[$code] : $locales[0];
                }
            }
        }

        return $locale;
    }
}
