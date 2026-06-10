<?php

namespace App\Models\Crm;

use App\Helpers\AppFuncs;
use App\Helpers\SSEvents;
use App\Managers\BindingRequests;
use App\Models\Crm\ClientComment;
use App\Models\Crm\TgChatLog;
use App\Models\User;
use App\Models\UserRole\UserRole;
use App\MUser;
use App\TgDestApi\TgDestApi;
use App\TgDestApi\TgResponse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\ManagerTgBot\ReqState\ReqestUserState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;
use function __;
use function config;
use function now;

/**
 * @property int $id
 * @property int $tg_id
 * @property int $manager_id
 * @property int $m_user_id
 * @property string $locale
 * @property string $username
 * @property string $first_name
 * @property string $last_name
 * @property int $status
 * @property int $work_status
 * @property string $data
 * @property int $req_status
 * @property int $req_message_id
 * @property string $email
 * @property string $phone
 * @property Carbon $last_active
 * @property Carbon $postpone_to
 * @property string $client_image base64 encoded client image
 * @property string $image_mime
 * @property string $hash
 * @property Carbon $last_accrual_at
 * @property ?User $manager
 * @property int $aff_link_id
 */
class TelegramClient extends Model
{
    use HasFactory;

    const STATUS_NEW = 0;
    const STATUS_PENDING_BIND = 1;
    const STATUS_BIND = 2;
    const STATUS_BIND_FAILED = 3;
    const STATUS_REGISTERED = 4;

    const WS_DEFAUL = 0;
    const WS_TO_WORK = 2;

    const REQ_STATUS_NONE = 0;
    const REQ_STATUS_GET_EMAIL = 1; //wait for users email or phone number
    const REQ_STATUS_CONFIRM_EMAIL = 2; //inline confirm provided email|phone
    const REQ_STATUS_CONFIRM_PHONE_CODE = 3; //confirm sent phone number
    const REQ_STATUS_CONFIRM_EMAIL_CODE = 4; //confirm sent email
    const REQ_STATUS_CONFIG = 5; // config menu
    const REQ_STATUS_LANG_CHOOSE = 6; //select language

    protected $connection = "crm_admin";

    protected $dates = [
        'last_active',
        'postpone_to',
        'created_at',
        'updated_at',
        'last_accrual_at',
    ];

    private ?TgDestApi $_api = null;

    /**
     * get id
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * get user telegram id
     * @return int
     */
    public function getTelegramId(): int
    {
        return $this->tg_id?? 0;
    }

    /**
     * get MUser
     * @return MUser|null
     */
    public function getMUser(): ?MUser
    {
        return MUser::find($this->m_user_id);
    }

    /**
     * store some data in data filed
     * @param string $key
     * @param $value
     */
    public function setData(string $key, $value)
    {
        $data = json_decode($this->data, true)?? [];
        $data[$key] = $value;
        $this->data = json_encode($data);
        $this->save();
    }

    /**
     * get data stored in data filed
     * @param string $key
     * @return mixed
     */
    public function getData(string $key): mixed
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
     * get request status
     * @return int|null
     */
    public function getReqStatus(): ?int
    {
        return $this->req_status ?? 0;
    }

    /**
     * get bind status
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    public function getStatusName(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'Новый',
            self::STATUS_PENDING_BIND => 'Ожидает привязки',
            self::STATUS_BIND => 'Привязан',
            self::STATUS_BIND_FAILED => 'Не привязан',
            self::STATUS_REGISTERED => 'Регистрация',
        };
    }

    /**
     * set bind status
     * @param int $status
     * @return TelegramClient
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        $this->save();
        return $this;
    }

    /**
     * get wirk status
     * @return int
     */
    public function getWorkStatus(): int
    {
        return $this->work_status?? 0;
    }

