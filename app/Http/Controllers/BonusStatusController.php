<?php

namespace App\Http\Controllers;

use App\Services\Users\UserManager;
use App\MUser;
use Illuminate\Support\Facades\Auth;

class BonusStatusController extends Controller
{
    public function updateBonusPad()
    {

        if (!Auth::check()) return null;

        /** @var MUser $user */
        $user = MUser::find(Auth::id());
        UserManager::bonusUpdateStatusPad($user->id);

//        $balance = $user->getBalance();

//        if ($user->balance_mode == MUser::BALANCE_MODE_BONUS) {
//            $status = UserManager::getActiveBonusState($user);
////            Log::info('status', $status);
//            if (!empty($status) && $status['cd'] === 200) {
//                if (empty($status['bid'])) return ['status' => KUserBonus::STATUS_DONE];
//                $sp = KBonus::getInstance()->bonusStatusPad($status['bid']);
////                Log::info('sp', $sp);
//                if ($sp['sp']['bonus_status'] != KUserBonus::STATUS_ACTIVE) return [
//                    'balance' => $balance,
//                    'status' => KUserBonus::STATUS_DONE];
//                if ($sp['sp']['show_pad']) {
//                    return [
//                        'show_status' => empty($sp['sp']['value'])?"" : __('bonus.' . $sp['sp']['value']) .($sp['sp']['progress']),
////                        'show_status' => UI::bonusPad(
////                            __('bonus.' . $sp['sp']['title']), empty($sp['sp']['value'])?"" : __('bonus.' . $sp['sp']['value'])) .($sp['sp']['progress']),
//                        'balance' => $balance,
//                        'progress_prc' => $sp['sp']['progress_prc']?? 0,
//                        'status' => 0,
//                    ];
//                } else {
//                    return [
//                        'balance' => $balance,
//                        'status' => 0,
//                    ];
//                }
//            }
//
//            return [
//                'balance' => $balance,
//                'status' => KUserBonus::STATUS_DONE,
//            ];
//        }
//
//        return [
//            'balance' => $balance,
//            'status' => 0,
//        ];
    }
}
