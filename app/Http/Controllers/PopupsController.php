<?php

namespace App\Http\Controllers;

use App\Helpers\Device;
use App\Helpers\Hlog;
use App\Models\Bonuses\KUserBonus;
use App\Models\Bonuses\Types\BonusMarker;
use App\Models\BonusPatterns;
use App\Models\Popups\CustomPopup;
use App\Models\Popups\PopupUnsubscribe;
use App\Models\Popups\UserPopup;
use App\MUser;
use App\Services\KBonus\KBonus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class PopupsController extends Controller
{
    const TIMEOUT = 5; //minimum seconds between popups

    public static function getNew()
    {
        if (!Auth::check()) return null;
        $timeout_to = Redis::get(Auth::id() . ':popups:timeout');
        if ($timeout_to > now()->format('Y-m-d H:i:s')) return null;

        Redis::set(Auth::id() . ':popups:timeout', now()->addSeconds(self::TIMEOUT)->format('Y-m-d H:i:s'));
        return UserPopup::getNew(MUser::getAuth())?->getContent();
    }

    public function unsubCategory(Request $request)
    {
        if (!Auth::check()) return null;
        return PopupUnsubscribe::firstOrCreate(['user_id' => Auth::id(), 'category' => $request->post('category', 'default')]);
    }

    public function setTimeout(Request $request)
    {
        if (!Auth::check()) return null;
        UserPopup::setTimeout(Auth::id(), $request->input('seconds', self::TIMEOUT));
    }

    public function prolongTimeout(Request $request)
    {
        if (!Auth::check()) return null;
        UserPopup::prolongTimeout(Auth::id(), $request->input('seconds', self::TIMEOUT));
    }

    public function regPopup(): ?string
    {
        if (!Auth::check()) return null;
        $user = MUser::getAuth();

        try {
            $user_bonuses = KBonus::getInstance()->getUserBonuses($user->getId(), $user->getCurrency());
            $bonus = null;
            //welcome bonus pack
            foreach ($user_bonuses as $ubonus) {
                if ($ubonus->marker === BonusMarker::RegBonus->value) $bonus = $ubonus;
            }
//            //welcome bonus pack
//            $bonus = UserBonus::where('marker', BonusMarker::RegBonus->value)
//                ->where('user_id', $user->getId())
//                ->first();
            if ($bonus === null) return null;
//            $bonus = UserBonus::convertByType($bonus);

            if ($bonus->type === KUserBonus::TYPE_PS_SFS_DYNAMIC) {
                return $this->viewOrJson(Device::detect().'.popups.fdep',[
                    'user' => $user,
                    'bonus' => $bonus,
                ]);
            }

            $reg_ptn_ids = BonusPatterns::getRegPatternIds($bonus->curr_name);
            $users_fd_bonuses = [];
            foreach ($user_bonuses as $ubonus) {
                if (in_array($ubonus->pattern_id, $reg_ptn_ids)) $users_fd_bonuses[$ubonus->pattern_id] = $ubonus;
            }
            krsort($users_fd_bonuses);
            $users_fd_bonuses = array_values($users_fd_bonuses);
//            $users_fd_bonuses = \App\Models\Bonuses\UserBonus::where('user_id', $user->getId())
//                ->whereIn('pattern_id', BonusPatterns::getRegPatternIds($bonus->curr_name))
//                ->orderBy('pattern_id')
//                ->get();

            return $this->viewOrJson('common.bonuses.wb-pack-deposit',[
                'user' => $user,
                'bonus' => $bonus,
                'users_fd_bonuses' => $users_fd_bonuses,
            ])->render();
        } catch (\Exception $e) {
            Hlog::logException($e);
        }

        return null;
    }

    public function authPopup(): ?string
    {
        if (Auth::check()) return null;

        try {
            /** @var CustomPopup $popup */
            $popup = CustomPopup::where('category', 'auth')->first();
            return $popup?->render();

        } catch (\Exception $e) {
            Hlog::logException($e);
        }

        return null;
    }
}
