<?php

namespace App\Models\Bonuses;

use App\GamesProviders\Game;
use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\IGameWallet;
use App\Helpers\Hlog;
use App\Models\BonusCurrencyConfig;
use App\Models\Bonuses\Contracts\BonusRenderer;
use App\Models\Bonuses\Render\DefaultBonusRenderer;
use App\Models\Bonuses\Segments\BonusSegment;
use App\Models\Bonuses\Segments\BonusSegmentAttachable;
use App\Models\BonusPatterns;
use App\Models\BonusStatistic;
use App\MUser;
use App\Services\Games\GameManagerApi;
use App\UserCommands\UserCommand;
use App\UserWallet;
use App\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Class UserBonus
 * Базовый класс бонусов
 * @property int $id
 * @property int $user_id
 * @property int $type
 * @property float $money_available
 * @property float $money_paid_out
 * @property float $total_win
 * @property float $total_bet
 * @property float $total_bet_for_payout
 * @property float $wager_to_complete
 * @property float $multiplier_to_complete
 * @property float $wager_to_payout
 * @property float $multiplier_to_payout
 * @property float $total_money_spent
 * @property float $money_to_activate
 * @property float $real_money_spent
 * @property float $multiplier
 * @property int $status
 * @property int $spins_total
 * @property int $spins_available
 * @property int $spins_made
 * @property int $created_at
 * @property int $updated_at
 * @property int $active_to
 * @property int $hours_active
 * @property float $max_win
 * @property int $tries
 * @property int $hall_id
 * @property int $currency
 * @property int $pattern_id
 * @property int $pattern_status
 * @property string $bonus_prizes
 * @property string $given_prizes
 * @property string $enable_after
 * @property bool $games_limited
 * @property string $allowed_games
 * @property string $json_params
 * @property int $creator_type
 * @property int $creator_id
 * @property string $game_provider
 * @property string $game_id
 * @property ?string $title
 * @property ?string $img_url
 * @property int $marker
 * @property int $completed_at
 * @property ?BonusPatterns pattern
 * @property MUser user
 * @property Collection|BonusSegment[] $bonusSegments
 */
class UserBonus extends Model implements VegasBonusInterface, DepositBonusInterface, BonusSegmentAttachable
{
    const TYPE_NON_DEPOSIT = 2;
    const TYPE_FREE_SPINS = 3;
    const TYPE_PLUS_MINUS = 5;
    const TYPE_INSURANCE = 6;          // Процент возврата за проигрыш в игре. не используется
    const TYPE_SUPER_FREE_SPINS = 10;
    const TYPE_SUPER_FREE_SPINS_PLAYBACK = 11;
    const TYPE_SUPER_FS_CASHBACK = 12;
    const TYPE_SUPER_FS_CASHBACK_PB = 13;
    const TYPE_FILTHY_FS = 14;
    const TYPE_FILTHY_FS_WASH_OFF = 15;
    const TYPE_WEEKLY_CASH_BACK = 16;
    const TYPE_BEZDEP = 17;
    const TYPE_FREE_SPINS_WAGER = 21;
    const TYPE_EVENT_CASH_BACK = 22;
    const TYPE_DEPOSIT_WAGER = 23;
    const TYPE_DEPOSIT_PRIZE = 24;
    const TYPE_DEPOSIT_FFS_TNT = 25;
    const TYPE_NON_DEPOSIT_WAGER = 26;
    const TYPE_CHOOSE_WAGER_FS2 = 27;
    const TYPE_SMART_WAGER = 28;
    const TYPE_TOURNAMENT_WIN = 29; // -
    const TYPE_PPLAY_FS = 30; //49
    const TYPE_PPLAY_SMART_FS = 31;
    const TYPE_QT_FS_WAGER = 32;
    const TYPE_QT_SMART_FS = 33;
    const TYPE_SLEG_FS = 34; //49
    const TYPE_SLEG_SMART = 35;
    const TYPE_PP_NONDEP = 36;
    const TYPE_SLEG_NONDEP = 37;
    const TYPE_PP_FS_MENU = 38;
    const TYPE_SLEG_FS_MENU = 39;
    const TYPE_CASHBACK_MONEY = 40;
    const TYPE_SWISS_FS_WAGER = 41;
    const TYPE_PPLAY_SFS_DYNAMIC = 42;
    const TYPE_SLEG_SFS_DYNAMIC = 43;
    const TYPE_MULTI_DAYS = 44;
    const TYPE_PLUS_MINUS_DEP = 45;
    const TYPE_SHARED_MONEY = 46;
    const TYPE_SHARED_MONEY_FS = 47;
    const TYPE_WELCOME_BONUS_PACK = 48;
    const TYPE_PS_FS = 49;
    const TYPE_PS_FS_MENU = 50;
    const TYPE_PS_SFS_DYNAMIC = 51;
    const TYPE_PS_SMART = 52;
    const TYPE_PS_NON_DEP = 53;
    const TYPE_NON_DEP = 54;

    const BONUS_PATTERN = -4;

    const STATUS_NOT_ACTIVATED = 0; // Ожидание активации
    const STATUS_ACTIVE = 1; // Активен
    const STATUS_DONE = 2; // Завершён
    const STATUS_CANCELED = 3;  // Отменен
    const STATUS_PENDING = 4; // Ожидает
    const STATUS_EXPIRED = 5; // Истёк
    const STATUS_WIN = 6; // Выигран
    const STATUS_WAITING_FOR_PAYMENT = 7; // Ожидает оплаты
    const STATUS_WAITING_FOR_AGREEMENT = 8; // Ожидает активации
    const STATUS_FILTHY = 9; // Отмывочный (не используется)
    const STATUS_WAITING_FOR_LOSE = 10; // Ожидает проигрыша
    const STATUS_LOCKED = 11; // Заблокирован
    const STATUS_BUY_REQUIRED = 12; // Требует покупки (не используется)
    const STATUS_ERROR = 13; // Ошибка
    const STATUS_DEPOSIT_LISTEN = 14; // Требует уведомления об оплате

    const SET_WAGER_TO_COMPLETE_MULTIPLIER = 'wager_to_complete_multiplier';
    const SET_WAGER_TO_PAYOUT_MULTIPLIER = 'wager_to_payout_multiplier';
    const SET_BONUS_SPINS = 'bonus_spins';
    const SET_MAX_BET = 'max_bet';
    const SET_BONUS_MONEY = 'bonus_money';
    const SET_TIME_FOR_ACTIVATION = 'time_for_activation';
    const SET_MONEY_MULTIPLIERS = 'money_multipliers';
    const SET_MONEY_TO_ACTIVATE = 'money_to_activate';

