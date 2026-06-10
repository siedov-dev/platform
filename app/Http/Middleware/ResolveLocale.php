<?php

namespace App\Http\Middleware;

use App\Events\LocaleChanged;
use App\Helpers\Localization;
use App\MUser;
use App\Services\IpInfo\IpInfo;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\URL;

class ResolveLocale
{
    const LOCALES = [
        'ru' => ['ru_RU.utf8', 'ru-RU', 'Russian', 'ru_RU'],
        'ua' => ['uk_UA.utf8', 'uk-UA', 'Ukrainian', 'ua_UA'],
        'hi' => ['hi_IN.utf8', 'hi-IN'],
        'en' => 'en_US',
        'kz' => ['kk_KZ.utf8', 'kk_KZ', 'kz_KZ', 'kk_KZ.UTF-8'],
        'de' => ['de_DE.utf8', 'de_DE', 'Deutsch', 'German', 'de_DE.UTF-8'],
        'fr' => ['fr_FR.utf8', 'fr_FR', 'France', 'fr_FR.UTF-8'],
        'it' => ['it_IT.utf8', 'it_IT', 'Italian', 'it_IT.UTF-8'],
        'pt' => ['pt_PT.utf8', 'pt_PT', 'pt_PT.UTF-8'],
        'es' => ['es_ES.utf8', 'es_ES', 'es_ES.UTF-8'],
        'sk' => ['sk_SK.utf8', 'sk_SK', 'sk_SK.UTF-8'],
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
        'DE' => 'de',
        'FR' => 'fr',
        'IT' => 'it',
        'PT' => 'pt',
        'ES' => 'es',
        'SK' => 'sk',
        'UZ' => 'uz',
    ];

    const DISABLED = [
        'api',
        'telescope',
        'captcha',
    ];


    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = \Illuminate\Support\Facades\Request::segment(1);

        if (in_array($locale, self::DISABLED)) return $next($request);

        $locale = explode('/', $locale)[0];
        $is_empty_local = empty($locale);

        $locales = Localization::getAvailableLocales();
        $replace_locale = false;
        $unsupported_locale = '';

        if (!in_array($locale, $locales)) {
            $is_empty_local = true;
            if (strlen($locale) === 2) {
                $unsupported_locale = $locale;
                $replace_locale = true;
            }
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
        } else {
            $cook = Cookie::make('locale', $locale, 60 * 24 * 30, '/', null, true, false);
            Cookie::queue($cook);
        }

        if ( !empty($locale)) {
            App::setLocale($locale);
            if (array_key_exists($locale, self::LOCALES)) {
                setlocale(LC_TIME, self::LOCALES[$locale]);
                Carbon::setLocale($locale);
            }
        }

        URL::defaults(['locale' => App::getLocale()]);

        if ($request->isMethod('get') && $is_empty_local) {
            $cook = Cookie::make('locale', $locale, 60 * 24 * 30, '/', null, true, false);
            Cookie::queue($cook);
            if ($replace_locale) return redirect()->to(preg_replace("#/$unsupported_locale#", "/$locale", $request->getRequestUri(), 1))->withInput();
            return redirect()->to("/$locale" . $request->getRequestUri())->withInput();
        }

        $resp = $next($request);

        if (Auth::check() && MUser::getAuth()->getLocale() !== $locale) {
            MUser::getAuth()->setLocale($locale);
            event(new LocaleChanged(Auth::id(), $locale));
        }

        return $resp;
    }
}
