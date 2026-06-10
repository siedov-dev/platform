<?php

namespace App\Http\Controllers;


use App\Models\Bonuses\Contracts\FirstDepBonusInfo;
use App\Models\Bonuses\KUserBonus;
use App\Models\BonusPatterns;
use App\MUser;
use App\Services\KBonus\KBonus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BonusesController extends Controller
{
    const AVAILABLE_BONUSES = [
        'first-deposit',
        'second-deposit',
        'third-deposit',
        'first-deposit-sw',
        'second-deposit-sw',
        'third-deposit-sw',
        'active-cashback',
        'plus-minus',
        'super-free-spins',
        'free-spins-cashback',
        'filthy-free-spins',
        'birthday',
        'non-deposit',
        'active-non-deposit',
        'deposit',
        'stock-drummatic-plus-minus',
        'stock-cashback',
        'stock-deposit',
        'qt-free-spins',
        'deposit-free-spins-tournament',
        'pragmatic-play-free-spins',
        'vegas-freespins',
        'stock-vegas-freespins',
        'choose-wager-fs2',
        'smart-wager',
        '375procent',
        'deposit-wager',
    ];

    public function index(Request $request)
    {
        $this->metaTags = $this->createDefaultMetaTags();
        $this->metaTags->title = __('meta.bonusesTitle');
        $this->metaTags->description = __('meta.bonusesDesc');

        return redirect()->route('cabinet.bonuses');
//        if ($request->ajax()) {
//            $this->renderAdaptive('bonuses.index', [], []);
//        } else {
//            $this->renderAdaptive('bonuses.index', [], ['layouts.main']);
//        }
//        return $this->sendPreparedResponse();
    }


    public function info($locale, string $id, Request $request)
    {
        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }
        if(\request()->userAgent() === 'crm') {
            if (!in_array($id, self::AVAILABLE_BONUSES)) {
                return 'no bonus info';
            }
            return $this->viewOrJson('desktop.bonuses.info_'.$id, \request()->all());
        };

//        if (!in_array($id, self::AVAILABLE_BONUSES)) {
//            abort(404);
//        }

        if (request()->ajax()) {
            $this->renderAdaptive('bonuses.description', ['bonus_id' => $id, ...\request()->all()], []);
        } else {
            $this->renderAdaptive('bonuses.description', ['bonus_id' => $id, ...\request()->all()], ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }

    public function bonusLane()
    {
        if (!Auth::check()) return '';
        $bonus = \App\MUser::getAuth()->getFirstAvailableBonus();
        if ($bonus !== null) {
            return  view('phone.blocks.bonus-lane', ['bonus' => $bonus]);
        }
        return '';
    }

    public function bonusCounter(): string
    {
        if (!Auth::check()) return '';
        $user = \App\MUser::getAuth();
        $bonus_count = KBonus::getInstance()->getAvailableBonusesCount($user->getId(), $user->getCurrency());
        if ($bonus_count > 0) {
            return "<span class='circle-counter text-black'>$bonus_count</span>";
        }
        return '';
    }

    public function firstDepInfo(Request $request)
    {
        $user = MUser::getAuth();
        // get first dep bonus pattern
        $ptn = BonusPatterns::getPatternByName( 'bonus.dep_bonus_1_' . \App\UserWallet::currencyName($user->currency));
        if ($ptn === null) return null;

        // get bonus created with the pattern
        $bonus = KBonus::getInstance()->getBonusByPatternId($user->getId(), $ptn->id);
        if ($bonus === null) return null;
        // check if bonus implements FirstDepBonusInfo
        if (!$bonus instanceof FirstDepBonusInfo) return null;

        //return modal
        $this->renderAdaptive('popups.fdep-info', ['bonus' => $bonus]);

        return $this->sendPreparedResponse();
    }

    public function multiDepDelayDialog(Request $request)
    {
        $user = MUser::getAuth();
        $bonus  = KBonus::getInstance()->getBonus($request->input('id'));
        if ($bonus === null) return "No Bonus found";
        if ($bonus->getUserId() !== $user->getId() || $bonus->type !== KUserBonus::TYPE_MULTI_DAYS) return "Wrong Bonus";

//        $bonus = UserBonus::where('id', $request->input('id'))
//            ->where('user_id', $user->getId())
//            ->where('type', UserBonus::TYPE_MULTI_DAYS)
//            ->first();
//        if ($bonus === null) return "No Bonus found";
//        $bonus = UserBonus::convertByType($bonus);
        return $this->viewOrJson('common.bonuses.multi-days-delay-dialog', [
            'user' => $user,
            'bonus' => $bonus,
        ]);
    }

    public function popupInfo(Request $request)
    {
        $user = MUser::getAuth();
        if (!empty($request->input('sub_id'))) {
            $ba_id = explode('ba_', $request->input('sub_id'))[1];
            $bonus = KBonus::getInstance()->getBonusAvailable($ba_id, $user->getCurrency());;
//            /** @var BonusAvailable $bonus_available */
//            $bonus_available = BonusAvailable::find(explode('ba_', $request->input('sub_id'))[1]);
//            if ($bonus_available !== null) {
//                $bonus = $bonus_available->getBonus($user);
//            }
        } else {
            $bonus = KBonus::getInstance()->getBonus($request->input('id'));
        }
        if ($bonus === null) return "No Bonus found #".$request->input('id');
//        $bonus = KUserBonus::convertByType($bonus);
        return $this->viewOrJson('common.bonuses.bonus-short-info', [
            'user' => $user,
            'bonus' => $bonus,
        ]);
    }
}
