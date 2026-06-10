<?php

namespace App\Http\Controllers;

use App\Helpers\Localization;
use App\Models\BonusBlackList;
use App\Models\BonusPromocode;
use App\Models\PromoActivationLog;
use App\Models\Slc\Achievements\SlcAchievement;
use App\MUser;
use App\Services\KSocket\KSocket;
use App\Services\Users\UserManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BonusPromocodesController extends Controller
{
    //
    public function activate(Request $request)
    {
        $registration = $request->boolean('registration', false);
        $promocode = $request->promocode;

        if (empty($promocode)) {
            return response(__('bonus.enter_promocode'), 400);
        }

        $log = new PromoActivationLog();
        $log->setRegistration($registration);
        $log->setUserId(Auth::id()?? 0);
        $log->setPromocode($promocode);
        $log->save();

        /** @var BonusPromocode $promo */
        $promo = BonusPromocode::where('promocode', $promocode)->orderBy('created_at', 'desc')->first();
        if (!$promo) {
            return response(__('bonus.promocode_not_found'), 400);
        }

        $log->setPromoId($promo->getId());
        $log->save();

        if ($promo->active_to !== null && $promo->active_to < now()) {
            return response(__('bonus.promocode_expired'), 400);
        }
        $user = MUser::getAuth();

        $is_in_black_list = BonusBlackList::where('user_id', $user->getId())->first() !== null;

        if ($is_in_black_list) {
            return response(__('bonus.promocode_unavailable'), 400);
        }

        if ($promo->activation_type == BonusPromocode::ACTIVATION_TYPE_REGISTERED && $user->created_at > $promo->created_at) {
            return response(__('bonus.promocode_unavailable'), 400);
        }

        if ($promo->activation_type == BonusPromocode::ACTIVATION_TYPE_NOT_REGISTERED && $user->created_at <= $promo->created_at) {
            return response(__('bonus.promocode_unavailable'), 400);
        }

        if (!$promo->canActivate($user)) {
            return response(__('bonus.promocode_no_loner_available'), 400);
        }

        if ($promo->activate($user, $registration) === null) {
            return response(__('bonus.promocode_activation_error'), 400);
        }

        $log->setSuccess(true);
        $log->save();

        SlcAchievement::updateAchievements($user);

        KSocket::getInstance()->sendEvent($user->getId(), "bonus:changed");

        if (session('showDeposit')) {
            return response(__('bonus.promocode_activated'), 200, ['show_deposit' => true]);
        }
        return response(__('bonus.promocode_activated'));
    }

    public function crmActivate(Request $request)
    {
        $key = 'bonus128keySzx';

        Log::info('Crm activating bonus');
        Log::info(var_export($request->all(), true));

        $data = $request->all();
        $hash = hash('sha256', $data['user_id'] . $data['promocode'] . $data['dt'] . $key);

        if ($hash !== $data['hash']) {
            Log::error('wrong hash');
            return response('wrong hash', 500);
        }

        /** @var MUser $user */
        $user = MUser::findOrFail($request->user_id);
        Auth::login($user);
        Localization::setAppLocale($user->getLocale());
        return $this->activate($request);
    }

}
