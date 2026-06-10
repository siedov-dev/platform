<?php

namespace App\Http\Middleware;

use App\Helpers\Localization;
use App\MUser;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Update user last active value
 * moved to service
 * @deprecated
 */
class UpdateLastActive
{
    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        /** @var MUser $user */
        $user = Auth::user();
        if ($user !== null) {
            $last_update = Cache::get('last_active_updated'.$user->id);
            $date = date('Y-m-d H:i:00');
            if ($last_update !== $date) {
                Cache::put('last_active_updated'.$user->id, $date, 70);

                $user->updateLastActive();
                $user->locale = App::getLocale();
                $user->save();

                $acceptLang = Localization::getAvailableLocales();

                if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                    foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $lang_data) {
                        $lang = substr($lang_data, 0, 2);
                        if (in_array($lang, $acceptLang)) break;
                        $lang = 'en';
                    }
                    $user->accept_language = strtolower($lang);
                }
            }
        }

        return $next($request);
    }
}
