<?php

namespace App\Models;

use App\GamesProviders\GamesManager;
use App\Helpers\GoldPartnersSdk;
use App\Helpers\Hlog;
use App\Jobs\BindManagerJob;
use App\Models\Bonuses\BonusFactory;
use App\Models\Bonuses\KUserBonus;
use App\Models\Bonuses\Segments\BonusSegment;
use App\Models\Bonuses\Segments\BonusSegmentAttachable;
use App\Models\Bonuses\UserBonus;
use App\Models\Slc\Activators\SlcItemActivator;
use App\Models\Slc\Factories\SlcItemFactory;
use App\Models\Slc\ItemInfo\ShopItemInfo;
use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcItemType;
use App\MUser;
use App\Services\KBonus\KBonus;
use App\UserWallet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/**
 * Class BonusPromocode.
 * Содержит информацию о бонусе или SlcItem для промокода.
 * Используется для создания бонуса или SlcItem по введенному промокоду пользователя.
 *
 * @property int id
 * @property PromoType $promo_type
 * @property string promocode
 * @property int bonus_type
 * @property string bonus_params
 * @property int quantity
 * @property int $activation_type
 * @property ?Carbon active_to
 * @property int hall_id
 * @property string user_list
 * @property string created_at
 * @property string allowed_games
 * @property string allowed_users
 * @property ?string $title
 * @property ?string $img_url
 * @property int $affiliate_id
 * @property int $link_id
 * @property int $manager_client_id
 * @property SlcItemType $slc_type
 * @property int $amount
 * @property int $marker
 * @property Collection|GameSegment[] $game_segments
 * @property Collection|BonusSegment[] $bonusSegments
 * @property ?int $wager_games_segment_id
 */
class BonusPromocode extends Model implements SlcItem, BonusSegmentAttachable
{
    const ACTIVATION_TYPE_REGISTERED = 0;
    const ACTIVATION_TYPE_NOT_REGISTERED = 1;
    const ACTIVATION_TYPE_ALL = 2;
    const ACTIVATION_TYPE_ALLOWED_USERS = 3;

    const BONUS_PATTERN = -4;
    const TYPE_VIP_STATUS = -10;

    protected $dates = ['active_to'];

    protected $casts = [
        'slc_type' => SlcItemType::class,
        'promo_type' => PromoType::class,
    ];

