<?php

namespace App\Models\Slc\Achievements;

use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\Helpers\Hlog;
use App\Models\Slc\Factories\AchievementDataFactory;
use App\Models\Slc\Achievements\Data\SlcAchievementData;
use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcItemType;
use App\MUser;
use App\Services\NatsService\NatsUserEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $name
 * @property SlcAchievementType $type
 * @property int $level @depraceted
 * @property int $currency
 * @property int $target_value
 * @property int $slc_reward @depraceted
 * @property int $bonus_type @depraceted
 * @property string $bonus_params @depraceted
 * @property string $bonus_games @depraceted
 * @property bool $is_disabled
 * @property float $float_param
 * @property int $int_param
 * @property string $string_param
 * @property ?string $game_cid
 * @property SlcItemType $item_type @depraceted
 * @property int $amount @depraceted
 * @property int $priority
 */
class SlcAchievement extends Model
{
    use HasFactory;

    private AchievementDataFactory $data_factory;

    protected $casts = [
        'type' => SlcAchievementType::class,
//        'status' => SlcAchievementStatus::class,
//        'item_type' => SlcItemType::class,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->data_factory = App::make(AchievementDataFactory::class);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAchievementType(): SlcAchievementType
    {
        return $this->type;
    }

    public function setAchievementType(SlcAchievementType $type): void
    {
        $this->type = $type;
    }

    /**
     * @deprecated
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level?? 0;
    }

    /**
     * @deprecated
     * @param int $level
     * @return void
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getCurrency(): int
    {
        return $this->currency;
    }

    public function setCurrency(int $currency): void
    {
        $this->currency = $currency;
    }

    public function getTargetValue(): int
    {
        return $this->target_value;
    }

    public function setTargetValue(int $target_value): void
    {
        $this->target_value = $target_value;
    }

    public function isDisabled(): bool
    {
        return $this->is_disabled;
    }

    public function setDisabled(bool $is_disabled): void
    {
        $this->is_disabled = $is_disabled;
    }

    public function getFloatParam(): float
    {
        return $this->float_param?? 0;
    }

    public function setFloatParam(float $float_param): void
    {
        $this->float_param = $float_param;
    }

    public function getIntParam(): int
    {
        return $this->int_param?? 0;
    }

    public function setIntParam(int $int_param): void
    {
        $this->int_param = $int_param;
    }

    public function getStringParam(): string
    {
        return $this->string_param?? "";
    }

    public function setStringParam(string $string_param): void
    {
        $this->string_param = $string_param;
    }

    public function getGameCid(): ?string
    {
        return $this->game_cid;
    }

    public function setGameCid(?string $game_cid): void
    {
        $this->game_cid = $game_cid;
    }

    /**
     * get icon image url
     * @return string
     */
    public function getImage(): string
    {
        return "/img/achievements/{$this->getName()}.svg";
    }

    public function getDisabledImage(): string
    {
        return "/img/achievements/{$this->getName()}-d.svg";
    }

    /**
     * achievement name id
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return __('slc.ach-name_'.$this->getName());
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getUserAchievement(int $user_id): ?SlcUserAchievement
    {
        return SlcUserAchievement::where('achievement_id', $this->id)
            ->where('user_id', $user_id)
            ->first();
    }

    /**
     * if achievement is completed (even if it's not rewarded)
     * @param int $user_id
     * @return bool
     */
    public function isCompleted(int $user_id): bool
    {
        return $this->getUserAchievement($user_id) !== null;
    }

    /**
     * if user got reward for the achievement
     * @param int $user_id
     * @return bool
     */
    public function isRewarded(int $user_id): bool
    {
        return $this->getUserAchievement($user_id)?->isRewarded() === true;
    }

    /**
     * get Achievement related data class
     * @return SlcAchievementData
     */
    public function getData(): SlcAchievementData
    {
        return $this->data_factory->getData($this);
    }

    public function checkCompleted(MUser $m_user): bool
    {
        if ($this->isCompleted($m_user->getId())) return true;
        if ($this->getData()->getCurrentValue($m_user) >= $this->getTargetValue()) {
            $this->setCompleted($m_user);
            return true;
        }
        return false;
    }

    public function setCompleted(MUser $user): void
    {
        try {
            DB::beginTransaction();

            $user_ach = new SlcUserAchievement();
            $user_ach->user_id = $user->getId();
            $user_ach->achievement_id = $this->getId();
            $user_ach->save();

            DB::commit();

            NatsUserEvents::achievementCompleted(
                user_id: $user->getId(),
                ach_id: $this->getId(),
                currency_id: $user->getCurrency(),
                ref_id: $user->ref_id,
                click_id: $user->click_id,
                fingerprint: $user->getFingerprint(),
            );

            CustomerIo::getInstance()->sendEvent($user->getId(), CioEventType::AchievementDone, [
                'type' => $this->getAchievementType()->name,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Hlog::logException($e);
        }
    }

    /**
     * @return int
     * @deprecated
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * slc items
     * @return SlcItem[]
     */
    public function getRewards()
    {
        return SlcAchievementReward::where('ach_id', $this->getId())->get();
    }

    public static function updateAchievements(MUser $user): void
    {
        $achs = SlcAchievement::where('currency', $user->getCurrency())
            ->orWhere('currency', 0)
            ->get();
        foreach ($achs as $achievement) {
            $achievement->checkCompleted($user);
        }
    }

}
