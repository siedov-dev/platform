<?php

namespace App\Http\Controllers;



use App\ApplicationForPayment;
use App\Helpers\Hlog;
use App\Helpers\Password;
use App\Models\Bonuses\UserBonus;
use App\Models\UserGift;
use App\Models\UserLock;
use App\MUser;
use App\Services\KPay\KPay;
use App\SMSManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CabinetController extends Controller
{
    public function account(Request $request)
    {
        if ($request->ajax()) {
            $this->renderAdaptive('cabinet.account', [], $this->getLayouts());
        } else {
            \session()->flash('cabinetShow', 'account');
            $this->renderAdaptive('landing.index', [], ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }

    public function sportHistory(Request $request)
    {
        if ($request->ajax()) {
            $this->renderAdaptive('cabinet.sport', [], $this->getLayouts());
        } else {
            \session()->flash('cabinetShow', 'sport');
            $this->renderAdaptive('landing.index', [], ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }


    public function transactions(Request $request)
    {
        if ($request->ajax()) {
            $this->renderAdaptive('cabinet.transactions', [], $this->getLayouts());
        } else {
            \session()->flash('cabinetShow', 'transactions');
            $this->renderAdaptive('landing.index', [], ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }


    public function tournaments(Request $request)
    {
        if ($request->ajax()) {
            $this->renderAdaptive('cabinet.tournaments', [], $this->getLayouts());
        } else {
            \session()->flash('cabinetShow', 'tournaments');
            $this->renderAdaptive('landing.index', [], ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }


    public function bonuses(Request $request)
    {
        $user = MUser::getAuth();
        $user->clearRedisValue('has_new_bonus');

        $bonuses = UserBonus::where('user_id', Auth::id())
            ->where('currency', $user->getCurrency())
//            ->orderBy('pattern_id', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $gifts = null;
//        $gifts = UserGift::where('user_id', Auth::id())
//            ->whereNotIn('status', [UserGift::STATUS_CANCELED, UserGift::STATUS_EXPIRED])
//            ->get();

        if ($request->ajax()) {
            return $this->renderPage('bonuses', ['bonuses' => $bonuses, 'gifts' => $gifts]);
        } else {
            \session()->flash('cabinetShow', 'bonuses');
            $this->renderAdaptive('landing.index', [], ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }


    public function ranks(Request $request)
    {
        if ($request->ajax()) {
            $this->renderAdaptive('cabinet.ranks', [], $this->getLayouts());
        } else {
            $this->renderAdaptive('landing.index', [], ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }


    /**
     * @see ReferralController::cabinet
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function partnership(Request $request)
    {
        if ($request->ajax()) {
            $this->renderAdaptive('cabinet.partnership', [], $this->getLayouts());
        } else {
            \session()->flash('cabinetShow', 'partnership');
            $this->renderAdaptive('landing.index', [], ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }


    public function settings(Request $request)
    {
        if ($request->ajax()) {
            $this->renderAdaptive('cabinet.settings', [], $this->getLayouts());
        } else {
            \session()->flash('cabinetShow', 'settings');
            $this->renderAdaptive('landing.index', [], ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }


    public function changePassword(Request $request)
    {
        /** @var MUser $user */
        $user = Auth::user();

        $newPass  = $request->post('new_password', '');
        $newPass2 = $request->post('new_password_confirmation', '');
        $oldPass  = $request->post('old_password', '');

        if (empty($newPass)) return response(__('cabinet.inputNewPassword'), 400);
        if (strlen($newPass) < 6) return response(__('cabinet.tooShortPassword'), 400);
        if ($newPass !== $newPass2) return response(__('cabinet.passwordsAreDifferent'), 400);
        if (empty($oldPass)) return response(__('cabinet.inputOldPassword'), 400);
        if (Password::hash($oldPass) !== $user->password) return response(__('cabinet.invalidPassword'), 400);

        $user->password = Password::hash($newPass);
        $user->save();

        return response(__('cabinet.passwordChanged'));
    }


    public function cancelPayout(Request $request)
    {
        $user = MUser::getAuth();
        $result = KPay::getInstance()->payoutCancelApplication($user->getId(), $request->post('id'));
        if ($result->isSuccess()) return $this->transactions($request);
        return response($result->getError(), 500);
//        try {
//            DB::beginTransaction();
//            /** @var ApplicationForPayment $app */
//            $app = \App\ApplicationForPayment::where('id', $request->post('id'))
//                ->where('user_id', Auth::id())
//                ->where('confirmed', ApplicationForPayment::STATUS_PENDING)
//                ->lockForUpdate()
//                ->first();
//
//            if ($app) {
//                $app->confirmed = ApplicationForPayment::STATUS_CANCELED;
//                $app->comentar = 'Отменено пользователем';
//                $app->save();
//                $user->getWallet($app->currency)->addMoney($app->money);
//            } else {
//                return response('Transaction not found', 400);
//            }
//        } catch (\Exception $e) {
//            Hlog::logException($e);
//            DB::rollBack();
//        } finally {
//            DB::commit();
//        }
//        return response('Server error', 500);
    }



    private function getLayouts()
    {
        return empty(request()->get('layout', null)) ? [] : ['cabinet.index'];
    }


    public function renderPage(string $page, array $params = [])
    {
        if (request()->ajax()) {
            $this->renderAdaptive('cabinet.'.$page, $params, $this->getLayouts());
        } else {
            $this->renderAdaptive('landing.index', [], ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }


    public function changeLogin(Request $request)
    {
        $newLogin = substr(trim($request->get('login', '')), 0, 16);
        if (empty($newLogin)) return response("Empty login", 400);

        /** @var MUser $user */
        $user = Auth::user();

        if ($user->login !== $newLogin) {
            $existingUser = MUser::where('login', $newLogin)->first();
            if ($existingUser !== null) {
                return response(__('cabinet.loginAlreadyTaken'). $newLogin, 400);
            }
        }

        $user->login = $newLogin;
        $user->save();
        return response(__('cabinet.loginChangedSuccessfully'));
    }

    public function selfLock(Request $request)
    {
        if ($request->isMethod('POST')) {
            $user = MUser::getAuth();
            UserLock::lockUser($user, $request->input('days'));
            return redirect()->route('logout');
        }

        $this->renderAdaptive('cabinet.self-lock-modal', ['days' => $request->input('days')], []);
        return $this->sendPreparedResponse();
    }
}