    const CREATOR_DEFAULT = 0;
    const CREATOR_TOURNAMENT = 1;
    const CREATOR_PROMO = 2;
    const CREATOR_EVENT = 3;
    const CREATOR_SCHEDULE = 4;
    const CREATOR_BONUS_AVAILABLE = 5;
    const CREATOR_PRECONF_BONUS = 6;
    const CREATOR_BONUS_SET = 7;

    const MAX_WIN = 30000;

    /** @var Carbon */
    private $_expirationDate;

    public $table = 'users_bonuses';

    public string $curr_name = ""; //currency name

    protected $guarded = [];

    private $_availableGames;

    protected string $_error = "";
    protected string $_sub_id = "";

    /**
     * Возвращает ид бонуса
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Устанавливает _sub_id
     * используется для идентификации в bonus available
     * @param string $id
     * @return void
     */
    public function setSubId(string $id): void
    {
        $this->_sub_id = $id;
    }

    /**
     * Возвращает _sub_id
     * Используется когда id недоступен (бонус еще не создан),
     * чтобы указать ид контейнера бонуса (BonusAvailable)
     * @return string
     */
    public function getSubId(): string
    {
        return $this->_sub_id;
    }

    /**
     * Возвращает тайтл бонуса.
     * Если установлен "перекрывает" дефалтное название бонуса
     * @return string
     */
    public function getTitle(): string
    {
        return __($this->title) ?? $this->getName();
    }

    /**
     * Возвращает катринку бонуса
     * @param string|null $theme
     * @return string
     */
    public function getImgUrl(?string $theme = 'dark'): string
    {
        $url = $this->img_url ?? (file_exists(public_path("/img/bonuses/bonus_" . $this->type . ".png")) ? "/img/bonuses/bonus_" . $this->type . ".png" : '/img/bonuses/bonus_bg.png');
//        if ($theme === 'light') {
//            $url = explode('.', $url)[0].'_light.'.explode('.', $url)[1];
//        }
        return $url . '?t=' . $this->id;
    }


    /**
     * Является ли бонус депозитным.
     * Используется в SlcBonusItemInfo для определения имени
     * @return bool
     */
    public function isDeposit(): bool
    {
        return in_array($this->type, [
            self::TYPE_DEPOSIT_WAGER,
            self::TYPE_DEPOSIT_PRIZE,
        ]);
    }

    /**
     * Является ли бонус бездепом.
     * Используется в SlcBonusItemInfo
     * @return bool
     */
    public function isNonDeposit(): bool
    {
        return in_array($this->type, [
            self::TYPE_PS_NON_DEP,
            self::TYPE_PP_NONDEP,
            self::TYPE_SLEG_NONDEP,
        ]);
    }

    /**
     * Является ли бонус фриспиновым
     * @return bool
     */
    public function isFreeSpins(): bool
    {
        return $this->spins_total > 0;
    }

    /**
     * Возвращает ошибку активации бонуса
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * Возвращает ид валюты бонуса
     * @return int
     */
    public function getCurrency(): int
    {
        return $this->currency;
    }

    /**
     * Возвращает связь с MUser
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(MUser::class, 'id', 'user_id');
    }

    /**
     * Возвращает ид юзера для которого создан бонус
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * Возвращает общее к-во спинов
     * @return int
     */
    public function getSpinsTotal(): int
    {
        return $this->spins_total;
    }

    /**
     * Устанавливает общее к-во спинов
     * @param int $spins_total
     * @return void
     */
    public function setSpinsTotal(int $spins_total): void
    {
        $this->spins_total = $spins_total;
    }

    /**
     * Возвращает доступное (оставшиеся) к-во спинов
     * @return int
     */
    public function getSpinsAvailable(): int
    {
        return $this->spins_available;
    }

    /**
     * Устанавливает доступное (оставшиеся) к-во спинов
     * @param int $spins_available
     * @return void
     */
    public function setSpinsAvailable(int $spins_available): void
    {
        $this->spins_available = $spins_available;
    }

    /**
     * Возвращает к-во выполненных спинов
     * @return int
     */
    public function getSpinsMade(): int
    {
        return $this->spins_made;
    }

    /**
     * Устанавливает к-во выполненных спинов
     * @param int $spins_made
     * @return void
     */
    public function setSpinsMade(int $spins_made): void
    {
        $this->spins_made = $spins_made;
    }

    /**
     * Возвращает доступную сумму бонусных средств
     * @return float
     */
    public function getMoneyAvailable(): float
    {
        return $this->money_available;
    }

    /**
     * Устанавливает доступную сумму бонусных средств
     * @param float $money_available
     * @return void
     */
    public function setMoneyAvailable(float $money_available): void
    {
        $this->money_available = $money_available;
    }

    /**
     * Указывает что у пользователя есть бонусы которые должны быть отменены при выплате
     * @param int $user_id
     * @return bool
     */
    public static function userHasCancelableBonuses(int $user_id): bool
    {
        return UserBonus::where('user_id', $user_id)
                ->whereIn('type', self::getPayoutCancelableTypes())
                ->whereIn('status', [
                    self::STATUS_PENDING,
                    self::STATUS_ACTIVE,
                    self::STATUS_WAITING_FOR_PAYMENT,
                    self::STATUS_WAITING_FOR_LOSE,
                ])->first() !== null;
    }

    /**
     * Возвращает к-во бонусов доступных пользователю для игры.
     * Включаяя активные и те которые можно активировать.
     * @param int $user_id
     * @return int
     */
    public static function getAvailableBonusesCount(int $user_id): int
    {
        return UserBonus::where('user_id', $user_id)
                ->whereIn('status', [
                    self::STATUS_NOT_ACTIVATED,
                    self::STATUS_WAITING_FOR_AGREEMENT,
                    self::STATUS_PENDING,
                    self::STATUS_ACTIVE,
                    self::STATUS_WAITING_FOR_PAYMENT,
                    self::STATUS_WAITING_FOR_LOSE,
                ])->count();
    }

    /**
     * Возвращает типы бонусов, которые должны быть отменены при выплате
     * @return int[]
     */
    public static function getPayoutCancelableTypes(): array
    {
        return [
            self::TYPE_SMART_WAGER,
            self::TYPE_BEZDEP,
            self::TYPE_EVENT_CASH_BACK,
            self::TYPE_SHARED_MONEY,
            self::TYPE_SHARED_MONEY_FS,
        ];
    }

