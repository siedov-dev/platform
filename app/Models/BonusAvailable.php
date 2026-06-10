<?php

namespace App\Models;

use App\Models\Bonuses\Segments\BonusSegment;
use App\Models\Bonuses\Segments\BonusSegmentAttachable;
use App\Models\Bonuses\UserBonus;
use App\MUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Доступные бонусы.
 * Используется для выдачи бонусов.
 * @property int $id
 * @property int $bonus_type
 * @property int $currency_id
 * @property string $params
 * @property string $allowed_users
 * @property string $users_activated
 * @property string $users_canceled
 * @property string $games
 * @property Carbon $available_from
 * @property Carbon $available_to
 * @property Carbon $crated_at
 * @property Carbon $updated_at
 * @property string $title
 * @property string $img_url
 * @property string $descr
 * @property int $pattern_id
 * @property Collection|BonusSegment[] $bonusSegments
 * @property ?int $wager_games_segment_id
 * @deprecated
 */
class BonusAvailable extends Model implements BonusSegmentAttachable
{
    use HasFactory;

    protected $dates = [
        'available_from',
        'available_to',
        'crated_at',
        'updated_at',
    ];

    private ?array $_allowed = null;
    private ?array $_activated = null;
    private ?array $_canceled = null;
    private ?UserBonus $_bonus = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * return array of available bonuses for user
     * @param MUser $user
     * @return array
     */
    public static function getForUser(MUser $user): array
    {
        $bonuses = [];
        $avl = BonusAvailable::where('available_from', '<=', now()->format('Y-m-d H:i:s'))
            ->where('available_to', '>', now()->format('Y-m-d H:i:s'))
            ->get();
        /** @var BonusAvailable $available */
        foreach ($avl as $available) {
            if ($available->userAllowed($user)) $bonuses[] = $available;
        }
        return $bonuses;
    }

    /**
     * is bonus available for user
     * @param MUser $user
     * @return bool
     */
    public function userAllowed(MUser $user): bool
    {
        // 0 - all currencies
        if ($this->currency_id != 0 && $user->getWallet()->getCurrencyId() != $this->currency_id) return false;
        if (in_array($user->getId(), $this->getActivatedIds())) return false;
        if (!empty($this->getAvailableIds())) return in_array($user->getId(), $this->getAvailableIds());
        return true;
    }

    /**
     * add user id to list of activated users
     * @param int $user_id
     */
    public function addActivatedId(int $user_id): void
    {
        $this->_activated = $this->getActivatedIds();
        $this->_activated[] = $user_id;
        $this->users_activated = json_encode($this->_activated);
        $this->save();
    }

    public function addCanceledId(int $user_id): void
    {
        $this->_canceled = $this->getCanceledIds();
        $this->_canceled[] = $user_id;
        $this->users_canceled = json_encode($this->_canceled);
        $this->save();
    }

    /**
     * get bonus for user
     * @param MUser $user
     * @return UserBonus
     * @throws \Exception
     */
    public function getBonus(MUser $user, ?bool $create = false): ?UserBonus
    {
        if (!$this->userAllowed($user)) {
            Log::error(__CLASS__."user not allowed for bonus #".$user->getId());
            return null;
        }
        if ($this->_bonus === null || $create) {
            $bonus = new UserBonus();
            $bonus->type = $this->bonus_type;
            $bonus = UserBonus::convertByType($bonus);
            if (!$create) {
                $this->_bonus = $bonus->createFromParams($this->params, $user->getId(), null, null, true);
                $this->_bonus->setSubId("ba_" . $this->id);
                if (!empty($this->games)) $this->_bonus->setAllowedGames($this->games);
                $this->_bonus->img_url = $this->img_url;
                $this->_bonus->title = $this->title;
                $this->_bonus->pattern_id = $this->pattern_id;
                $this->_bonus->created_at = $this->available_from;
                $this->_bonus->active_to = $this->available_to->addHour();

            } else {
                $this->addActivatedId($user->getId());
                $this->_bonus = $bonus->createFromParams($this->params, $user->getId());
                if (!empty($this->games)) $this->_bonus->setAllowedGames($this->games);
                $this->_bonus->creator_id = $this->id;
                $this->_bonus->creator_type = UserBonus::CREATOR_BONUS_AVAILABLE;
                $this->_bonus->img_url = $this->img_url;
                $this->_bonus->title = $this->title;
                $this->_bonus->pattern_id = $this->pattern_id;
                $this->_bonus->save();
                if ($this->bonusSegments->count() > 0) {
                    $this->_bonus->attachBonusSegments($this->bonusSegments->pluck('id')->toArray());
                }
            }
        }

        return $this->_bonus;
    }