    /**
     * set work status
     * @param int $work_status
     */
    public function setWorkStatus(int $work_status): void
    {
        $this->work_status = $work_status;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->first_name?? '';
    }


    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username?? '';
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->last_name?? '';
    }

    /**
     * @return int
     */
    public function getManagerId(): int
    {
        return $this->manager_id?? 0;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->getMUser()?->email?? $this->email;
    }

    public function manager()
    {
        return $this->hasOne(User::class, 'id', 'manager_id');
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @return Carbon|NULL
     */
    public function getLastActive(): ?Carbon
    {
        return $this->last_active;
    }

    public function updateLastActive(): void
    {
        $this->last_active = now();
        if ($this->getWorkStatus() === self::WS_TO_WORK) $this->setWorkStatus(self::WS_DEFAUL);
        $this->save();
    }

    private function getTgApi(): TgDestApi
    {
        if ($this->_api === null) $this->_api = new TgDestApi(config('manager_bot.token'));
        return $this->_api;
    }

    public function sendMessage(string $message): ?TgResponse
    {
        if ($this->tg_id == 0) return null;

        if ($this->_api === null) $this->_api = new TgDestApi(config('manager_bot.token'));
        $resp = $this->_api->sendMessage($this->tg_id, $message, $this->getMenu());
        if ($resp->isOk()) {
            $message = str_replace("\n", "</br>", $message);
            TgChatLog::saveLog(now()->timestamp, $message, $resp->getResult()->get('message_id'), $this->getTelegramId(), Auth::id(), TgChatLog::TO_USER);
        }
        $this->updateLastActive();
        return $resp;
    }

    public function sendImage($file, ?string $caption = null): ?TgResponse
    {
        if ($this->tg_id == 0) return null;

        if ($this->_api === null) $this->_api = new TgDestApi(config('manager_bot.token'));
        $resp = $this->_api->sendPhoto($this->tg_id, $file, $caption, $this->getMenu());
        if ($resp->isOk()) {
            $message = str_replace("\n", "</br>", $caption);
        }
        $this->updateLastActive();
        return $resp;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale)
    {
        $this->locale = $locale;
    }

    public function getReqState(): ReqestUserState
    {
        return ReqestUserState::getState($this);
    }

    /**
     * get affiliate's link id
     * @return int
     */
    public function getAffLinkId(): int
    {
        return $this->aff_link_id?? 0;
    }

    /**
     * set affiliate's link id
     * @param int $aff_link_id
     * @return TelegramClient
     */
    public function setAffLinkId(int $aff_link_id): self
    {
        $this->aff_link_id = $aff_link_id;
        $this->save();
        return $this;
    }

    public function getMenu(): ?array
    {
        return $this->getReqState()->getUserMenu();
    }

    /**
     * @return Collection<ClientComment>
     */
    public function getComments(): Collection
    {
        return ClientComment::where('client_id', $this->getId())->get();
    }

    public function getTgProfileImgBase64(): string
    {
        if ($this->client_image !== null) return $this->client_image;
        // Get the URL of the user's profile photo
        $resp = $this->getTgApi()->getUserProfilePhoto($this->getTelegramId());
        if (!$resp->isOk() || $resp->getResult()->getTotalCount() === 0) return "";
        $bot_token = $this->getTgApi()->getToken();
        $file_id = $resp->getResult()->getPhotos()[0][0]->file_id;
        if (!empty($file_id)) {
            $file_info = $this->getTgApi()->getFile($file_id)->getResult();
            if (!empty($file_info->getFileId())) {
                $file = file_get_contents('https://api.telegram.org/file/bot'.$this->getTgApi()->getToken().'/'.$file_info->getFilePath());
                $this->client_image = base64_encode($file);
                $this->save();
                return $this->client_image?? "";
            }
        }

        $this->client_image = "";
        $this->save();

        // Use the URL to display the user's profile photo
        return $this->client_image?? "";
    }

    public function getTgProfileImgMime(): string
    {
        if (!empty($this->image_mime)) return $this->image_mime;
        if (!empty($this->client_image)) {
            $binary_image = base64_decode($this->client_image);
            $info = getimagesizefromstring($binary_image);
            $this->image_mime = $info['mime']??'';
            $this->save();
            return $info['mime']??'';
        }
        return "";
    }

    #[Pure] public function getNameColor(): string
    {
        // Define an array of 16 colors
        $colors = array(
            '#134f5c', '#0b5394', '#351c75', '#741b47',
            '#990000', '#b45f06', '#bf9000', '#38761d',
            '#2c3e50', '#8e44ad', '#c0392b', '#9c14c6',
            '#2d3436', '#6c5ce7', '#299ab7', '#17c0eb'
        );

        // Calculate the hash value of the input string
        $hash = hexdec(substr(md5($this->getUsername()), 0, 7));

        // Get the index of the color to use
        $color_index = $hash % 16;

        // Return the selected color
        return $colors[$color_index];
    }

    public function setManager(User $manager, ?string $reason = null)
    {
        if ($this->last_accrual_at === null) {
            $this->last_accrual_at = now();
        } else {
            ManagerAccrual::calculateClient($this);
        }

        $this->manager_id = $manager->getId();
        $this->status = self::STATUS_BIND;
        $this->save();

        $log = new ClientBindHistory();
        $log->bind_to = $manager->getId();
        $log->top_manager_id = Auth::id();
        $log->reason = $reason;
        $log->client_id = $this->getId();
        $log->save();
    }

    public function getHash(): string
    {
        if (empty($this->hash)) {
            $this->hash = Str::random(20);
            $this->save();
        }
        return $this->hash;
    }

    public function requestBind(User $manager)
    {
        BindingRequests::createRequest($this, $manager);

        //send notification to manager
        SSEvents::sendMessage($manager->getId(), SSEvents::TYPE_BIND_REQUEST, [
            'from_user' => $this->getId(),
            'to_user' => $manager->getId(),
            'login' => $this->getMUser()?->login,
            'username' => $this->getUsername(),
        ]);
    }

    public function bindAvailableManager()
    {
        //mark user as waiting for bind
        $this->setStatus(self::STATUS_PENDING_BIND);

        //if there is manager available
        $availableManager = User::getNextAvailableManager($this->getLocale());
        if ($availableManager !== null) {
            $this->requestBind($availableManager);
            return;
        }
        $this->sendMessage(__('tg_manager.no_managers_available'));
        $this->setStatus(self::STATUS_BIND_FAILED);
        SSEvents::sendToUserRole(UserRole::ROLE_TOP_MANAGER, 'bind_fail', [
            'client_id' => $this->getId(),
            'username' => $this->getUsername(),
            'login' => $this->getMUser()?->login,
        ]);
    }


}
