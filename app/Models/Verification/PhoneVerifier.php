<?php

namespace App\Models\Verification;

use App\MUser;
use App\SimpleModel;

class PhoneVerifier extends SimpleModel
{
    /** @var MUser|null */
    private $_user;

    /**
     * Finds user with given token and reset it.
     * Empty token means that email was verified.
     * @param string $token
     * @return bool
     */
    public function useToken(string $token)
    {
        $this->_user = MUser::where(['once_pass' => $token])->first();
        if ($this->_user === null) {
            $this->addError('Неверный код подтверждения.');
            return false;
        }

        $this->_user->once_pass = '';
        return true;
    }

    /**
     * Returns user that was found by given token.
     * If no user found or token was not used yet - null will be returned.
     * @return MUser|null
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Writes changes to the database.
     * @return bool
     */
    public function updateUser()
    {
        if ($this->_user === null) return false;
        $res = $this->_user->save();
        if (!$res) {
            $this->addError('Во время вашего запроса произошла ошибка. Повторите попытку позже.');
        }
        return $res;
    }
}
