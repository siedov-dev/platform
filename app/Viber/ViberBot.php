<?php


namespace App\Viber;


use App\Telegram\Models\TelegramUser;
use App\Viber\Models\ViberStat;
use App\Viber\Models\ViberUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ViberBot
{
    protected MessageParser $parser;

    public function __construct()
    {
        $this->parser = new MessageParser($this);
    }

    public function onMessage(ViberUser $v_user, $update)
    {
        Log::channel('viber')->info(' user#' . $v_user->viber_id);
        try {

            $message_text = $update['message']['text']?? '';
            Log::channel('viber')->info(' message:' . $message_text);

            //check for commands
            if ($this->parser->parseMessage($v_user, $message_text, $update)) {
                ViberStat::incCommands();
                return;
            }

            if ($v_user->status == ViberUser::STATUS_NOT_CONFIRMED && $v_user->getReqStatus() == ViberUser::REQ_STATUS_CONFIRM_PHONE_CODE) {

                if (preg_match('#^\d{4}$#', $message_text)) {
                    if ($v_user->phone_confirm_code === $message_text) {
                        $v_user->status = ViberUser::STATUS_CONFIRMED;
                        $v_user->setReqStatus(ViberUser::REQ_STATUS_NONE);
                        $v_user->save();
                        $m_user = $v_user->getMUser();
                        $m_user->once_pass = null;
                        if (empty($m_user->email) && !empty($v_user->email)) {
                            $m_user->email = $v_user->email;
                        }
                        $m_user->save();
                        $v_user->sendText('✅ Ваш аккаунт подключен.');
                        ViberStat::incConnected();
                        return;
                    }
                }

                $v_user->sendText("Не верный код подтверждения!");
            }

            if ($v_user->status == ViberUser::STATUS_NOT_CONFIRMED && $v_user->getReqStatus() == ViberUser::REQ_STATUS_CONFIRM_EMAIL_CODE) {

                if (preg_match('#^\d{8}$#', $message_text)) {
                    if ($v_user->email_confirm_code === $message_text) {
                        $v_user->status = ViberUser::STATUS_CONFIRMED;
                        $v_user->setReqStatus(ViberUser::REQ_STATUS_NONE);
                        $v_user->save();
                        $m_user = $v_user->getMUser();
                        $m_user->email_verification_token = null;
                        if (empty($m_user->phone) && !empty($v_user->phone)) {
                            $m_user->phone = $v_user->phone;
                        }
                        $m_user->save();
                        $v_user->sendText('✅ Ваш аккаунт подключен.');
                        ViberStat::incConnected();
                        return;
                    }
                }

                return $v_user->sendText("Не верный код подтверждения email!");
            }

            //email, phone check and send confirm connect user inline request
            if ($v_user->status == ViberUser::STATUS_NOT_CONFIRMED && $v_user->getReqStatus() == ViberUser::REQ_STATUS_GET_EMAIL) {
                $email = null;
                $phone = null;
                $reqs = null;

                if (preg_match('/^([a-zA-Z0-9_\-.]+)@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/', $message_text)) {
                    $email = $message_text;
                    $reqs = $email;
                }

                if (preg_match('/^(\+?)\d{10,}$/', $message_text)) {
                    $phone = $message_text;
                    $reqs = $phone;
                }

                if (!empty($update['message']['contact']['phone_number'])) {
                    $phone = $update['message']['contact']['phone_number'];
                    $reqs = $phone;
                }

                if ($email === null && $phone === null) {
                    $v_user->sendText('Введен неверный email или номер телефона');
                    $v_user->setReqStatus(TelegramUser::REQ_STATUS_NONE);
                    return;
                }

                $v_user->setReqStatus(ViberUser::REQ_STATUS_CONFIRM_EMAIL);
                $v_user->save();

                $v_user->sendText("Привязать аккаунт" . ($phone !== null ? " \u{1F4F1}:" : "\u{1F4E8}:.") . $reqs . "?", $reqs);
                return;
            }

            $v_user->sendText('Для продолжения воспользуйтесь контекстным меню.');
            ////
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function update(Request $request)
    {
        try {
            $update = $request->all();
            Log::channel('viber')->info(var_export($update, true));

            if (!empty($update['event']) && $update['event'] === 'conversation_started') {
                $v_user = ViberUser::getFromData($update['user']);
                $v_user->sendText(' Добро пожаловать! Для продолжения воспользуйтесь контекстным меню.');
            }

            if (!empty($update['sender'])) {
                $sender = $update['sender'];
                /** @var ViberUser $v_user */
                $v_user = ViberUser::getFromData($sender);
                $bot = new ViberBot();
                $bot->onMessage($v_user, $update);
            }
        } catch (\Exception $e) {
            Log::channel('viber')->error($e->getMessage());
        }
        return 'ok';
    }
}