    /**
     * Возвращает первый доступный не активированный бонус
     * @param int $user_id
     * @return UserBonus|null
     * @throws \Exception
     */
    public static function firstUnactivatedBonus(int $user_id): ?UserBonus
    {
        $bonus = UserBonus::where('user_id', $user_id)
            ->whereIn('status', [
                self::STATUS_PENDING,
                self::STATUS_WAITING_FOR_PAYMENT,
                self::STATUS_WAITING_FOR_AGREEMENT,
            ])->first();
        if ($bonus !== null) $bonus = self::convertByType($bonus);
        return $bonus;
    }

    /**
     * Создает бонус из json строки с параметрами бонуса
     * @param int $bonus_type тип бонуса
     * @param string $params параметры
     * @param int $user_id ид юзера
     * @param bool $tmp временное создание бонус (не сохраняет после создание, только возвращает бонус)
     * @return UserBonus
     * @throws \Exception
     */
    public static function fromParams(int $bonus_type, string $params, int $user_id, bool $tmp = true): UserBonus
    {
        $bonus = new UserBonus();
        $bonus->type = $bonus_type;
        $bonus = UserBonus::convertByType($bonus);
        return $bonus->createFromParams($params, $user_id, null, false, $tmp);
    }


    /**
     * Возвращает первый доступный активный бонус.
     * @param int $userId ид юзера
     * @param null|int $currency ид валюты
     * @return UserBonus|null
     * @throws \Exception
     */
    public static function findFirstActiveBonus(int $userId, ?int $currency = null): ?UserBonus
    {
        if (!$userId) return null;
        if ($currency === null) {
            /** @var MUser $user */
            $user = MUser::find($userId);
            $currency = $user->currency;
        }

        $bonus = UserBonus::where(['status' => UserBonus::STATUS_ACTIVE])
            ->where('user_id', '=', $userId)
            ->where('currency', $currency)
            ->where('type', '!=', UserBonus::TYPE_INSURANCE)
            ->where(function ($query) {
                $query->where('money_available', '>', 0)
                    ->orWhere(function ($query) {
                        $query->where('spins_available', '>=', 0);
                    });
            })
            ->first();

        if ($bonus !== null) {
            return static::convertByType($bonus);
        }

        return $bonus;
    }

    /**
     * Возвращает активный бонус для указанной игры
     * @param int $user_id
     * @param string $provider_id
     * @param string $game_id
     * @param int|null $currency
     * @return UserBonus|null
     * @throws \Exception
     */
    public static function getFirstActiveGameBonus(int $user_id, string $provider_id, string $game_id, ?int $currency = null): ?UserBonus
    {
        if (!$user_id) return null;
        if ($currency === null) {
            /** @var MUser $user */
            $user = MUser::find($user_id);
            $currency = $user->currency;
        }

        $bonus = UserBonus::where(['status' => UserBonus::STATUS_ACTIVE])
            ->where('user_id', $user_id)
            ->where('currency', $currency)
            ->where('type', '!=', UserBonus::TYPE_INSURANCE)
            ->where(function ($query) {
                $query->where('money_available', '>', 0)
                    ->orWhere(function ($query) {
                        $query->where('spins_available', '>=', 0);
                    });
            })
            ->where('game_provider', $provider_id)
            ->where('game_id', $game_id)
            ->first();

        if ($bonus !== null) {
            return UserBonus::convertByType($bonus);
        }

        return $bonus;
    }


    /**
     * Конвертирует бонус в модель/класс соответствующую типу бонуса
     * @param UserBonus $bonus
     * @throws \Exception
     */
    public static function convertByType(UserBonus $bonus)
    {
        $bonus->type = intval($bonus->type);
        $bonus = match ($bonus->type) {
            self::TYPE_NON_DEPOSIT => new NonDepositBonus($bonus->attributes),
            self::TYPE_FREE_SPINS => new FreeSpinsBonus($bonus->attributes),
            self::TYPE_PLUS_MINUS => new PlusMinusBonus($bonus->attributes),
            self::TYPE_FREE_SPINS_WAGER => new WagerFreeSpinsBonus($bonus->attributes),
            self::TYPE_SUPER_FREE_SPINS => new SuperFreeSpinsBonus($bonus->attributes),
            self::TYPE_SUPER_FREE_SPINS_PLAYBACK => new SuperFreeSpinsPlayback($bonus->attributes),
            self::TYPE_EVENT_CASH_BACK => new CashbackEventBonus($bonus->attributes),
            self::TYPE_DEPOSIT_WAGER => new DepositWagerBonus($bonus->attributes),
            self::TYPE_SUPER_FS_CASHBACK => new SuperFsCashbackBonus($bonus->attributes),
            self::TYPE_SUPER_FS_CASHBACK_PB => new SuperFsCashbackPbBonus($bonus->attributes),
            self::TYPE_FILTHY_FS => new FilthyFreeSpinsBonus($bonus->attributes),
            self::TYPE_FILTHY_FS_WASH_OFF => new FilthyFsWashOffBonus($bonus->attributes),
            self::TYPE_BEZDEP => new BezdepBonus($bonus->attributes),
            self::TYPE_DEPOSIT_PRIZE => new DepositPrizeBonus($bonus->attributes),
            self::TYPE_DEPOSIT_FFS_TNT => new DepositFFrespinsTntBonus($bonus->attributes),
            self::TYPE_NON_DEPOSIT_WAGER => new NonDepWagerBonus($bonus->attributes),
            self::TYPE_CHOOSE_WAGER_FS2 => new ChooseWagerFs2($bonus->attributes),
            self::TYPE_SMART_WAGER => new SmartWagerBonus($bonus->attributes),
            self::TYPE_TOURNAMENT_WIN => new TournamentWinBonus($bonus->attributes),
            self::TYPE_PPLAY_FS => new PPlayFreeSpins($bonus->attributes),
            self::TYPE_PPLAY_SMART_FS => new PPlaySmartFs($bonus->attributes),
            self::TYPE_QT_FS_WAGER => new QtFsWager($bonus->attributes),
            self::TYPE_QT_SMART_FS => new QtSmartFs($bonus->attributes),
            self::TYPE_SLEG_FS => new SlegFsWager($bonus->attributes),
            self::TYPE_SLEG_SMART => new SlegSmartFs($bonus->attributes),
            self::TYPE_PP_NONDEP => new PPNonDepFs($bonus->attributes),
            self::TYPE_SLEG_NONDEP => new SlegNonDepFs($bonus->attributes),
            self::TYPE_PP_FS_MENU => new PPlayFSMenu($bonus->attributes),
            self::TYPE_SLEG_FS_MENU => new SlegFsMenu($bonus->attributes),
            self::TYPE_CASHBACK_MONEY => new CashbackMoneyBonus($bonus->attributes),
            self::TYPE_SWISS_FS_WAGER => new SwissFsWager($bonus->attributes),
            self::TYPE_PPLAY_SFS_DYNAMIC => new PPlaySfsDyn($bonus->attributes),
            self::TYPE_SLEG_SFS_DYNAMIC => new SlegSfsDyn($bonus->attributes),
            self::TYPE_MULTI_DAYS => new MultiDaysBonus($bonus->attributes),
            self::TYPE_PLUS_MINUS_DEP => new PlusMinusDepBonus($bonus->attributes),
            self::TYPE_SHARED_MONEY => new SharedMoneyBonus($bonus->attributes),
            self::TYPE_SHARED_MONEY_FS => new SharedMoneyFsBonus($bonus->attributes),
            self::TYPE_WELCOME_BONUS_PACK => new WelcomeBonusPack($bonus->attributes),
            self::TYPE_PS_FS => new PSFreeSpins($bonus->attributes),
            self::TYPE_PS_FS_MENU => new PSFSMenu($bonus->attributes),
            self::TYPE_PS_SFS_DYNAMIC => new PSSfsDyn($bonus->attributes),
            self::TYPE_PS_SMART => new PSSmartFs($bonus->attributes),
            self::TYPE_PS_NON_DEP => new PSNonDepFs($bonus->attributes),
            self::TYPE_NON_DEP => new NonDepBonus($bonus->attributes),
            default => throw new \Exception('Can`t convert bonus'),
        };
        $bonus->exists = true;
        $bonus->curr_name = UserWallet::currencyName($bonus->currency ?? UserWallet::CURRENCY_RUB);
        return $bonus;
    }

