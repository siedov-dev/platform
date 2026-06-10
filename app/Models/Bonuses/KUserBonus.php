<?php

namespace App\Models\Bonuses;

use App\GamesProviders\Game;
use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\IGameWallet;
use App\Helpers\Hlog;
use App\Models\Bonuses\Contracts\BonusRenderer;
use App\Models\Bonuses\Render\DefaultBonusRenderer;
use App\MUser;
use App\Services\Games\GameManagerApi;
use App\Services\KBonus\KBonus;
use App\UserWallet;
use App\Wallet;
use ArrayAccess;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class KUserBonus
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
 * @property ?int $wager_games_segment_id
 * Базовый класс бонусов
 */
class KUserBonus implements BonusInterface, DepositBonusInterface, Arrayable, ArrayAccess
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
    const TYPE_STICKY_FS_MENU = 55;

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
    const STATUS_LOCKED = 11; // Заблокирован (deprecated)
    const STATUS_BUY_REQUIRED = 12; // Требует покупки (не используется)
    const STATUS_ERROR = 13; // Ошибка
    const STATUS_DEPOSIT_LISTEN = 14; // Требует уведомления об оплате
    const STATUS_PAUSE = 15; // Приостановлен
    const STATUS_PAUSE_PAYMENT = 16; // Приостановлен на оплате

    const CREATOR_DEFAULT = 0;
    const CREATOR_TOURNAMENT = 1;
    const CREATOR_PROMO = 2;
    const CREATOR_EVENT = 3;
    const CREATOR_SCHEDULE = 4;
    const CREATOR_BONUS_AVAILABLE = 5;
    const CREATOR_PRECONF_BONUS = 6;
    const CREATOR_BONUS_SET = 7;
    const CREATOR_BONUS_PATTERN = 8;
    const CREATOR_SLC_ITEM = 9;
    const CREATOR_WHEEL = 10;

    protected array $attributes = [];
    protected array $meta = [];

    /** @var Carbon */
    private $_expirationDate;

    public string $curr_name = ""; //currency name

    private $_availableGames;

    protected string $_error = "";
    protected string $_sub_id = "";

    public function __construct(array &$attributes = [], array &$meta = [])
    {
        $this->attributes = $attributes;
        $this->meta = $meta;
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
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->attributes[$offset];
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
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

    public function getMeta(): array
    {
        return $this->meta;
    }

    public static function find(int $id): ?KUserBonus
    {
        return KBonus::getInstance()->getBonus($id);
    }

    /**
     * Возвращает ид бонуса
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id === 0 ? null : $this->id;
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
        if ($this->id === 0 && $this->creator_type === self::CREATOR_BONUS_AVAILABLE) return "ba_" . $this->creator_id;
        return $this->_sub_id;
    }

    /**
     * Возвращает тайтл бонуса.
     * Если установлен "перекрывает" дефалтное название бонуса
     * @return string
     */
    public function getTitle(): string
    {
        if (is_string(__($this->title))) {
            return __($this->title);
        }
        return $this->title?? $this->getName();
    }

    /**
     * Возвращает картинку бонуса
     * @param string|null $theme
     * @return string
     */
    public function getImgUrl(?string $theme = 'dark'): string
    {
        $url = $this->img_url ?? (file_exists(public_path("/img/bonuses/bonus_" . $this->type . ".png")) ? "/img/bonuses/bonus_" . $this->type . ".png" : '/img/bonuses/bonus_bg.png');
        return $url . '?t=' . $this->id;
    }


    /**
     * Является ли бонус депозитным.
     * Используется в SlcBonusItemInfo для определения имени
     * @return bool
     */
    public function isDeposit(): bool
    {
        return $this->meta['is_deposit'] ?? false;
    }

    /**
     * Является ли бонус бездепом.
     * Используется в SlcBonusItemInfo
     * @return bool
     */
    public function isNonDeposit(): bool
    {
        return $this->meta['is_non_deposit'] ?? false;
    }

    /**
     * Является ли бонус фриспиновым
     * @return bool
     */
    public function isFreeSpins(): bool
    {
        return $this->meta['is_free_spins'] ?? false;
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
        return $this->attributes['currency'] ?? 0;
    }

    /**
     * Возвращает ид юзера для которого создан бонус
     * @return int
     */
    public function getUserId(): int
    {
        return $this->attributes['user_id'] ?? 0;
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
     * @param MUser $user
     * @return bool
     */
    public static function userHasCancelableBonuses(MUser $user): bool
    {
        $bonuses = KBonus::getInstance()->getUserBonuses($user->getId(), $user->getCurrency());
        foreach ($bonuses as $bonus) {
            if ($bonus->isActive() || $bonus->isPaused()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Возвращает к-во бонусов доступных пользователю для игры.
     * Включаяя активные и те которые можно активировать.
     * @param int $user_id
     * @return int
     */
    public static function getAvailableBonusesCount(int $user_id): int
    {
        return KBonus::getInstance()->getAvailableBonusesCount($user_id);
    }

    /**
     * Возвращает первый доступный не активированный бонус
     * @param int $user_id
     * @return KUserBonus|null
     * @throws \Exception
     */
    public static function firstUnactivatedBonus(int $user_id): ?KUserBonus
    {
        return KBonus::getInstance()->getFirstNotActiveBonus($user_id);
    }

    /**
     * Создает бонус из json строки с параметрами бонуса
     * @param int $bonus_type тип бонуса
     * @param string $params параметры
     * @param int $user_id ид юзера
     * @param bool $tmp временное создание бонус (не сохраняет после создание, только возвращает бонус)
     * @return KUserBonus
     * @throws \Exception
     */
    public static function fromParams(int $bonus_type, string $params, int $user_id, bool $tmp = true): ?KUserBonus
    {
        return KBonus::getInstance()->createFromParams(
            bonus_type: $bonus_type,
            user_id: $user_id,
            params: $params,
            currency: null,
            tmp: $tmp,
        );
    }


    /**
     * Возвращает первый доступный активный бонус.
     * @param int $userId ид юзера
     * @param null|int $currency ид валюты
     * @return KUserBonus|null
     * @throws \Exception
     */
    public static function findFirstActiveBonus(int $userId, ?int $currency = null): ?KUserBonus
    {
        if (!$userId) return null;
        if ($currency === null) {
            /** @var MUser $user */
            $user = MUser::find($userId);
            $currency = $user->currency;
        }

        return KBonus::getInstance()->getFirstActiveBonus($userId, $currency);
    }

    /**
     * Возвращает активный бонус для указанной игры
     * @param int $user_id
     * @param string $provider_id
     * @param string $game_id
     * @param int|null $currency
     * @return KUserBonus|null
     * @throws \Exception
     */
    public static function getFirstActiveGameBonus(int $user_id, string $provider_id, string $game_id, ?int $currency = null): ?KUserBonus
    {
        if (!$user_id) return null;
        if ($currency === null) {
            /** @var MUser $user */
            $user = MUser::find($user_id);
            $currency = $user->currency;
        }

        $bonus = KUserBonus::findFirstActiveBonus($user_id, $currency);

        if ($bonus !== null && $bonus->game_id === $game_id && $bonus->game_provider === $provider_id) {
            return $bonus;
        }

        return null;
    }


    /**
     * Конвертирует бонус в модель/класс соответствующую типу бонуса
     * @param KUserBonus $bonus
     * @throws \Exception
     */
    public static function convertByType(KUserBonus &$bonus): KUserBonus
    {
        $attr = $bonus->toArray();
        $meta = $bonus->getMeta();
        $bonus->type = intval($bonus->type);
        $bonus = match ($bonus->type) {
            self::TYPE_NON_DEPOSIT => new NonDepositBonus($attr, $meta),
//            self::TYPE_FREE_SPINS => new FreeSpinsBonus($attr, $meta),
//            self::TYPE_PLUS_MINUS => new PlusMinusBonus($attr, $meta),
//            self::TYPE_FREE_SPINS_WAGER => new WagerFreeSpinsBonus($attr, $meta),
//            self::TYPE_SUPER_FREE_SPINS => new SuperFreeSpinsBonus($attr, $meta),
//            self::TYPE_SUPER_FREE_SPINS_PLAYBACK => new SuperFreeSpinsPlayback($attr, $meta),
//            self::TYPE_EVENT_CASH_BACK => new CashbackEventBonus($attr, $meta),
//            self::TYPE_DEPOSIT_WAGER => new DepositWagerBonus($attr, $meta),
//            self::TYPE_SUPER_FS_CASHBACK => new SuperFsCashbackBonus($attr, $meta),
//            self::TYPE_SUPER_FS_CASHBACK_PB => new SuperFsCashbackPbBonus($attr, $meta),
//            self::TYPE_FILTHY_FS => new FilthyFreeSpinsBonus($attr, $meta),
//            self::TYPE_FILTHY_FS_WASH_OFF => new FilthyFsWashOffBonus($attr, $meta),
//            self::TYPE_BEZDEP => new BezdepBonus($attr, $meta),
            self::TYPE_DEPOSIT_PRIZE => new DepositPrizeBonus($attr, $meta),
//            self::TYPE_DEPOSIT_FFS_TNT => new DepositFFrespinsTntBonus($attr, $meta),
//            self::TYPE_NON_DEPOSIT_WAGER => new NonDepWagerBonus($attr, $meta),
            self::TYPE_CHOOSE_WAGER_FS2 => new ChooseWagerFs2($attr, $meta),
//            self::TYPE_SMART_WAGER => new SmartWagerBonus($attr, $meta),
//            self::TYPE_TOURNAMENT_WIN => new TournamentWinBonus($attr, $meta),
//            self::TYPE_PPLAY_FS => new PPlayFreeSpins($attr, $meta),
//            self::TYPE_PPLAY_SMART_FS => new PPlaySmartFs($attr, $meta),
//            self::TYPE_QT_FS_WAGER => new QtFsWager($attr, $meta),
//            self::TYPE_QT_SMART_FS => new QtSmartFs($attr, $meta),
//            self::TYPE_SLEG_FS => new SlegFsWager($attr, $meta),
//            self::TYPE_SLEG_SMART => new SlegSmartFs($attr, $meta),
//            self::TYPE_PP_NONDEP => new PPNonDepFs($attr, $meta),
//            self::TYPE_SLEG_NONDEP => new SlegNonDepFs($attr, $meta),
//            self::TYPE_PP_FS_MENU => new PPlayFSMenu($attr, $meta),
//            self::TYPE_SLEG_FS_MENU => new SlegFsMenu($attr, $meta),
            self::TYPE_CASHBACK_MONEY => new CashbackMoneyBonus($attr, $meta),
//            self::TYPE_SWISS_FS_WAGER => new SwissFsWager($attr, $meta),
//            self::TYPE_PPLAY_SFS_DYNAMIC => new PPlaySfsDyn($attr, $meta),
//            self::TYPE_SLEG_SFS_DYNAMIC => new SlegSfsDyn($attr, $meta),
            self::TYPE_MULTI_DAYS => new MultiDaysBonus($attr, $meta),
//            self::TYPE_PLUS_MINUS_DEP => new PlusMinusDepBonus($attr, $meta),
            self::TYPE_SHARED_MONEY => new SharedMoneyBonus($attr, $meta),
            self::TYPE_SHARED_MONEY_FS => new SharedMoneyFsBonus($attr, $meta),
            self::TYPE_WELCOME_BONUS_PACK => new WelcomeBonusPack($attr, $meta),
            self::TYPE_PS_FS => new PSFreeSpins($attr, $meta),
            self::TYPE_PS_FS_MENU => new PSFSMenu($attr, $meta),
            self::TYPE_PS_SFS_DYNAMIC => new PSSfsDyn($attr, $meta),
            self::TYPE_PS_SMART => new PSSmartFs($attr, $meta),
            self::TYPE_PS_NON_DEP => new PSNonDepFs($attr, $meta),
            self::TYPE_NON_DEP => new NonDepBonus($attr, $meta),
            self::TYPE_STICKY_FS_MENU => new StickyFSMenu($attr, $meta),
            default => new UnsupportedBonus($attr, $meta),
//            default => throw new \Exception('Can`t convert bonus'),
        };
//        $bonus->exists = true;
        $bonus->curr_name = Wallet::currencyName($bonus->currency ?? UserWallet::CURRENCY_RUB);
        return $bonus;
    }

    /**
     * Возвращает бонус на проигрыш
     * @param MUser $user
     * @return KUserBonus|null
     * @throws \Exception
     * @deprecated
     */
    public static function getWaitingLose(MUser $user): ?KUserBonus
    {
//        $bonus = KUserBonus::where(['status' => KUserBonus::STATUS_WAITING_FOR_LOSE])
//            ->where('user_id', $user->id)
//            ->where('currency', $user->currency)
//            ->first();
//        if ($bonus) {
//            return static::convertByType($bonus);
//        }
        return null;
    }

    /**
     * Возвращает бонус, ожидающий оплату (депозит)
     * @param MUser $user
     * @return KUserBonus|null
     * @throws \Exception
     */
    public static function getWaitingDeposit(MUser $user): ?KUserBonus
    {
        return KBonus::getInstance()->getFirstWaitingDepositBonus($user->id, $user->currency);
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
     *
     * @return string
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
    public function createFromParams(string $params, int $user_id, ?int $currency = null, ?bool $keep_params = false, ?bool $tmp = false): ?KUserBonus
    {
        if ($currency === null) $currency = $this->currency;
        $bonus = KBonus::getInstance()->createFromParams(
            bonus_type: $this->type,
            user_id: $user_id,
            params: $params,
            currency: $currency,
            keep_params: $keep_params,
            tmp: $tmp,
        );
        return $bonus;
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
        return $this->meta['is_locked'] ?? false;
    }

    /**
     * Проверяет не истек ли срок жизни бонуса.
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->meta['is_expired'] ?? false;
    }

    public function isPaused(): bool
    {
        return $this->meta['is_paused'] ?? false;
    }

    public function canPause(): bool
    {
        return $this->meta['can_pause'] ?? false;
    }

    public function pause(): bool
    {
        $bonus = KBonus::getInstance()->pauseOrResumeBonus($this->getId(), true);
        if ($bonus === null) return false;
        $this->attributes = $bonus->toArray();
        $this->meta = $bonus->getMeta();
        return true;
    }

    public function resume(): bool
    {
        $bonus = KBonus::getInstance()->pauseOrResumeBonus($this->getId(), false);
        if ($bonus === null) return false;
        $this->attributes = $bonus->toArray();
        $this->meta = $bonus->getMeta();
        return true;
    }

    /**
     * Активирует бонус и возвращает была ли активация успешной.
     * @return bool
     */
    public function activate(): bool
    {
        $bonus = KBonus::getInstance()->activateBonus($this->getId());
        if ($bonus === null) return false;
        $this->attributes = $bonus->toArray();
        $this->meta = $bonus->getMeta();
        return true;
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
     * @deprecated
     */
    public function getDescription(): string
    {
        return "Бонус #$this->id";
    }

    /**
     * Возвращает описания бонуса.
     * По умолчанию рендер view файла,
     * находящегося в /common/bonus-descriptions.t<ид типа бонуса>
     * @return string
     */
    public function getShortDescription(): string
    {
        if (view()->exists('common.bonus-descriptions.t' . $this->type)) return view('common.bonus-descriptions.t' . $this->type, ['bonus' => $this])->render();
        return 'NO DESCRIPTION ' . $this->type;
    }

    /**
     * Возвращает название файла описания бонуса.
     * Используется для определения view файла с описанием
     * @return string|null
     */
    public function getDescriptionName(): ?string
    {
        return null;
    }

    /**
     * Требует ли бонус оплаты
     * @return bool
     */
    public function isPaymentRequired(): bool
    {
        return $this->meta['is_payment_required'] ?? false;
    }

    /**
     * Требует ли бонус активации
     * @return bool
     */
    public function isAgreementRequired(): bool
    {
        return $this->meta['is_agreement_required'] ?? false;
    }

    /**
     * Доступен ли прогресс бонуса
     * @return bool
     */
    public function isProgressAvailable(): bool
    {
        return $this->status === self::STATUS_ACTIVE || $this->status === self::STATUS_PAUSE;
    }

    /**
     * Возвращает строку прогресса бонуса. (Сколько сделано спинов, отыграно вейджера ...)
     * @return string
     */
    public function getProgressHint(): string
    {
        if ($this->spins_available > 0) {
            return __('bonus.spins_made') . ($this->spins_total - $this->spins_available) . '/' . $this->spins_total;
        }
        if ($this->isProgressAvailable()) {
            return __('bonus.played') . number_format($this->total_bet, 2, '.', '') . '/' . number_format($this->wager_to_complete, 2, '.', '');
        }
        return '';
    }

    public function getTargetHint(): string
    {
        if ($this->spins_available > 0) {
            return __('bonus.spins_made') . $this->spins_total;
        }
        if ($this->isProgressAvailable()) {
            return __('bonus.played') . number_format($this->wager_to_complete, 2, '.', ' ');
        }
        return '';
    }

    /**
     * Возвращает процент отыгрыша бонуса
     * @return int
     */
    public function getProgressPercents(): int
    {
        if ($this->spins_available > 0) {
            return $this->spins_available <= 0 ? 100 : (100 - round($this->spins_available / $this->spins_total * 100));
        }
        if ($this->wager_to_complete == 0) return 100;
        $p = floor($this->total_bet / $this->wager_to_complete * 100);
        if ($p > 100) $p = 100;
        return $p;
    }


    /**
     * Возвращает сумму депозита, требуемую для активации бонуса.
     * @return float
     */
    public function getRequiredSum(): float
    {
        return $this->money_to_activate;
    }

    /**
     * Можно ли отменить бонус.
     * @return bool
     */
    public function isCancelable(): bool
    {
        return $this->meta['is_cancelable'] ?? false;
    }

    /**
     * Активен ли бонус
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->meta['is_active'] ?? false;
    }

    /**
     * Завершен ли бонус
     * @return bool
     */
    public function isDone(): bool
    {
        return $this->meta['is_done'] ?? false;
    }

    /**
     * Отменен ли бонус.
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->meta['is_canceled'] ?? false;
    }

    /**
     * Находится ли бонус в состоянии ожидания.
     * Бонусы, на которые нельзя играть напрямую, не должны быть в активном статусе.
     * Например, кешбэк бонусы находятся в состоянии ожидания а не активности.
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->meta['is_pending'] ?? false;
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
        return $this->meta['is_listening_deposit'] ?? false;
    }

    /**
     * Вызывается при совершении депозита для бонусов в состоянии STATUS_DEPOSIT_LISTEN (isListeningDeposit)
     * @param float $amount
     * @param int $currency
     * @return bool
     */
    public function onDeposit(float $amount, int $currency): bool
    {
        $bonus = KBonus::getInstance()->onDeposit($this->getId(), $amount, $currency);
        if ($bonus !== null) {
            $this->attributes = $bonus->toArray();
            $this->meta = $bonus->getMeta();
        }
        return true;
    }

    /**
     * Завершен ли бонус
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->meta['is_completed'] ?? false;
    }

    /**
     * Возвращает дату истечения бонуса
     * @return Carbon
     */
    public function getExpirationDate(): Carbon
    {
        if ($this->_expirationDate === null) {
            if ($this->active_to === null) return now()->addHours(12)->locale('ru');
            $this->_expirationDate = Carbon::parse($this->active_to)->locale('ru');
        }
        return $this->_expirationDate;
    }


    /**
     * Возвращает общая сумма выигранных денег
     * @return float
     */
    public function getTotalMoneyWon(): float
    {
        if (!$this->isDone()) return 0;
        return $this->meta['total_money_won'] ?? 0;
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
     * Возвращает сумму, оставшуюся для "отмывания" (отыгрывания на реальные средства)
     * @return float
     */
    public function getMoneyAvailableForPlay(): float
    {
        return 0;
    }

    /**
     * Бонус активируется с помощью меню
     * @return bool
     */
    public function isActivationMenu(): bool
    {
        return $this->meta['is_activated_by_menu'] ?? false;
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
        $bonus = KBonus::getInstance()->activateBonus($this->getId(), $option_value);
        if ($bonus === null) return false;
        $this->attributes = $bonus->toArray();
        $this->meta = $bonus->getMeta();
        return true;
    }

    /**
     * Отменяет бонус
     * @return void
     */
    public function cancelBonus(): bool
    {
        $bonus = KBonus::getInstance()->cancelBonus($this->getId());
        if ($bonus === null) return false;
        $this->attributes = $bonus->toArray();
        $this->meta = $bonus->getMeta();
        return true;
    }

    /**
     * Переводит доступные отыгранные средства на реальный баланс
     * Для "отмываемых" бонусов (не используются)
     * @return float
     */
    public function transferBonusMoney(): float
    {
        return 0;
    }

    /**
     * Активация бонусов, ожидающих оплаты.
     * Вызывается при совершении оплаты для бонусов в статусе STATUS_WAITING_FOR_PAYMENT
     * Бонусы могут изымать часть денег, если требуют реальные деньги для активации (нигде не используется).
     * @param MUser $user пользователь, совершающий депозит
     * @param float $amount сумма депозита
     * @param int $currency ид валюты депозита
     * @return float остаток средств к зачислению на счет
     * @see Payin::completeTransaction
     */
    public function confirmPayment(MUser $user, float $amount, int $currency): float
    {
        $bonus = KBonus::getInstance()->confirmPayment($this->getId(), $amount, $currency);
        if ($bonus !== null) {
            $this->attributes = $bonus->toArray();
            $this->meta = $bonus->getMeta();
        }
        // на данный момент ниодин бонус не должен требовать реальных денег для активации
        return $amount;
    }

    /**
     * Указывает должен ли активный бонус обрабатывает депозит
     * @return bool
     * @see Payin::completeTransaction
     */
    public function shouldProcessPayment(): bool
    {
        return $this->meta['is_process_payment'] ?? false;
    }

    /**
     * Разрешена ли игра провайдера для данного бонуса
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
        if ($this->getId() > 0) return KBonus::getInstance()->getBonusBalanceInCents($this->getId());
        return $this->money_available * 100;
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
    public static function cancelPayoutBonuses(MUser $user): void
    {
        $bonuses = KBonus::getInstance()->getUserBonuses($user->getId(), $user->getCurrency());
        foreach ($bonuses as $bonus) {
            if ($bonus->isActive() || $bonus->isPaused()) {
                try {
                    $bonus->cancelBonus();
                } catch (\Throwable $e) {
                    Hlog::logException($e);
                }
            }
        }
//        KBonus::getInstance()->cancelBonusesOnPayout($user->getId());
    }

    /**
     * Отменяет кешбэки при выплате
     * @param MUser $user
     * @return void
     * @deprecated
     */
    public static function cancelCashBackBonuses(MUser $user)
    {
//        CashbackEventBonus::cancelForUser($user);
    }

    /**
     * Возвращает строку с балансом бонуса.
     * Используется в строке бонусного состояния меню пользователя.
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
     * Используется в строке бонусного состояния меню пользователя.
     * @return string
     */
    public function getBalanceName(): string
    {
        if ($this->isDone()) {
            return __('bonus.win');
        }

        if ($this->getSpinsAvailable() > 0) {
            return "spins";
        } else {
            return __('bonus.bonus_balance');
        }
    }


    /**
     * Возвращает коллекцию доступных для бонуса игр.
     * Использует сервис GamesManager.
     * @return GamesCollection
     * @see GamesManager
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

            // if wager games are set
            if (!empty($this->wager_games_segment_id)) {
                $games = GameManagerApi::getInstance()->getGameSegmentsGames(
                    segment_id: $this->wager_games_segment_id,
                    per_page: 0,
                    page: 0,
                );
            } else {
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
            }

            $this->_availableGames = new GamesCollection();
            foreach ($games->getGames() as $game) $this->_availableGames->addGame($game);
        }
        return $this->_availableGames;
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
                'provider' => $game->getGroupName(),
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
     * Указывает что бонус должен принудительно запускать доступные для бонуса игры в бонусном режиме.
     * @return bool
     */
    public function forceToPlay(): bool
    {
        return $this->meta['is_force_to_play'] ?? false;
    }

    /**
     * Указывает что для бонуса не должна отображаться кнопка "играть"
     * @return bool
     */
    public function noPlayButton(): bool
    {
        return $this->meta['is_not_playable'] ?? false;
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
        return empty($this->allowed_games) && empty($this->game_id) && empty($this->wager_games_segment_id);
    }

    /**
     * Возвращает маркер бонуса
     * @return int
     * @see BonusMarker
     */
    public function getMarker(): int
    {
        return $this->marker;
    }

    /** Устанавливает маркер боунса
     * @param int $marker
     * @return void
     * @see BonusMarker
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
            $game = GamesManager::getProvider($this->game_provider)?->getGame('', $this->game_id, true);
            if ($game) {
                KBonus::getInstance()->addBonusToSegmentByName($this->getId(), $game->getName());
            }
        }
        $user = MUser::find($this->getUserId());
        $user?->setRedisValue('has_new_bonus', true);

    }

    public function save(): bool
    {
        $bonus = KBonus::getInstance()->saveBonus($this->attributes);
        if ($bonus === null) return false;
        $this->attributes = $bonus->toArray();
        $this->meta = $bonus->getMeta();
        return true;
    }

}
