<?php

namespace App\Models;

use App\Email;
use App\Http\Controllers\SmsController;
use App\MUser;
use App\SimpleModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\SMSManager;

/**
 * @deprecated
 */
class ContactsVerifier extends SimpleModel
{
    const TYPE_SMS = 1;
    const TYPE_EMAIL = 2;

    public $phone;
    public $email;

    private $_verificationType;
    private $_user;

    function __construct(MUser $user = null)
    {
        $this->_user = $user;
    }

    public function setUser(MUser $user) {
        $this->_user = $user;
    }

    /**
     * Validates data given by user.
     * @return bool
     */
    public function validate() {
        if ($this->isVerified()) {
            $this->addError('Вы уже подтвердили ваши контактные данные.');
        }

        if (empty($this->phone) && empty($this->email)) {
            $this->addError('Введите номер телефона или email.');
        }

        return !$this->hasErrors();
    }

    /**
     * Returns true if user's contact info already verified.
     * @return bool
     */
    public function isVerified()
    {
        return (!empty($this->_user->phone) || !empty($this->_user->email)) && empty($this->_user->email_verification_token) && empty($this->_user->once_pass);
    }

    /**
     * Returns verification type.
     * @return int
     */
    public function getVerificationType() {
        if ($this->_verificationType === null) {
            return empty($this->phone) ? self::TYPE_EMAIL : self::TYPE_SMS;
        }
        return $this->_verificationType;
    }

    /**
     * Sends verification message via SMS or email.
     * @return bool Returns true if sending was successful.
     */
    public function sendVerificationMessage() {
        Log::info('Sending verification message...');
        if (!empty($this->phone)) {
            $code = Str::random(6);
            $this->_user->once_pass = $code;
            if ($this->_user->save()) {
                $this->_verificationType = self::TYPE_SMS;
                return $this->sendPhoneVerificationCode($code);
            }
        }

        if (!empty($this->email)) {
            $token = Str::random(16);
            $this->_user->email_verification_token = $token;
            if ($this->_user->save()) {
                $this->_verificationType = self::TYPE_EMAIL;
                return $this->sendEmailVerificationLink($token);
            }
        }

        $this->addError('Ошибка при отправке кодаподтверждения.');
        Log::error('Поля телефона и email пустые. Убедитесь, что вызываете метод validate() перед отправкой сообщения.');
        return false;
    }

    /**
     * Sends SMS with phone verification link.
     * @param $code
     * @return bool
     */
    private function sendPhoneVerificationCode($code)
    {
        Log::info('Sending verification code via SMS...');
        if (substr($this->phone, 0, 1) === '+') {
            // Send SMS with verification code.
            $balance = SMSManager::getBalance();
            if ($balance < 2) {
                $this->addError('Ошибка при отправке SMS с кодом подтверждения.');
                Log::error('Недостаточно денег для отправки SMS.');
                return false;
            }
            $resp = SMSManager::sendSMS($this->phone, 'https://'.$_SERVER['SERVER_NAME'].'/verify-contact-info?token='.$code);

            if ($resp->success === false) {
                $this->addError(empty($resp->error)? 'Error sending SMS' : $resp->error);
                return false;
            }
        } else {
            $this->addError('Мы не можем выслать СМС на указанный номер телефона. Пожалуйста, используйте электронную почту для регистрации.');
            return false;
        }
        return true;
    }

    /**
     * Sends email verification link.
     * @param string $token
     * @return bool
     */
    private function sendEmailVerificationLink($token)
    {
        $letter = new Email([
            'to' => $this->email,
            'subject' => 'Подтверждение email',
            'text' => 'Для подтверждения перейдите по ссылкe https://'.$_SERVER['SERVER_NAME'].'/verify-email?token='.$token,
            'html' => '
                <html>
                <head>
                  <title>Подтверждение email</title>
                </head>
                <body>
                    Для подтверждения перейдите по ссылкe <a href="https://'.$_SERVER['SERVER_NAME'].'/verify-contact-info?token='.$token.'">Подтвердить email</a>
                </body>
                </html>
                ',
        ]);
        $res = $letter->send(true, true);
        if (!$res) {
            $this->addError('Мы не смогли отправить вам email. Повторите попытку позже.');
        }
        return $res;
    }


    /**
     * Verifies phone using given code from SMS.
     * @param $code
     * @return bool
     * @see getError()
     */
    public function verifyByCodeFromSms($code) {
        $this->_user = MUser::where('once_pass', '=', $code)->first();

        if ($this->_user === null) {
            $this->addError('Ваша ссылка не валидна.');
            return false;
        }

        $this->_user->once_pass = '';
        if ($this->_user->save()) {
            return true;
        }

        $this->addError('Ошибка при обработке запроса. Пожалуйста, повторите попытку позже.');
        return false;
    }

    /**
     * Verifies email using token from email.
     * @param $token
     * @return bool
     */
    public function verifyByTokenFromEmail($token) {
        $this->_user = MUser::where('email_verification_token', '=', $token)->first();

        if ($this->_user === null) {
            $this->addError('Ваша ссылка не валидна.');
            return false;
        }

        $this->_user->email_verification_token = '';
        if ($this->_user->save()) {
            return true;
        }

        $this->addError('Ошибка при обработке запроса. Пожалуйста, повторите попытку позже.');
        return false;
    }
}
