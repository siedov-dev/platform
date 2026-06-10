<?php

namespace App\Http\Middleware;

use App\Models\PersonalData;
use App\MUser;
use App\Services\Users\UserManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckBlock
{
    const EXCLUDED_ROUTES = ['wallet.balance', 'landing', 'lastWin', 'logout', 'cabinet.account'];
    const EXCLUDED_ROUTES_SETTINGS = ['wallet.balance', 'landing', 'lastWin', 'logout', 'cabinet.settings', 'cabinet.personalData', 'cabinet.account', 'bonus.lane',];


    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()){
            /** @var MUser $user */
            $user = Auth::user();
//            if ($user->getId() !== 12 && $user->getId() !== 158603) {
//                return response("Service in maintenance mode");
//            }
            $block = UserManager::userPermanentBlockGet($user->getId());

            if (!empty($block) && !in_array(request()->route()->getName(), self::EXCLUDED_ROUTES)) {
                Auth::logout();
                if ($request->ajax()) {
                    return response(empty($block['reason']) ? 'Account is blocked' : $block['reason'], 403);
                }
                return redirect('/');
            }

        }
        return $next($request);
    }


}
