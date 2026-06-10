<?php


namespace App\Viber\Commands;


use App\MUser;
use App\Viber\Models\ViberUser;
use App\Viber\ViberBot;

class CommandConfirmLogin implements CommandInterface
{
    private ViberBot $bot;

    public function __construct(ViberBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process($params, ViberUser $v_user, $update)
    {
        $data = $update['message']['tracking_data']??'';
        if (preg_match('/^([a-zA-Z0-9_\-.]+)@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/', $data)) {

            /** @var MUser $m_user */
            $m_user = MUser::where('email', $data)->first();
            if (!$m_user) {

                $v_user->email = $data;
                $v_user->save();

                $v_user->setReqStatus(ViberUser::REQ_STATUS_NONE);
                $v_user->sendText('Введенный email не найден ❌');
                return;
            }

            $v_user_ex = ViberUser::where('user_id', $m_user->id)->where('viber_id', '!=', $v_user->viber_id)->first();
            if ($v_user_ex !== null) {
                $v_user->setReqStatus(ViberUser::REQ_STATUS_NONE);
                $v_user->sendText('Введенный email зарегистрирован другим пользователем ❌');
                return;
            }

//                        if (!$m_user->isEmailVerified()) {
//                            $this->sendMsg($user, $t_user, 'email не верифицирован. Пройдите верификацию перед подключением пользователя.');
//                            return;
//                        }


            $v_user->user_id = $m_user->id;
            $v_user->setReqStatus(ViberUser::REQ_STATUS_CONFIRM_EMAIL_CODE);
            $v_user->email = $data;
            $v_user->save();

            $v_user->sendEmailConfirmCode();

            $v_user->sendText('Введите код верификации, высланный на указаный email');
            return;
        }

        if (preg_match('/^(\+?)(\d{10,})$/', $data, $match)) {
            $phone = $match[2];

            /** @var MUser $m_user */
            $m_user = MUser::where('phone', $phone)->first();
            if (!$m_user) $m_user = MUser::where('phone', '+' . $phone)->first();

            if (!$m_user) {
                $v_user->phone = $phone;
                $v_user->save();

                $v_user->setReqStatus(ViberUser::REQ_STATUS_NONE);
                $v_user->sendText('Введенный номер телефона не найден ❌');
                return;
            }

            $v_user_ex = ViberUser::where('user_id', $m_user->id)->where('viber_id', '!=', $v_user->viber_id)->first();
            if ($v_user_ex !== null) {
                $v_user->setReqStatus(ViberUser::REQ_STATUS_NONE);
                $v_user->sendText('Введенный номер телефона зарегистрирован другим пользователем ❌');
                return;
            }

//                        if (!$m_user->isPhoneVerified()) {
//                            $this->sendMsg($user, $t_user, 'Номер телефона  не верифицирован. Пройдите верификацию перед подключением пользователя.');
//                            return;
//                        }

            $v_user->user_id = $m_user->id;
            $v_user->setReqStatus(ViberUser::REQ_STATUS_CONFIRM_PHONE_CODE);
            $v_user->phone = $phone;
            $v_user->save();

            $v_user->sendPhoneConfirmCode();

            $v_user->sendText('Введите код подтверждения, высланный на указаный номер телефона');
            return;
        }

        $v_user->setReqStatus(ViberUser::REQ_STATUS_NONE);
        $v_user->sendText('Error: wrong data'.$data);
    }
}
