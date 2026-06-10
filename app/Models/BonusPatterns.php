<?php

namespace App\Models;

use App\GamesProviders\GamesManager;
use App\Models\Bonuses\KUserBonus;
use App\MUser;
use App\Services\KBonus\KBonus;

/**
 * Class BonusPatterns
 * @package App\Models
 * @property int id
 * @property string name
 * @property string description
 * @property int description_page_id
 * @property int bonus_type
 * @property string bonus_params
 * @property string on_create_commands
 * @property string on_activate_commands
 * @property string on_done_commands
 * @property string allowed_games
 * @property string $title
 * @property string $img_url
 * @property string $segment_ids
 * @property bool $disabled
 * @property ?int $wager_games_segment_id
 */
class BonusPatterns
{
    const PTN_FIRST_DEP_BONUS_RUB = 1;
    const PTN_SECOND_DEP_BONUS_RUB = 3;
    const PTN_THIRD_DEP_BONUS_RUB = 5;
    const PTN_FIRST_DEP_BONUS_UAH = 7;
    const PTN_SECOND_DEP_BONUS_UAH = 8;
    const PTN_THIRD_DEP_BONUS_UAH = 9;

    const PTN_FIRST_DEP_BONUS_SW = 30;
    const PTN_SECOND_DEP_BONUS_SW = 31;
    const PTN_THIRD_DEP_BONUS_SW = 32;

    const PTN_20SPINS_REACTIVATION = 10;
    const PTN_40SPINS_REACTIVATION = 11;
    const PTN_50SPINS_REACTIVATION = 12;
    const PTN_REACTIVATION_DEPOSIT1 = 13;
    const PTN_REACTIVATION_DEPOSIT2 = 14;
    const PTN_REACTIVATION_DEPOSIT3 = 15;

    const PTN_10_FFS = 16;

    const PTN_VIP_RET_1 = 17;
    const PTN_VIP_RET_2 = 18;
    const PTN_VIP_RET_3 = 19;
    const PTN_VIP_RET_4 = 20;
    const PTN_VIP_RET_5 = 0;
    const PTN_VIP_RET_6 = 0;
    const PTN_VIP_RET_7 = 17;
    const PTN_VIP_RET_8 = 21;

    const PTN_30_FFS = 22;
    const PTN_120_DEP = 23;
    const PTN_50_NDEP_FS = 24;
    const PTN_150_DEP_50_FS = 25;

    const PTN_BIRTHDAY = 27;

    const PTN_RET_1 = 51;
    const PTN_RET_2 = 52;
    const PTN_RET_3 = 53;
    const PTN_RET_4 = 54;
    const PTN_RET_5 = 55;

    const PTN_SUBSCRIBE = 63;
    const PTN_MAIL_CONFIRM = 64;

    const RB_TYPE_DEP = "dep_bonus";
    const RB_TYPE_FS = "fd_fs";

    protected $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->attributes[$offset];
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->attributes[$offset] = null;
    }

    public function toArray()
    {
        return $this->attributes;
    }

    public static function find(int $id): ?BonusPatterns
    {
        return KBonus::getInstance()->getPatternById($id);
    }

    public static function getRegPattern(string $currency, string $type, int $num): ?BonusPatterns
    {
        return KBonus::getInstance()->getRegPattern($currency, $type, $num);
    }

    public static function getPatternByName(string $name): ?BonusPatterns
    {
        return KBonus::getInstance()->getPatternByName($name);
    }

    public static function getRegPatternIds(string $currency): array
    {
        return KBonus::getInstance()->getRegPatternIds($currency);
    }

    public static function getRegFsPatternIds(string $currency): array
    {
        return KBonus::getInstance()->getRegFsPatternIds($currency);
    }

    public static function getRegNonDepPatternIds(string $currency): array
    {
        return KBonus::getInstance()->getRegNonDepPatternIds($currency);
    }

    public function createForUser(MUser $user, ?int $currency = null, $tmp = false): ?KUserBonus
    {
        $bonus = KBonus::getInstance()->createFromPatternParams(
            user_id: $user->id,
            pattern_id: $this->id,
            currency: $currency,
            title: $this->title,
            img_url: $this->img_url,
            tmp: $tmp,
        );
        if (!$tmp && !empty($bonus)) {
//            $bonus = KUserBonus::convertByType($bonus);
            // segment by game name
            if (!empty($bonus->game_id) && !empty($bonus->game_provider)) {
                $game = GamesManager::getProvider($bonus->game_provider)?->getGame('', $bonus->game_id, true);
                if ($game) {
                    KBonus::getInstance()->addBonusToSegmentByName($bonus->getId(), $game->getName());
                }
            }
        }

        return $bonus;
    }

    /**
     * Get value from json data
     * @param string $key
     * @param $default
     * @return mixed|null
     */
    public function getBonusParam(string $key, string|null $currency = null, $default = null): mixed
    {
        $params = json_decode($this->bonus_params, true)?? [];
        if ($currency === null) return $params[$key]?? $default;
        return $params['params_'.$currency][$key]?? $params[$key]?? $default;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescriptionPageId(): int
    {
        return $this->description_page_id;
    }

    public function setDescriptionPageId(int $description_page_id): void
    {
        $this->description_page_id = $description_page_id;
    }

    public function getBonusType(): int
    {
        return $this->bonus_type;
    }

    public function setBonusType(int $bonus_type): void
    {
        $this->bonus_type = $bonus_type;
    }

    public function getBonusParams(): string
    {
        return $this->bonus_params;
    }

    public function setBonusParams(string $bonus_params): void
    {
        $this->bonus_params = $bonus_params;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getImgUrl(): string
    {
        return $this->img_url;
    }

    public function setImgUrl(string $img_url): void
    {
        $this->img_url = $img_url;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }
}
