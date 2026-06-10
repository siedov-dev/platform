<?php

namespace App\Telegram\Models;

use App\Email;
use App\Mail\EmailVerification;
use App\MUser;
use App\SMSManager;
use App\Telegram\ReqState\ReqestUserState;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Class TelegramUser
 * @package App\Telegram
 * @property int id
 * @property int telegram_id
 * @property int user_id
 * @property string username
 * @property string first_name
 * @property string last_name
 * @property int status
 * @property string data
 * @property int $req_status
 * @property int $req_message_id
 * @property string $email
 * @property string $phone
 * @property string $phone_confirm_code
 * @property string $email_confirm_code
 * @property Carbon $last_send_at
 * @property string $lang
 * @property int $flags
 */
class TelegramUser extends Model
{
    protected $dates = ['last_send_at'];

    const STATUS_NOT_CONFIRMED = 0;
    const STATUS_REQUEST_REG_BONUS = 1;
    const STATUS_CONFIRMED = 2;

    const REQ_STATUS_NONE = 0;
    const REQ_STATUS_GET_EMAIL = 1; //wait for users email or phone number
    const REQ_STATUS_CONFIRM_EMAIL = 2; //inline confirm provided email|phone
    const REQ_STATUS_CONFIRM_PHONE_CODE = 3; //confirm sent phone number
    const REQ_STATUS_CONFIRM_EMAIL_CODE = 4; //confirm sent email
    const REQ_STATUS_CONFIG = 5; // config menu
    const REQ_STATUS_LANG_CHOOSE = 6; //select language

    const FLAG_GOT_SUBS_BONUS = 1;

    /**
     * get id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * get user telegram id
     * @return int
     */
    public function getTelegramId()
    {
        return $this->telegram_id;
    }

    /**
     * get MUser
     * @return MUser|null
     */
    public function getMUser(): ?MUser
    {
        return MUser::find($this->user_id);
    }

    /**
     * store some data in data filed
     * @param string $key
     * @param $value
     */
    public function setData(string $key, $value)
    {
        $data = json_decode($this->data, true);
        if (!$data) {
            $data = [];
        }
        $data[$key] = $value;
        $this->data = json_encode($data);
        $this->save();
    }

    /**
     * get data stored in data filed
     * @param string $key
     * @return mixed|null
     */
    public function getData(string $key)
    {
        $data = json_decode($this->data, true);
        if (!$data || !is_array($data) || !isset($data[$key])) {
            return null;
        }
        return $data[$key];
    }

    /**
     * set request status
     * @param int $status
     */
    public function setReqStatus(int $status)
    {
        $this->req_status = $status;
        $this->save();
    }

    /**
     * get request_status
     * @return int
     */
    public function getReqStatus(): ?int
    {
        return $this->req_status?? self::REQ_STATUS_NONE;
    }

    /**
     * current users request state
     * @return ReqestUserState
     */
    public function getState(): ReqestUserState
    {
        return ReqestUserState::getState($this);
    }

    /**
     * get get telegram menu markup
     * @return array
     */
    public function getUserMenu(): array
    {
        return $this->getState()->getUserMenu();
    }

    /**
     * return current user locale chosen in telegram bot or 'ru as default
     * @return string
     */
    public function getLocale(): string
    {
        return $this->lang?? 'ru';
    }

    /**
     * if user have got telegram subscribe bonus yet
     * @return bool
     */
    public function gotSubsBonus(): bool
    {
        return $this->hasFlag(self::FLAG_GOT_SUBS_BONUS);
    }

    /**
     * set user have got telegram subscribe bonus
     * @return void
     */
    public function setGotSubBonus(): void
    {
        $this->setFlag(self::FLAG_GOT_SUBS_BONUS);
    }

    /**
     * check if user's flag set
     * @param int $flag flag
     * @return bool
     */
    public function hasFlag(int $flag): bool
    {
        return $this->flags & $flag !== 0;
    }

    /**
     * set user's flags
     * @param int $flag flags
     * @return int result flags
     */
    public function setFlag(int $flag): int
    {
        $this->flags = $this->flags | $flag;
        $this->save();
        return $this->flags;
    }

    public function sendPhoneConfirmCode(): string
    {
        try {
            if (!empty($this->last_send_at)) {
                $secs = now()->diffInSeconds($this->last_send_at);
                Log::channel('telegram')->info($secs);
                if( $secs < 120) {
//                    return "Повторная отправка смс возможна через:". (120 - $secs).' c';
                    return __('telegram.resend_timeout', ['secs' => (120 - $secs)]);
                }
            }
            if (empty($this->phone_confirm_code)) {
                $this->phone_confirm_code = str_pad(random_int(0, 9999), 4, '0');
            }
            $this->last_send_at = now()->format('Y-m-d H:i:s');
//            @SMSManager::sendSMS($this->phone, "Код подтверждения телеграм: ".$this->phone_confirm_code);
            $resp = SMSManager::sendSMS($this->phone, __('telegram.confirm_code', ['code' => $this->phone_confirm_code]));
            if ($resp->success === false) {
                return ($resp->error?? 'Error sending SMS');
            }
            $this->save();
            return __('telegram.code_sent');

        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
        return "System error!";
    }

    public function sendEmailConfirmCode(): string
    {
        try {
            if (!empty($this->last_send_at)) {
                $secs = now()->diffInSeconds($this->last_send_at);
                Log::channel('telegram')->info($secs);
                if( $secs < 60) {
//                    return "Повторная отправка email возможна через:". (60 - $secs).' c';
                    return __('telegram.resend_timeout', ['secs' => (120 - $secs)]);
                }
            }

            if (empty($this->email_confirm_code)) {
                $this->email_confirm_code = str_pad(random_int(0, 99999999), 8, '0');
            }

            $this->last_send_at = now()->format('Y-m-d H:i:s');
            $this->save();

            $user = $this->getMUser();
            $user->email_verification_token = $this->email_confirm_code;
            $user->save();

            @Mail::to($user)->send(new EmailVerification($user));
            return __('telegram.code_sent');

        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
        return "System error!";
    }
}