    public function getActivatedIds(): array
    {
        if ($this->_activated === null) {
            if (!empty($this->users_activated)) {
                $this->_activated = json_decode($this->users_activated, true)?? [];
            } else {
                $this->_activated = [];
            }
        }
        return $this->_activated;
    }

    public function getCanceledIds(): array
    {
        if ($this->_canceled === null) {
            if (!empty($this->users_canceled)) {
                $this->_canceled = json_decode($this->users_canceled, true)?? [];
            } else {
                $this->_canceled = [];
            }
        }
        return $this->_canceled;
    }

    public function getAvailableIds(): array
    {
        if ($this->_allowed === null) {
            if (!empty($this->allowed_users)) {
                $this->_allowed = json_decode($this->allowed_users, true)?? [];
            } else {
                $this->_allowed = [];
            }
        }
        return $this->_allowed;

    }

    public function getBonusType(): int
    {
        return $this->bonus_type;
    }

    public function setBonusType(int $bonus_type): void
    {
        $this->bonus_type = $bonus_type;
    }

    public function getCurrencyId(): int
    {
        return $this->currency_id;
    }

    public function setCurrencyId(int $currency_id): void
    {
        $this->currency_id = $currency_id;
    }

    public function getParams(): string
    {
        return $this->params;
    }

    public function setParams(string $params): void
    {
        $this->params = $params;
    }

    public function getAllowedUsers(): string
    {
        return $this->allowed_users;
    }

    public function setAllowedUsers(string $allowed_users): void
    {
        $this->allowed_users = $allowed_users;
    }

    public function getUsersActivated(): string
    {
        return $this->users_activated;
    }

    public function setUsersActivated(string $users_activated): void
    {
        $this->users_activated = $users_activated;
    }

    public function getUsersCanceled(): string
    {
        return $this->users_canceled;
    }

    public function setUsersCanceled(string $users_canceled): void
    {
        $this->users_canceled = $users_canceled;
    }

    public function getGames(): string
    {
        return $this->games;
    }

    public function setGames(string $games): void
    {
        $this->games = $games;
    }

    public function getAvailableFrom(): Carbon
    {
        return $this->available_from;
    }

    public function setAvailableFrom(Carbon $available_from): void
    {
        $this->available_from = $available_from;
    }

    public function getAvailableTo(): Carbon
    {
        return $this->available_to;
    }

    public function setAvailableTo(Carbon $available_to): void
    {
        $this->available_to = $available_to;
    }

    public function getCratedAt(): Carbon
    {
        return $this->crated_at;
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

    public function getDescr(): string
    {
        return $this->descr;
    }

    public function setDescr(string $descr): void
    {
        $this->descr = $descr;
    }

    public function getPatternId(): int
    {
        return $this->pattern_id;
    }

    public function setPatternId(int $pattern_id): void
    {
        $this->pattern_id = $pattern_id;
    }

    public function bonusSegments(): BelongsToMany
    {
        return $this->belongsToMany(BonusSegment::class, 'bonus_segment_bonus_available');
    }

    /**
     * Привязывает бонус к сегментам
     * @param array $segm_ids
     * @return void
     */
    public function attachBonusSegments(array $segm_ids = []): void
    {
        $this->bonusSegments()->syncWithoutDetaching($segm_ids);
    }

    public function getWagerGamesSegmentId(): ?int
    {
        return $this->wager_games_segment_id;
    }

    public function setWagerGamesSegmentId(?int $wager_games_segment_id): void
    {
        $this->wager_games_segment_id = $wager_games_segment_id;
    }

}