    public function game_segments(): BelongsToMany
    {
        return $this->belongsToMany(GameSegment::class);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param MUser $user
     * @return bool
     */
    public function canActivate(MUser $user): bool
    {

        $is_in_black_list = BonusBlackList::where('user_id', $user->getId())->first() !== null;

        if ($is_in_black_list) return false;

        if ($this->activation_type == self::ACTIVATION_TYPE_ALLOWED_USERS) {
            $allowed_users = json_decode($this->allowed_users, true);
            if (!empty($allowed_users) && is_array($allowed_users)) {
                if (!in_array($user->id, $allowed_users)) return false;
            }
        }

        if ($this->quantity > 0 || $this->quantity == -1) {

            $user_list = json_decode($this->user_list, false);

            if (empty($user_list) || !is_array($user_list) || !in_array($user->id, $user_list)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param MUser $user
     * @return mixed
     * @throws \Exception
     */
    public function activate(MUser $user, bool $registration = false)
    {
        $user_list = json_decode($this->user_list, false);

        if (empty($user_list) || !is_array($user_list)) {
            $user_list = [];
        }

        $user_list[] = $user->id;
        $this->user_list = json_encode($user_list);

        if ($this->quantity > 0) {
            $this->quantity--;
        }

        $this->save();

        //if partners promocode - reg partner
        try {
            if ($this->affiliate_id !== null && $registration) {
                $clickId = GoldPartnersSdk::getClickId($this->link_id);
                if (!empty($clickId) && $clickId > 0) {
                    $clickId = $user->setClickId($clickId, $registration);
                    $sdk = new GoldPartnersSdk(config('goldPartners.casinoId'), config('goldPartners.secretKey'));
                    $sdk->clickId = $clickId;
                    $sdk->clientId = $user->id;
                    $sdk->clientName = $user->login;
                    $sdk->currency = UserWallet::currencyName($user->currency);
                    $msg = $sdk->createMessage(GoldPartnersSdk::EVENT_REGISTRATION);
                    $sdk->send($msg);
                    session()->flash('reg_click_sent', true);
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }

        try {
            if ($this->manager_client_id !== null) {
                BindManagerJob::dispatch($user->getId(), '', true, $this->manager_client_id);
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }

        if ($this->getPromoType() === PromoType::SlcItem) {
            return $user->getSlcInventory()->addItem($this, "promocode:".$this->getId())?? true;
        } else {

            if ($this->bonus_type == self::TYPE_VIP_STATUS) {
                $params = json_decode($this->bonus_params, true);
                $status = UserVipStatus::setForUser($user->getId(), $params['vip_status'], $params['active_days']);
                return $status;
            } elseif ($this->bonus_type == self::BONUS_PATTERN) {
                $params = json_decode($this->bonus_params, true);
                if (!empty($params && is_array($params)) && isset($params['pattern_id'])) {
                    $bonus = KBonus::getInstance()->createFromPatternParams(
                        user_id: $user->id,
                        pattern_id: $params['pattern_id'],
                        currency: $user->currency,
                        creator_type: UserBonus::CREATOR_PROMO,
                        creator_id: $this->id,
                        title: $this->title,
                        img_url: $this->img_url,
                        marker: $this->marker,
                        allowed_games: $this->allowed_games,
                        tmp: false,
                    );
                    if ($bonus !== null && $this->affiliate_id !== null) {
                        KBonus::getInstance()->addBonusToSegmentByName($bonus->getId(), "promo partner id:".$this->affiliate_id);;
                    }
//                    if (!empty($bonus)) $bonus = KUserBonus::convertByType($bonus);
                    return $bonus;
                }

            } elseif ($this->bonus_type > 0) {
                Log::info('BonusPromo creating bonus type' . $this->bonus_type . ' for user #' . $user->id);
                $bonus = BonusFactory::createFromParams(
                    bonus_type: $this->bonus_type,
                    user_id: $user->id,
                    params: $this->bonus_params,
                    currency: $user->currency,
                    keep_params: false,
                    tmp: false,
                    pattern_id: 0,
                    creator_type: UserBonus::CREATOR_PROMO,
                    creator_id: $this->id,
                    title: $this->title,
                    img_url: $this->img_url,
                    marker: $this->marker,
                    allowed_games: $this->allowed_games,
                    wager_game_segment_id: $this->wager_games_segment_id,
                );

                if ($bonus?->isAgreementRequired() && !$bonus?->isActivationMenu()) {
                    $activeUserBonus = KUserBonus::findFirstActiveBonus($user->getId());
                    $depositBonus = KUserBonus::getWaitingDeposit($user);
                    if ($activeUserBonus === null && $depositBonus === null) {
                        $bonus->activate();
                        session()->flash('showDeposit', true);
                    }
                }

                // add to segments
                if ($bonus && $this->bonusSegments->count() > 0) {
                    $ids = $this->bonusSegments->pluck('id')->toArray();
                    KBonus::getInstance()->addBonusToSegmentsIds($bonus->getId(), $ids);
                }
                if ($bonus !== null && $this->affiliate_id !== null) {
                    KBonus::getInstance()->addBonusToSegmentByName($bonus->getId(), "promo partner id:".$this->affiliate_id);;
                }
//                // segment by game name
//                if (!empty($bonus->game_id) && !empty($bonus->game_provider)) {
//                    $game = GamesManager::getProvider($bonus->game_provider)?->getGame('', $bonus->game_id, true);
//                    if ($game) {
//                        KBonus::getInstance()->addBonusToSegmentByName($bonus->getId(), $game->getName());
//                    }
//                }
                return $bonus;
            }
        }
        return null;
    }

    public static function getFirstActive(): ?BonusPromocode
    {
        return BonusPromocode::where('active_to', '>', now()->format('Y-n-d H:i:s'))->first();
    }

    public function getType(): SlcItemType
    {
        return $this->slc_type;
    }

    public function setSlcType(SlcItemType $slc_type): void
    {
        $this->slc_type = $slc_type;
    }

    public function getBonusType(): int
    {
        return $this->bonus_type;
    }

    public function getAmount(): int
    {
        return $this->amount?? 1;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getParams(): string
    {
        return $this->bonus_params;
    }

    public function getAllowedGames(): string
    {
        return $this->allowed_games?? '';
    }

    public function getInfo(): ShopItemInfo
    {
        return  App::make(SlcItemFactory::class)->getItemInfo($this);
    }

    public function getActivator(): SlcItemActivator
    {
        return App::make(SlcItemFactory::class)->getItemActivator($this);
    }

    public function getPromoType(): PromoType
    {
        return $this->promo_type;
    }

    public function setPromoType(PromoType $promo_type): void
    {
        $this->promo_type = $promo_type;
    }

    // slc item stubs
    public function getImage(): ?string
    {
        return null;
    }

    public function getDescription(?string $locale = null): ?string
    {
        return null;
    }

    public function getName(?string $locale = null): ?string
    {
        return null;
    }

    public function getTexts(): ?string
    {
        return null;
    }

    public function bonusSegments(): BelongsToMany
    {
        return $this->belongsToMany(BonusSegment::class);
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
