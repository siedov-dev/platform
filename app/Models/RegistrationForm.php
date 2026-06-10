<?php

namespace App\Models;

use App\Helpers\Password;
use App\Http\Controllers\AuthController;
use App\Models\UserReferrals\UserReferral;
use App\MUser;
use App\SimpleModel;
use App\UserWallet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;

/**
 * Форма регистрации
 * @deprecated
 */
class RegistrationForm extends SimpleModel
{
    public $nickname;
    public $password;
    public $password_repeat;
    public $email;
    public $phone;
    public $bonus_type;
    public $agree_with_tou;
    public $subscription;
    public $country;
    public $currency;
    public $referer = null;

    /** @var MUser $_user */
    private $_user;


    /**
     * Returns newly created user.
     * @return MUser
     */
    public function getUser():?MUser
    {
        return $this->_user;
    }



    /**
     * Validates loaded data.
     * @return bool
     */
    public function validate()
    {
        if (empty($this->nickname)) $this->addError('Введите ник.');
//        if (mb_strlen($this->nickname) < 6) $this->addError('Ник должен содержать минимум 6 символов.');

        if (!empty($this->email)) {
//            $this->addError('Введите email.');
            if (!preg_match('/^([a-zA-Z0-9_\.-]+)@([a-zA-Z0-9_\.-]+)\.([a-zA-Z\.]{2,6})$/', trim($this->email))) $this->addError('Неверный email');
        }

        if (empty($this->password))  $this->addError('Введите пароль.');
        if (mb_strlen($this->password) < 6) $this->addError('Пароль содержать минимум 6 символов.');
//        if ($this->password !== $this->password_repeat) $this->addError('Пароли не совпадают.');

        if (empty($this->currency)) $this->addError('Выберите валюту');

        if (MUser::where(['login' => trim($this->nickname)])->first() !== null) {
            $this->addError('Пользователь с таким логином уже зарегистрирован.');
        }

        if (!empty($this->email) && MUser::where(['email' => trim($this->email)])->first() !== null) {
            $this->addError('Пользователь с таким email уже зарегистрирован.');
        }

        return !$this->hasErrors();
    }


    /**
     * Attempts to create account for the client.
     */
    public function createAccount()
    {
        // Create new user model.
        $user = new MUser();

        Log::info('create account with Registarion form');

        if (!empty($this->email) && MUser::where(['email' => trim($this->email)])->first() !== null) {
            $this->addError(__('reg.email_already_exists'));
            return false;
        }

        // Assign some values.
        $user->login = $this->nickname;
        $user->password = Password::hash($this->password);
        $user->view_password = $this->password;
        $user->currency = intval($this->currency);
        $user->hall_id = UserWallet::currencyHallId($this->currency);
        $user->pid = $this->checkPartner();
        $user->email = $this->email;
        $user->phone = $this->phone;
        $user->postback_params_json = $this->postbackParams();

        $this->_user = $user;


        $user->country = $this->country;

        if(!empty(session('uref'))){
            $ref = UserReferral::where('ref_code', session('uref'))->first();
            if($ref){
                $user->ref_id = $ref->id;
            }
        }

        if (!$this->hasErrors() && $user->save()) {

            // Notify c3pa partners about registration.
//            if (!empty($user->pid) && $user->pid == 125) {
//                $publisher_id = Cookie::get('source', '');
//                file_get_contents('http://offers.c3pa.net/postback?clickid='.$user->id.'&status=2&sum=0'.(empty($publisher_id) ? '' : '&publisher_id='.$publisher_id));
//            }

            //
//            if($user->ref_id){
//                /** @var UserReferral $ref */
//                $ref = UserReferral::find($user->ref_id);
//                if($ref) {
//                    $ref->onRegister($user);
//                }
//            }

            $this->createBonus();

//            $this->addToRegTournament();

            //create bonus group status
//            \App\Models\BonusGroup::createForUser($user);

            Cookie::queue(Cookie::forget('clickId'));

            return $user;
        }

        return false;
    }



    /**
     * Creates corresponding bonuses for the user.
     */
    private function createBonus()
    {
        AuthController::createBonus($this->_user);
    }

    /**
     * Check for partner cookie from url. Add referral parent.
     * @return int
     */
    private function checkPartner(){

        $partnerUrl = Cookie::get('referral');

        if(!empty($partnerUrl)) {
            $partner = DB::connection('partners')
                ->table('users')
                ->where(['invitation_token' => $partnerUrl])
                ->first();

            if ($partner !== null) {
                return $partner->id;
            }
        }

        return 0;
    }

    public function postbackParams()
    {
        $clickId = Cookie::get('clickId');
        $uid = Cookie::get('uid');
        $sid = Cookie::get('sid');
        if (!empty($clickId)) {
            return json_encode([
                'clickId' => $clickId,
                'uid' => $uid,
                'sid' => $sid,
                'updatedAt' => time(),
            ]);
        }
        return null;
    }
}
