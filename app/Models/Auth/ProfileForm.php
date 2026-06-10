<?php

namespace App\Models\Auth;

use App\Email;
use App\Mail\EmailVerification;
use App\MUser;
use App\SimpleModel;
use App\SMSManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProfileForm extends SimpleModel
{
    private $_user;
    private $_requiresPhoneVerification = false;
    private $_requiresEmailVerification = false;

    public $login;
    public $email;
    public $phone;
    public $telegramm;


    function __construct(MUser $user)
    {
        $this->_user = $user;
        $this->login = $user->login;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->telegramm = $user->telegramm;
    }


    public function validate()
    {
        // Check login.
        if (!empty($this->login) && $this->login !== $this->_user->login) {
            $userWithSameLogin = MUser::where('login', $this->login)->first();
            if ($userWithSameLogin !== null) {
                $this->addError('Такой логин уже занят.');
            }

            if(!preg_match('#[a-zA-Z0-9]{6,}#', $this->login)){
                $this->addError('Логин должен использовать только символы латинского алфавита и цифры минимальной длинной 6 символов');
            }
        }

        // Check email.
        if (!empty($this->email) && $this->email !== $this->_user->email) {
            $userWithSameEmail = MUser::where('email', $this->email)->first();
            if ($userWithSameEmail !== null) {
                $this->addError('Пользователь с таким email уже существует.');
            }
            if(!preg_match('/^([a-zA-Z0-9_\-.]+)@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/', $this->email)){
                $this->addError('Не правильный e-mail.');
            }
        }

        // Check phone;
        if (!empty($this->phone) && $this->phone !== $this->_user->phone) {
            $userWithSamePhone = MUser::where('phone', $this->phone)->first();
            if ($userWithSamePhone !== null) {
                $this->addError('Пользователь с таким номером телефона уже существует.');
            }

            if(!preg_match('#^\+[0-9]{10,}$#', $this->phone)){
                $this->addError('Не правильный номер телефона.');
            }
        }

        // telegramm
        if (!empty($this->telegramm) && $this->telegramm !== $this->_user->telegramm) {
            $userWithSameTm = MUser::where('telegramm', $this->telegramm)->first();
            if ($userWithSameTm !== null) {
                $this->addError('Пользователь с таким номером телеграмма уже существует.');
            }

            if(!preg_match('#^@.*$#', $this->telegramm)){
                $this->addError('Не правильный номер телеграмма.');
            }
        }
        return !$this->hasErrors();
    }


    public function updateUser()
    {
        // If email was changed - send email verification letter.
        if (!empty($this->email) && $this->_user->email !== $this->email) {
            try {
                $this->sendEmailVerificationMail();
                $this->_requiresEmailVerification = true;
                $this->_user->email = $this->email;
            } catch (\Exception $exception){
                $this->addError($exception->getMessage());
            }
        }

        // If phone was changed - send phone verification code.
        if (!empty($this->phone) && $this->_user->phone !== $this->phone) {
            try {
                $this->_user->phone = $this->phone;
                $this->sendPhoneVerificationSMS();
                $this->_requiresPhoneVerification = true;
            } catch (\Exception $exception){
                $this->addError($exception->getMessage());
            }
        }

        if (!empty($this->login)) {
            $this->_user->login = $this->login;
        }

        if (!empty($this->telegramm)) {
            $this->_user->telegramm = $this->telegramm;
        }

        if ($this->hasErrors()) {
            return false;
        }


        $res = $this->_user->save();
        if (!$res) {
            $this->addError('Во время бработки вашего запроса произошла ошибка. Повторите попытку позже.');
        }
        return $res;
    }


    private function sendEmailVerificationMail()
    {
//        $token = strtoupper(Str::random(8));
        $token = str_pad(random_int(0, 99999999), 8, '0');
        $this->_user->email_verification_token = $token;
        @Mail::to($this->email)->send(new EmailVerification($this->_user));

        DB::table('all_email_history')
            ->insert([
                'total' => 1,
                'sent' => 1,
                'not_sent' => 0,
                'title' => 'KrakenCasino - Подтверждение email',
                'text' => $token,
                'html' => $this->_user->login,
                'sender' => 'info@krakencassino.com',
                'created_at' => now()->format('Y-m-d H:i:s')
            ]);
        return true;
    }


    private function sendPhoneVerificationSMS()
    {
//        $code = strtoupper(Str::random(6));
        $code = str_pad(random_int(0, 9999), 4, '0');
        $this->_user->once_pass = $code;

            if (SMSManager::getBalance() < 2) {
                $this->addError('Ошибка при отправке SMS с кодом подтверждения. Повторите попытку позже.');
                return false;
            }
        $resp = $this->_user->sendSMS($code);

        if ($resp->success === false) {
            $this->addError($resp->error?? 'Error sending SMS');
            return false;
        }
        return true;
    }


    public function requiresPhoneVerification()
    {
        return $this->_requiresPhoneVerification;
    }

    public function requiresEmailVerification()
    {
        return $this->_requiresEmailVerification;
    }
}