    /**
     * Возвращает бонус "страховку"
     * @param int $userId
     * @param int $userId
     * @return UserBonus|null
     * @deprecated
     */
    public static function getInsurance($userId)
    {
        /** @var MUser $user */
        $user = MUser::find($userId);
        return UserBonus::where(['status' => UserBonus::STATUS_ACTIVE])
            ->where('user_id', $userId)
            ->where('hall_id', $user->hall_id)
            ->where('type', UserBonus::TYPE_INSURANCE)
            ->first();
    }

    /**
     * Возвращает бонус на проигрыш
     * @param MUser $user
     * @return UserBonus|null
     * @throws \Exception
     * @deprecated
     */
    public static function getWaitingLose(MUser $user): ?UserBonus
    {
        $bonus = UserBonus::where(['status' => UserBonus::STATUS_WAITING_FOR_LOSE])
            ->where('user_id', $user->id)
            ->where('currency', $user->currency)
            ->first();
        if ($bonus) {
            return static::convertByType($bonus);
        }
        return null;
    }

    /**
     * Возвращает бонус ожидающий оплату (депозит)
     * @param MUser $user
     * @return UserBonus|null
     * @throws \Exception
     */
    public static function getWaitingDeposit(MUser $user): ?UserBonus
    {
        $bonus = UserBonus::where('user_id', $user->id)
            ->where('currency', $user->currency)
            ->where('status', UserBonus::STATUS_WAITING_FOR_PAYMENT)
            ->first();
        if ($bonus !== null) {
            return UserBonus::convertByType($bonus);
        }
        return null;
    }

    /**
     * Возвращает название валюты бонуса (RUB, UAH...)
     * @return string
     */
    public function getCurrencyName(): string
    {
        return Wallet::currencyName($this->currency);
    }


    /**
     * Возвращает настройку бонуса (не используется)
     * @param string $setting
     * @param mixed $default
     * @return float|null
     * @deprecated
     */
    public static function getSetting($setting, $default)
    {
        return null;
    }

    /**
     * Возвращает название типа
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Translation\Translator|string|null
     * @deprecated
     */
    public function getTypeName()
    {
        switch ($this->type) {
            case self::TYPE_NON_DEPOSIT:
                return __('bonuses.activeCashBackName');
            case self::TYPE_FREE_SPINS:
                return __('bonuses.freeSpinsName');
            case self::TYPE_PLUS_MINUS:
                return __('bonuses.plusMinusName');
            case self::TYPE_INSURANCE:
                return __('bonuses.insuranceName');
            case self::TYPE_FREE_SPINS_WAGER:
                return __('bonuses.nonDepositName');
            case self::TYPE_SUPER_FREE_SPINS:
                return __('bonuses.freeSpinsName');
            case self::TYPE_SUPER_FREE_SPINS_PLAYBACK:
                return __('bonuses.freeSpinsName');
            case self::TYPE_EVENT_CASH_BACK:
                return __('bonuses.stockCashBackName');
            case self::TYPE_DEPOSIT_WAGER:
                return __('bonuses.depBonusName');
        }
        return 'Unknown';
    }

    /**
     *
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Translation\Translator|string|null
     */
    public function getStatusName(): string
    {
        return match ($this->status) {
            self::STATUS_NOT_ACTIVATED => __('bonuses.notActivated'),
            self::STATUS_ACTIVE => __('bonuses.active'),
            self::STATUS_PENDING => __('bonuses.pending'),
            self::STATUS_CANCELED => __('bonuses.canceled'),
            self::STATUS_EXPIRED => __('bonuses.expired'),
            self::STATUS_DONE => __('bonuses.done'),
            self::STATUS_WIN => __('bonuses.won'),
            self::STATUS_WAITING_FOR_PAYMENT => __('bonuses.waitingForPayment'),
            self::STATUS_WAITING_FOR_AGREEMENT => __('bonuses.waitingForActivation'),
            self::STATUS_FILTHY => __('bonuses.laundering'),
            self::STATUS_WAITING_FOR_LOSE => __('bonuses.activationOnLose'),
            self::STATUS_LOCKED => __('bonuses.locked'),
            self::STATUS_BUY_REQUIRED => __('bonuses.buyRequired'),
            self::STATUS_ERROR => __('bonuses.error'),
            self::STATUS_DEPOSIT_LISTEN => __('bonuses.waitingForPayment'),
            default => __('bonuses.notActivated'),
        };
    }

    /**
     * Создает бонус из json строки параметров.
     * @param string $params json параметры
     * @param int $user_id ид юзера
     * @param int|null $currency валюта бонуса
     * @param bool $keep_params не изменят параметры по валюте
     * @param bool $tmp временное создание, не сохраняет бонус, только возвращает созданный
     */
    public function createFromParams(string $params, int $user_id, ?int $currency = null, ?bool $keep_params = false, ?bool $tmp = false): UserBonus
    {
        throw new \Exception('Create bonus in base class');
    }

