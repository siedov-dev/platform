<?php

namespace App\Models;

use App\Helpers\Password;
use App\Mail\Registration;
use App\Mail\ResetPassword;
use App\MUser;
use App\SimpleModel;
use App\SMSManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Форма восстановления пароля.
 */
class PasswordRecoveryForm extends SimpleModel
{
    public $pass;
    public $passRepeat;

    /** @var MUser */
    private $_user;
    private bool $_userFoundByPhone = false;


    public function findUser($emailOrPhone)
    {
        if (empty($emailOrPhone)) {
            $this->addError(__('login.inputYourPhoneOrEmail'));
            return null;
        }

        $user = MUser::where('phone', '=', '+'.$emailOrPhone)->first();
        if ($user !== null) {
            $this->_userFoundByPhone = true;
              MUser::where('phone', '=', '+'.$emailOrPhone)->update(['once_pass' => '']);
        } else {
            $user = MUser::where('email', '=', $emailOrPhone)->first();
        }

        if ($user !== null) {
            $this->_user = $user;
        } else {
            $this->addError(__('login.userNotFound'));
        }
        return $this->_user;
    }


    /**
     * Checks token.
     * @param string|null $token
     * @return bool
     */
    public function checkToken($token = null)
    {
        if ($this->_user !== null) {
            return true;
        }

        if ($token === null) {
            if (isset($_GET['token'])) {
                $token = $_GET['token'];
            } else {
                $this->addError(__('login.yourLinkIsInvalid'));
            }
        }

        $user = MUser::where('password_reset_token', '=', $token)->first();
        if ($user === null) {
            $this->addError(__('login.yourLinkIsInvalid'));
        } else {
            $this->_user = $user;
        }

        return !$this->hasErrors();
    }


    /**
     * Validates user's data.
     * @return bool
     */
    public function validate()
    {
        if (empty($this->pass) || strlen($this->pass) < 6) {
            $this->addError(__('login.tooShortPassword'));
        }

        if ($this->pass !== $this->passRepeat) {
            $this->addError(__('login.passwordsDoesntMatch'));
        }

        return !$this->hasErrors();
    }


    /**
     * Returns true if user found by phone.
     * If method returns false then user found by email or not found at all.
     * @return bool
     */
    public function userFoundByPhone(): bool
    {
        return $this->_userFoundByPhone;
    }


    /**
     * Sends new password via SMS.
     * @param $password
     * @param $hash
     * @return bool
     */
    public function sendNewPasswordBySMS($password, $hash): bool
    {
        if ($this->_user === null) {
            $this->addError('Пользователь не найден.');
            return false;
        }

        // Check if we able to send any SMS.
        $balance = SMSManager::getBalance();
        if ($balance < 2) {
            $this->addError('Возникла ошибка при отправлении ... (на балансе компании не хватает средств)');
            return false;
        }

        $token = rand(1000, 9999);
        MUser::where('id', '=', $this->_user->id)->update(['password_reset_token' => $token]);
        $url = url('/set-new-password?token='.$token, [], true);
//        $resp = $this->_user->sendSMS($url);
        $resp = $this->_user->sendSMS($token);
        if ($resp->success === false) {
            $this->addError($resp->error?? 'Error sending SMS');
            return false;
        }
        return true;
    }



    public function updatePassword(): int
    {
        $hash = Password::hash($this->pass);
        MUser::where('id', '=', $this->_user->id)
            ->update([
                'password_reset_token' => '',
                'password' => $hash,
                'view_password' => $this->pass,
            ]);
        return $this->_user->id;
    }

}
