<?php

namespace App\Viber\Models;

use App\Mail\EmailVerification;
use App\MUser;
use App\SMSManager;
use App\Telegram\Models\TelegramUser;
use App\Viber\ViberApi;
use App\Viber\ViberBot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Class ViberUser
 * @package App\Viber\Models
 * @property int $id
 * @property string viber_id
 * @property int user_id
 * @property string name
 * @property string avatar
 * @property string language
 * @property string country
 * @property string api_ver
 * @property int status
 * @property string data
 * @property int req_status
 * @property int req_message_id
 * @property string email
 * @property string phone
 * @property string phone_confirm_code
 * @property string email_confirm_code
 * @property Carbon last_send_at
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class ViberUser extends Model
{
    //
    const STATUS_NOT_CONFIRMED = 0;
    const STATUS_REQUEST_REG_BONUS = 1;
    const STATUS_CONFIRMED = 2;

    const REQ_STATUS_NONE = 0;
    const REQ_STATUS_GET_EMAIL = 1; //wait for users email or phone number
    const REQ_STATUS_CONFIRM_EMAIL = 2; //inline confirm provided email|phone
    const REQ_STATUS_CONFIRM_PHONE_CODE = 3; //confirm sent phone number
    const REQ_STATUS_CONFIRM_EMAIL_CODE = 4; //confirm sent email

    protected $dates = [
        'last_send_at',
        'created_at',
        'updated_at',
    ];

    private ViberApi $api;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->api = new ViberApi();
    }

    public function sendText(string $text, string $tracking_data = null)
    {
        $this->api->sendMessage($this->viber_id, $text, $this->getUserMenu(), $tracking_data);
    }

    public function sendImage($img_url, $description = "", $thumbnail = null, string $tracking_data = null)
    {
        $this->api->sendImage($this->viber_id, $img_url, $description, $thumbnail, $this->getUserMenu(), $tracking_data);
    }

    public static function getFromData(array $data): ?ViberUser
    {
        if (empty($data['id'])) {
            Log::channel('viber')->error('sender id is empty!' . var_export($data, true));
            return null;
        }

        $v_user = ViberUser::where('viber_id', $data['id'])->first();
        if ($v_user === null) {
            $v_user = new ViberUser();
            ViberStat::incJoin();
        }

        $v_user->viber_id = $data['id'];
        $v_user->name = $data['name'] ?? '';
        $v_user->avatar = $data['avatar'] ?? '';
        $v_user->country = $data['country'] ?? '';
        $v_user->language = $data['language'] ?? '';
        $v_user->api_ver = $data['api_version'] ?? '';
        $v_user->save();

        return $v_user;
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
        return $this->req_status ?? self::REQ_STATUS_NONE;
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
     * get get telegram menu markup
     * @return array
     */
    public function getUserMenu()
    {

        if ($this->status == self::STATUS_NOT_CONFIRMED && $this->getReqStatus() == self::REQ_STATUS_CONFIRM_EMAIL) {

            return [
                "Type" => "keyboard",
                "DefaultHeight" => false,
                "Buttons" => [
                    [
                        "ActionType" => "reply",
                        "ActionBody" => "/confirm_login",
                        "Text" => "✅ <font color='#FFFFFF'>Да</font>",
                        "TextSize" => "large",
                        "BgColor" => "#3D3E6D",
                        "Color" => "#FFFFFF",
                        "TextHAlign" => "center",
                        "TextVAlign" => "middle",
                    ],
                    [
                        "ActionType" => "reply",
                        "ActionBody" => "/cancel",
                        "Text" => "❌ <font color='#FFFFFF'>Отмена</font>",
                        "TextSize" => "large",
                        "BgColor" => "#3D3E6D",
                        "Color" => "#FFFFFF",
                        "TextHAlign" => "center",
                        "TextVAlign" => "middle",
                    ],
                ]
            ];

        }

        if ($this->status == self::STATUS_NOT_CONFIRMED && $this->getReqStatus() == self::REQ_STATUS_CONFIRM_PHONE_CODE) {

            return [
                "Type" => "keyboard",
                "DefaultHeight" => false,
                "Buttons" => [
                    [
                        "ActionType" => "reply",
                        "ActionBody" => "/resend_phone",
                        "Text" => "\u{1F4E8} <font color='#FFFFFF'>Повторно выслать смс код</font>",
                        "TextSize" => "large",
                        "BgColor" => "#3D3E6D",
                        "Color" => "#FFFFFF",
                        "TextHAlign" => "center",
                        "TextVAlign" => "middle",
                    ],
                    [
                        "ActionType" => "reply",
                        "ActionBody" => "/cancel",
                        "Text" => "❌ <font color='#FFFFFF'>Отмена</font>",
                        "TextSize" => "large",
                        "BgColor" => "#3D3E6D",
                        "Color" => "#FFFFFF",
                        "TextHAlign" => "center",
                        "TextVAlign" => "middle",
                    ],
                ]
            ];

        }

        if ($this->status == self::STATUS_NOT_CONFIRMED && $this->getReqStatus() == self::REQ_STATUS_CONFIRM_EMAIL_CODE) {

            return [
                "Type" => "keyboard",
                "DefaultHeight" => false,
                "Buttons" => [
                    [
                        "ActionType" => "reply",
                        "ActionBody" => "/resend_email",
                        "Text" => "\u{1F4E8} <font color='#FFFFFF'>Повторно выслать email код</font>",
                        "TextSize" => "large",
                        "BgColor" => "#3D3E6D",
                        "Color" => "#FFFFFF",
                        "TextHAlign" => "center",
                        "TextVAlign" => "middle",
                    ],
                    [
                        "ActionType" => "reply",
                        "ActionBody" => "/cancel",
                        "Text" => "❌ <font color='#FFFFFF'>Отмена</font>",
                        "TextSize" => "large",
                        "BgColor" => "#3D3E6D",
                        "Color" => "#FFFFFF",
                        "TextHAlign" => "center",
                        "TextVAlign" => "middle",
                    ],
                ]
            ];

        }

        if ($this->status == TelegramUser::STATUS_NOT_CONFIRMED && $this->getReqStatus() == TelegramUser::REQ_STATUS_GET_EMAIL) {

            return [
                "Type" => "keyboard",
                "DefaultHeight" => false,
                "Buttons" => [
                    [
                        "ActionType" => "share-phone",
                        "ActionBody" => "share_phone",
                        "Text" => "\u{1F4F2} <font color='#FFFFFF'>Мой номер</font>",
                        "TextSize" => "large",
                        "BgColor" => "#3D3E6D",
                        "Color" => "#FFFFFF",
                        "TextHAlign" => "center",
                        "TextVAlign" => "middle",
                        "Columns" => 3,
                    ],
                    [
                        "ActionType" => "reply",
                        "ActionBody" => "/cancel",
                        "Text" => "❌ <font color='#FFFFFF'>Отмена</font>",
                        "TextSize" => "large",
                        "BgColor" => "#3D3E6D",
                        "Color" => "#FFFFFF",
                        "TextHAlign" => "center",
                        "TextVAlign" => "middle",
                        "Columns" => 3,
                    ],
                ]
            ];
        }

        if ($this->status == self::STATUS_NOT_CONFIRMED) {

            return [
                "Type" => "keyboard",
                "DefaultHeight" => false,
                "Buttons" => [
                    [
                        "ActionType" => "reply",
                        "ActionBody" => "/login",
                        "Text" => "\u{1F4F2} <font color='#FFFFFF'>Привязать аккаунт</font>",
                        "TextSize" => "large",
                        "BgColor" => "#3D3E6D",
                        "Color" => "#FFFFFF",
                        "TextHAlign" => "center",
                        "TextVAlign" => "middle",
                    ]
                ]
            ];

        }

        return [
            "Type" => "keyboard",
            "DefaultHeight" => false,
            "Buttons" => [
                [
                    "ActionType" => "reply",
                    "ActionBody" => "/balance",
                    "Text" => "\u{1F4B5} <font color='#FFFFFF'>Баланс</font>",
                    "TextSize" => "large",
                    "BgColor" => "#3D3E6D",
                    "Color" => "#FFFFFF",
                    "TextHAlign" => "center",
                    "TextVAlign" => "middle",
                    "Columns" => 3,
                ],
                [
                    "ActionType" => "reply",
                    "ActionBody" => "/bonuses",
                    "Text" => "\u{1F381} <font color='#FFFFFF'>Бонусы</font>",
                    "TextSize" => "large",
                    "BgColor" => "#3D3E6D",
                    "Color" => "#FFFFFF",
                    "TextHAlign" => "center",
                    "TextVAlign" => "middle",
                    "Columns" => 3,
                ],
                [
                    "ActionType" => "reply",
                    "ActionBody" => "/events",
                    "Text" => "\u{2728} <font color='#FFFFFF'>Текущие активности</font>",
                    "TextSize" => "large",
                    "BgColor" => "#3D3E6D",
                    "Color" => "#FFFFFF",
                    "TextHAlign" => "center",
                    "TextVAlign" => "middle",
                    "Columns" => 3,
                ],
                [
                    "ActionType" => "reply",
                    "ActionBody" => "/promocode",
                    "Text" => "\u{1F39F} \u{FE0F} <font color='#FFFFFF'>Бездеп. бонус</font>",
                    "TextSize" => "large",
                    "BgColor" => "#3D3E6D",
                    "Color" => "#FFFFFF",
                    "TextHAlign" => "center",
                    "TextVAlign" => "middle",
                    "Columns" => 3,
                ],
                [
                    "ActionType" => "open-url",
                    "ActionBody" => "viber://pa?chatURI=krakencasinosupport",
                    "Text" => "\u{1F4AC} <font color='#FFFFFF'>Онлайн-чат</font>",
                    "TextSize" => "large",
                    "BgColor" => "#3D3E6D",
                    "Color" => "#FFFFFF",
                    "TextHAlign" => "center",
                    "TextVAlign" => "middle",
                    "Columns" => 6,
                ],
            ]
        ];
    }

    public function sendPhoneConfirmCode(): string
    {
        try {
            if (!empty($this->last_send_at)) {
                $secs = now()->diffInSeconds($this->last_send_at);
                Log::channel('telegram')->info($secs);
                if ($secs < 120) {
                    return __('telegram.resend_timeout', ['secs' => (120 - $secs)]);
                }
            }
            if (empty($this->phone_confirm_code)) {
                $this->phone_confirm_code = str_pad(random_int(0, 9999), 4, '0');
            }
            $this->last_send_at = now()->format('Y-m-d H:i:s');
            $resp = SMSManager::sendSMS($this->phone, "Код подтверждения телеграм: " . $this->phone_confirm_code);
            if ($resp->success === false) {
                return ($resp->error?? 'Error sending SMS');
            }
            $this->save();
            return __('telegram.code_sent');

        } catch (\Exception $e) {
            Log::channel('stack')->error($e->getMessage());
        }
        return "error!";
    }

    public function sendEmailConfirmCode(): string
    {
        try {
            if (!empty($this->last_send_at)) {
                $secs = now()->diffInSeconds($this->last_send_at);
                Log::channel('telegram')->info($secs);
                if ($secs < 60) {
                    return "Повторная отправка email возможна через:" . (60 - $secs) . ' c';
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
            return "Код выслан на email";

        } catch (\Exception $e) {
            Log::channel('stack')->error($e->getMessage());
        }
        return "error!";
    }
}