    /**
     * Возвращает параметр из переданных во время создания в json параметрах
     * @param string $key
     * @param $default
     * @return mixed|null
     */
    public function getJsonParam(string $key, $default = null): mixed
    {
        $params = json_decode($this->json_params, true) ?? [];
        return $params[$key] ?? $default;
    }

    /**
     * Устанавливает параметр в json параметрах бонуса
     * @param string $key
     * @param $value
     * @return void
     */
    public function setJsonParam(string $key, $value = null): void
    {
        $params = json_decode($this->json_params, true) ?? [];
        if ($value === null) {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
        $this->json_params = json_encode($params);
    }


    /**
     * Актуализация бонуса. Обрабатывает текущее состояние.
     * Должен переопределяется в подклассах.
     */
    public function actualize()
    {
        if ($this->isExpired()) return;
    }

    /**
     * Заблокирован ли бонус.
     * Используется в бонусах которые становятся доступны только после определенных условий.
     * Например: после завершения бонуса из определенного шаблона.
     * @return bool
     */
    public function isLocked(): bool
    {
        if ($this->status != static::STATUS_LOCKED) return false;

        if (empty($this->enable_after) || !is_array(json_decode($this->enable_after, true))) {
            $this->status = static::STATUS_NOT_ACTIVATED;
            $this->save();
            Log::error('Locked bonus has no enable_after ids');
            return false;
        }

        $bonuses = json_decode($this->enable_after);
        foreach ($bonuses as $bonus_pattern_id) {
            $bonus = UserBonus::where('user_id', Auth::id())
                ->where('pattern_id', $bonus_pattern_id)
                ->whereNotIn('status', [UserBonus::STATUS_CANCELED, UserBonus::STATUS_EXPIRED, UserBonus::STATUS_DONE])->first();
            if ($bonus) return true;
        }

        $this->status = static::STATUS_NOT_ACTIVATED;
        $this->save();
        return false;
    }

    /**
     * Проверяет не истек ли срок жизни бонуса.
     * @return bool
     */
    public function isExpired(): bool
    {
        if ($this->status == self::STATUS_EXPIRED) {
            return true;
        }

        if ($this->active_to === null) return false;

        if (strtotime($this->active_to) < time()) {
            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_EXPIRED, $this->money_available, $this->status, self::STATUS_EXPIRED);
            $this->status = self::STATUS_EXPIRED;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Активирует бонус и возвращает была ли активация успешной.
     * @return bool
     */
    public function activate(): bool
    {
        // This method may be implemented in the subclass.
        return false;
    }

    /**
     * Возвращает название бонуса
     * @return string
     */
    public function getName(): string
    {
        return "unknown";
    }

    /**
     * Возвращает описание бонуса.
     * @return string
     */
    public function getDescription(): string
    {
        return "Бонус #$this->id";
    }

    /**
     * Короткое описание бонуса
     * @return string
     */
    public function getShortDescription(): string
    {
        if (view()->exists('common.bonus-descriptions.t' . $this->type)) return view('common.bonus-descriptions.t' . $this->type, ['bonus' => $this])->render();
        return 'NO DESCRIPTION ' . $this->type;
    }

    /**
     * Возвращает название файла описания бонуса.
     * Используется для определения view файла с описанием,
     * находящегося в /common/bonus-descriptions
     * @return string|null
     */
    public function getDescriptionName(): ?string
    {
        return null;
    }

    /**
     * Возвращает требует ли бонус оплаты
     * @return bool
     */
    public function isPaymentRequired(): bool
    {
        return $this->status == self::STATUS_WAITING_FOR_PAYMENT;
    }

    /**
     * Возвращает, требует ли бонус активации
     * @return bool
     */
    public function isAgreementRequired(): bool
    {
        return ($this->status == self::STATUS_WAITING_FOR_AGREEMENT | $this->status == self::STATUS_NOT_ACTIVATED);
    }

    /**
     * Доступен ли прогресс бонуса
     * @return bool
     */
    public function isProgressAvailable(): bool
    {
        return false;
    }

    /**
     * Возвращает строку прогресса бонуса. (Сколько сделано спинов, отыграно вейджера ....)
     * @return string
     */
    public function getProgressHint(): string
    {
        return '';
    }

    /**
     * Возвращает процент отыгрыша бонуса
     * @return int
     */
    public function getProgressPercents(): int
    {
        return 0;
    }

    /**
     * Возвращает сумму депозита требуемую для активации бонуса.
     * @return float
     */
    public function getRequiredSum(): float
    {
        return $this->money_to_activate;
    }

    /**
     * Мохно ли отменить бонус.
     * @return bool
     */
    public function isCancelable(): bool
    {
        if ($this->isExpired()) return false;

        return $this->status != self::STATUS_EXPIRED
            && $this->status != self::STATUS_DONE
            && $this->status != self::STATUS_CANCELED;
    }

    /**
     * Активен ли бонус
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * Завершен ли бонус
     * @return bool
     */
    public function isDone(): bool
    {
        return $this->status == self::STATUS_DONE;
    }

    /**
     * Отменен ли бонус.
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->status == self::STATUS_CANCELED;
    }

    /**
     * Находится ли бонус в состоянии ожидания.
     * Бонусы на которые нельзя играть напрямую не должны быть в активном статусе.
     * Например кешбэк бонусы находятся в состоянии ожидания а не активности.
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status == self::STATUS_PENDING;
    }

    /**
     * Бонус требует оплаты реальных средств (не используется)
     * @return bool
     */
    public function realPayRequired(): bool
    {
        return false;
    }

    /**
     * Бонус должен быть уведомлен о совершении оплаты.
     * @return bool
     */
    public function isListeningDeposit(): bool
    {
        return $this->status == self::STATUS_DEPOSIT_LISTEN;
    }

    /**
     * Вызывается при совершении депозита для бонусов в состоянии STATUS_DEPOSIT_LISTEN (isListeningDeposit)
     * @param float $amount
     * @param int $currency
     * @return bool
     */
    public function onDeposit(float $amount, int $currency): bool
    {
        return false;
    }

    /**
     * Завершен ли бонус
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status == self::STATUS_EXPIRED
            || $this->status == self::STATUS_DONE
            || $this->status == self::STATUS_CANCELED;
    }

    /**
     * Возвращает дату истечения бонуса
     * @return Carbon
     */
    public function getExpirationDate(): Carbon
    {
        if ($this->_expirationDate === null) {
            if ($this->active_to === null) return now()->addHours(12)->locale('ru');
            $this->_expirationDate = Carbon::createFromFormat('Y-m-d H:i:s', $this->active_to)->locale('ru');
        }
        return $this->_expirationDate;
    }


    /**
     * Возвращяет общая сумма выигранных денег
     * @return float
     */
    public function getTotalMoneyWon(): float
    {
        if ($this->status != self::STATUS_DONE) return 0;
        return $this->money_available + $this->money_paid_out;
    }

    /**
     * Возвращает сумму отыгранных денег для перевоза средств на баланс пользователя.
     * Используется в "отмываемых"(отыгруемых на реальные средства) бонусах (в данный момент уже не используются)
     * @return float
     */
    public function getMoneyToPayout(): float
    {
        return 0;
    }

    /**
     * Возвращает сумму оставшуюся для "отмывания"(отыгрывания на реальные средства)
     * @return float
     */
    public function getMoneyAvailableForPlay(): float
    {
        return 0;
    }

    /**
     * Бонус активируется с помощю меню
     * @return bool
     */
    public function isActivationMenu(): bool
    {
        return false;
    }

    /**
     * Возвращает меню для активации
     * @return array|null
     */
    public function getActivationMenu(): ?array
    {
        return null;
    }

    /**
     * Активация значением меню
     * @param $option_value
     * @return bool
     */
    public function ActivateByMenuOption($option_value): bool
    {
        return $this->activate();
    }

    /**
     * Отменяет бонус
     * @return void
     */
    public function cancelBonus()
    {
        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_CANCEL,
            $this->money_available, $this->status, self::STATUS_CANCELED, $this->pattern_id);
        $this->status = static::STATUS_CANCELED;
        $this->save();
    }

    /**
     * Переводит доступные отыгранные средства на реальный баланс
     * Для "отмываемых" бонусов (не используются)
     * @return bool
     */
    public function transferBonusMoney(): float
    {
        return 0;
    }

    /**
     * Активация бонусов ожидающих оплаты.
     * Вызывается при совершении оплаты для бонусов в статусе STATUS_WAITING_FOR_PAYMENT
     * Бонусы могут изымать часть денег, если требуют реальные деньги для активации (нигде не используется).
     * @see Payin::completeTransaction
     * @param MUser $user пользователь совершающий депозит
     * @param float $amount сумма депозита
     * @param int $currency ид валюты депозита
     * @return float остаток средств к зачислению на счет
     */
    public function confirmPayment(MUser $user, float $amount, int $currency): float
    {
        return $amount;
    }

    /**
     * Указывает должен ли активный бонус обрабатывает депозит
     * @see Payin::completeTransaction
     * @return bool
     */
    public function shouldProcessPayment(): bool
    {
        return false;
    }

    /**
     * Вызывается при активации бонуса
     * @return void
     * @throws \Exception
     */
    public function onActivate()
    {
//        // если бнус создан из шаблона
//        if ($this->pattern_id) {
//            /** @var BonusPatterns $bonus_pattern */
//            $bonus_pattern = BonusPatterns::find($this->pattern_id);
//            // Выполняет команды из бонусного шаблона (не используются в данный момент)
//            if ($bonus_pattern && !empty($bonus_pattern->on_activate_commands)) {
//                $commands = json_decode($bonus_pattern->on_activate_commands, true);
//                Log::info('commands ' . var_export($commands, true));
//                foreach ($commands as $command) {
//                    $cmd = UserCommand::create(json_decode($command, true));
//                    Log::info('command ' . var_export($cmd, true));
//                    if ($cmd) $cmd->execute(MUser::find($this->user_id), $this->currency, $this);
//                }
//            }
//        }
    }

    /**
     * Разрешена ли игра провайдера для даннго бонуса
     * @param string $providerId
     * @param string $gameId
     * @return bool
     */
    public function isGameAllowedByProvider(string $providerId, string $gameId): bool
    {
        $game = GamesManager::getProvider($providerId)->getGame('', $gameId, true);
        if ($game !== null) return $this->isGameAllowed($game);
        return true;
    }


    /**
     * Разрешена ли игра для бонуса
     * @param Game $game
     * @return bool
     */
    public function isGameAllowed(Game $game): bool
    {
        return $this->getAllowedGames()->getGame($game->getProviderId(), $game->getGroupId(), $game->getId()) !== null;
    }


    /**
     * Устанавливает json строку с разрешенными играми для бонуса
     * @param string|null $games список игр
     * @return void
     */
    public function setAllowedGames(?string $games): void
    {
        if ($games === null) return;
        $json = json_decode($games, true);
        if (!empty($json) && is_array($json)) {
            $this->allowed_games = $games;
            $this->games_limited = true;
            // if one provider and one game set direct url bonus params
            if (count($json) === 1 && count(reset($json)) === 1) {
                $this->game_provider = key($json);
                $this->game_id = current($json[$this->game_provider]);
            }
        }
    }

    /**
     * Возвращает текущий баланс бонуса.
     * Некоторые бонусы могут использовать совместно бонусные средства и реальные, для этого передается кошелек
     * @param IGameWallet|null $game_wallet Игровой кошелек (на данный момент всегда кошелек пользователя)
     * @return int
     */
    public function getBalanceCents(IGameWallet $game_wallet = null): int
    {
        return intval($this->money_available * 100);
    }

    /**
     * Возвращает строку с информацией о текущем статусе бонуса
     * @param IGameWallet|null $game_wallet
     * @return string
     */
    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        return '';
    }

    /**
     * Снимает центы с бонусного баланса.
     * Использовалось для обработки игровых ставок на бонус.
     * Механики перемещены в сервис.
     * @param int $sum
     * @param IGameWallet $game_wallet
     * @param string $system
     * @param string $game_id
     * @return bool|int
     * @deprecated
     */
    public function withdrawCents(int $sum, IGameWallet $game_wallet, string $system, string $game_id)
    {
        if ($this->money_available < ($sum / 100)) return false;
        $this->money_available -= ($sum / 100);
        $this->total_bet += $sum / 100;
        return intval($this->money_available * 100);
    }

    /**
     * Добавляет центы на бонусный баланс.
     * Использовалось для обработки игровых ставок на бонус.
     * Механики перемещены в сервис.
     * @param int $sum
     * @param IGameWallet $game_wallet
     * @param string $system
     * @param string $game_id
     * @return int
     */
    public function depositCents(int $sum, IGameWallet $game_wallet, string $system, string $game_id): int
    {
        $this->money_available += ($sum / 100);
        $this->total_win += $sum / 100;
        return intval($this->money_available * 100);
    }

    /**
     * Актуализация бонуса после совершения игровой операции.
     * Использовалось для обработки игровых ставок на бонус.
     * Механики перемещены в сервис.
     * @param string $game_id
     * @param IGameWallet $game_wallet
     * @param string $system
     * @return void
     * @deprecated
     */
    public function actualizeVegas(string $game_id, IGameWallet $game_wallet, string $system = 'vegas')
    {
        $this->actualize();
    }

    /**
     * Использовалось для обработки игровых ставок на бонус для бонусов с отыгрышем на реальные средства.
     * Механики перемещены в сервис.
     * Данные бонусы уже не используются.
     * @param float $amount
     * @param string $game_id
     * @return void
     * @deprecated
     */
    public function onUserBet(float $amount, string $game_id)
    {
    }

    /**
     * Использовалось для обработки игровых ставок на бонус для бонусов с отыгрышем на реальные средства.
     * Механики перемещены в сервис.
     * Данные бонусы уже не используются.
     * @return void
     */
    public function onUserLose()
    {
    }

    /**
     * Отменяет определенные бонусы при выплате.
     * @param MUser $user
     * @return void
     * @throws \Exception
     */
    public static function cancelPayoutBonuses(MUser $user)
    {
        $bonuses = UserBonus::where('user_id', $user->id)
            ->whereIn('type', [
                self::TYPE_SMART_WAGER,
                self::TYPE_PPLAY_SMART_FS,
                self::TYPE_SLEG_SMART,
                self::TYPE_SHARED_MONEY,
                self::TYPE_SHARED_MONEY_FS,
            ])
            ->wherein('status', [self::STATUS_ACTIVE, self::STATUS_WAITING_FOR_PAYMENT])->get();
        /** @var UserBonus $bonus */
        foreach ($bonuses as $bonus) {
            $bonus = UserBonus::convertByType($bonus);
            $bonus->cancelBonus();
        }
    }

    /**
     * Отменяет кешбэки при выплате
     * @param MUser $user
     * @return void
     */
    public static function cancelCashBackBonuses(MUser $user)
    {
//        CashbackEventBonus::cancelForUser($user);
//
//        UserBonus::where('user_id', $user->id)
//            ->where('type', self::TYPE_BEZDEP)
//            ->wherein('status', [self::STATUS_WAITING_FOR_LOSE, self::STATUS_WAITING_FOR_PAYMENT])
//            ->update(['status' => self::STATUS_CANCELED]);
//        //todo: bonus statistics
    }

    /**
     * Возвращает строку с балансом бонуса
     * Используется встроке бонусного состояния меню пользователя.
     * @return float|string
     */
    public function getBalanceString()
    {
        if ($this->isDone()) {
            return number_format($this->getTotalMoneyWon(), 2, ".", "") . " " . UserWallet::currencySymbol($this->currency);
        }
        if ($this->spins_available > 0) {
            return floor($this->spins_available);
        } else {
            return number_format($this->money_available, 2, ".", "") . " " . UserWallet::currencySymbol($this->currency);
        }
    }

    /**
     * Возвращает строку с указанием текущего типа баланса бонуса (спины либо денежный баланс)
     * Используется встроке бонусного состояния меню пользователя.
     * @return string
     */
    public function getBalanceName(): string
    {
        if ($this->isDone()) {
            return __('bonus.win');
        }

        if ($this->isFreeSpins() > 0) {
            return "spins";
        } else {
            return __('bonus.bonus_balance');
        }
    }


    /**
     * Возвращает коллекцию доступных для бонуса игр.
     * Использует сервис GamesManager.
     * @see GamesManager
     * @return GamesCollection
     */
    public function getAllowedGames(): GamesCollection
    {
        if ($this->_availableGames === null) {

            if ($this->isDirectPlayUrl()) {
                $provider = GamesManager::getProvider($this->game_provider);
                $game = $provider?->getGame('', $this->game_id, true);
                $this->_availableGames = new GamesCollection();
                if ($game !== null) {
                    $this->_availableGames->addGame($game);
                    return $this->_availableGames;
                }
            }

            //default games for bonus type
            $games = GameManagerApi::getInstance()->searchGames(
                groups: $groups ?? [],
                categories: $search_cats ?? [],
                tags: $tags ?? [],
                name: $name ?? '',
                per_page: 0,
                page: 0,
                for_bonus_type: $this->type,
            );
            $this->_availableGames = new GamesCollection();
            foreach ($games->getGames() as $game) $this->_availableGames->addGame($game);
        }
        return $this->_availableGames;
    }

    /**
     * Понижает вейджер на указаный процент
     * @param $percent
     * @return void
     * @deprecated
     */
    public function wagerDiminution($percent)
    {
    }

    /**
     * Преобразует параметры бонуса согласно валюты.
     * Используется, только если для бонуса не указаны параметры в необходимой валюте.
     * Преобразовывает параметры согласно коэффициентам установленных в BonusCurrencyConfig
     * @see BonusCurrencyConfig
     * Не рекомендуется.
     * @param int $currency
     * @param array $bonus_params
     * @return array|mixed
     */
    public function getCurrencyParams(int $currency, array $bonus_params)
    {
        $curr_params_name = 'params_' . UserWallet::currencyName($currency);
        if (isset($bonus_params[$curr_params_name])) {
            return $bonus_params[$curr_params_name];
        }

        $params = BonusCurrencyConfig::getBonusConfig($currency, $this->type);

        if ($params === null) {
            Log::channel('stack')->warning('bonus currency params not found! Currency:' . $currency . ' Bonus id#' . $this->id);
            return $bonus_params;
        }

        if (isset($bonus_params['money'])) $bonus_params['money'] = $bonus_params['money'] * $params->money_k;
        if (isset($bonus_params['freespins']) && $bonus_params['freespins'] > 0) $bonus_params['freespins'] = $bonus_params['freespins'] * $params->spins_k;
        if (isset($bonus_params['wager'])) $bonus_params['wager'] = $bonus_params['wager'] * $params->wager_k;
        if (isset($bonus_params['min_sum'])) $bonus_params['min_sum'] = $bonus_params['min_sum'] * $params->min_deposit_k;
        if (isset($bonus_params['max_sum'])) $bonus_params['max_sum'] = $bonus_params['max_sum'] * $params->max_deposit_k;
        if (isset($bonus_params['win_limit'])) $bonus_params['win_limit'] = $bonus_params['win_limit'] * $params->money_k;
        if (isset($bonus_params['max_win_k'])) $bonus_params['max_win_k'] = $bonus_params['max_win_k'] * $params->max_win_coef_k;
        if (isset($bonus_params['max_win_sum'])) $bonus_params['max_win_sum'] = $bonus_params['max_win_sum'] * $params->money_k;
        if (isset($bonus_params['max_win'])) $bonus_params['max_win'] = $bonus_params['max_win'] * $params->money_k;
        if (isset($bonus_params['max_bet'])) $bonus_params['max_bet'] = $bonus_params['max_bet'] * $params->money_k;
        if (isset($bonus_params['activation_sum'])) $bonus_params['activation_sum'] = $bonus_params['activation_sum'] * $params->money_k;
        if (isset($bonus_params['spins_amount'])) $bonus_params['spins_amount'] = $bonus_params['spins_amount'] * $params->money_k;

        return $bonus_params;
    }

    /**
     * Возвращает url запуска игры на бонус.
     * @return string
     */
    public function getPlayUrl(): string
    {
        // Если бонус у бонуса больше одно игры то переводим на список бонусных игр
        if (!$this->isDirectPlayUrl()) return route('games', [], false) . "?category=bonus";
        // Проверяем доступность игр
        $provider = GamesManager::getProvider($this->game_provider);
        $game = $provider?->getGame('', $this->game_id, true);

        if ($game === null) return route('games', [], false) . "?category=bonus";

        // Адрес запуска игры в режиме бонуса
        return action('EmulatorController@index', [
                'system' => $game->getProviderId(),
                'provider' => $game->getGroupId(),
                'gameId' => $game->getId(),
                'type' => 'real',
            ]) . "?balance=bonus";
    }

    /**
     * Указывает должен ли бонус запускать игру сразу, вместо отображения списка бонусных игр.
     * Если для бонуса доступна только одна игра то устанавливаются поля game_provider и game_id для данной игры
     * и бонус считается DirectPlay.
     * @return bool
     */
    public function isDirectPlayUrl(): bool
    {
        // Если установлен game_provider и game_id
        return (!empty($this->game_provider) && !empty($this->game_id));
    }

    /**
     * Возвращает название игры для прямого запуска.
     * @return string
     */
    public function getGameName(): string
    {
        if (!$this->isDirectPlayUrl()) return '';

        $provider = GamesManager::getProvider($this->game_provider);
        return $provider?->getGame('', $this->game_id, true)?->getName() ?? '';
    }

    /**
     * Обновляет кешированную строку состояния бонуса.
     * Так как обработчики и строка состояния находятся в сервисе GamesManager больше не используется
     * @param IGameWallet $game_wallet
     * @param string $system
     * @param string $game_id
     * @return void
     * @deprecated
     */
    public function refreshBonusStatus(IGameWallet $game_wallet, string $system, string $game_id)
    {
        $status = $this->getStatusString($game_wallet);
        if (empty($status)) {
            Redis::del('bonus-status-' . $this->user_id);
        } else {
            Redis::set('bonus-status-' . $this->user_id, $status);
        }
    }

    /**
     * Указывает что бонус должен принудительно запускать доступные для бонуса игры в бонусном режиме.
     * @return bool
     */
    public function forceToPlay(): bool
    {
        return false;
    }

    /**
     * Указывает что для бонуса не должна отображаться кнопка "играть"
     * @return bool
     */
    public function noPlayButton(): bool
    {
        return false;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getBonusSystemId(): string
    {
        return 'kraken';
    }

    /**
     * Обработчик спинов игровых провайдеров
     * Перемещено в UserManager
     * @param IGameWallet $game_wallet
     * @param string $system
     * @param string $game_id
     * @param float $amount
     * @return float
     * @deprecated
     */
    public function externFreeSpinBet(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float
    {
        return $this->getBalanceCents() / 100;
    }

    /**
     * Обработчик спинов игровых провайдеров
     * Перемещено в UserManager
     * @param IGameWallet $game_wallet
     * @param string $system
     * @param string $game_id
     * @param float $amount
     * @return float
     * @deprecated
     */
    public function externFreeSpinWin(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float
    {
        return $this->getBalanceCents() / 100;
    }

    /**
     * Обработчик спинов игровых провайдеров
     * Перемещено в UserManager
     * @param IGameWallet $game_wallet
     * @param string $system
     * @param string $game_id
     * @param float $amount
     * @return float
     */
    public function externFreeSpinsCompleted(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float
    {
        return $this->getBalanceCents() / 100;
    }

    /**
     * Возвращает систему генерации view специфичную для данного типа бонуса.
     * @return BonusRenderer
     */
    public function getRenderer(): BonusRenderer
    {
        return new DefaultBonusRenderer($this);
    }

    /**
     * Доступны ли все игры
     * @return bool
     */
    public function allGamesAllowed(): bool
    {
        return empty($this->allowed_games) && empty($this->game_id);
    }

    /**
     * Отношение к паттерну создавшему бонус
     * @return HasOne
     */
    public function pattern(): HasOne
    {
        return $this->hasOne(BonusPatterns::class, 'id', 'pattern_id');
    }

    /**
     * Отношение к сегментам бонуса
     * @return BelongsToMany
     */
    public function bonusSegments(): BelongsToMany
    {
        return $this->belongsToMany(BonusSegment::class, 'bonus_segment_user_bonus', 'user_bonus_id', 'bonus_segment_id');
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

    /**
     * Возвращает маркер бонуса
     * @see BonusMarker
     * @return int
     */
    public function getMarker(): int
    {
        return $this->marker;
    }

    /** Устанавливает маркер боунса
     * @see BonusMarker
     * @param int $marker
     * @return void
     */
    public function setMarker(int $marker): void
    {
        $this->marker = $marker;
    }

    /**
     * Операции вызываемые после создания бонуса
     * @return void
     */
    public function afterCreated(): void
    {
        if (empty($this->id)) {
            Hlog::logException(new \Exception('Trying attach bonus with no id to segment'));
            return;
        }
        // привязка к BonusSegment по названию игры
        if (!empty($this->game_provider) && !empty($this->game_id)) {
            $game = GamesManager::getProvider($this->game_provider)?->getGame('', $this->game_id);
            if ($game !== null) {
                BonusSegment::attachOrCreateByName($this, $game->getName());
            }
        }
        $user = MUser::find($this->getUserId());
        $user?->setRedisValue('has_new_bonus', true);

        // запись статистики
        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_CREATED,
            $this->money_available, self::STATUS_NOT_ACTIVATED, $this->status, $this->pattern_id, $this->json_params);
    }

    public static function booted()
    {
        static::created(function ($bonus) {
            $bonus = UserBonus::convertByType($bonus);
            $bonus->afterCreated();
            Log::info('created bonus:' . var_export($bonus, true));
        });
    }

}
