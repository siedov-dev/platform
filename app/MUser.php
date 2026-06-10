<?php

namespace App;

use App\Events\UserFirstDayActivityEvent;
use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Currency;
use App\Helpers\GoldPartnersSdk;
use App\Helpers\Hlog;
use App\Models\Bonuses\KUserBonus;
use App\Services\KBonus\KBonus;
use App\Services\Users\UserManager;
use App\Services\IpInfo\IpInfo;
use App\Models\BonusBlackList;
use App\Models\Bonuses\DepositBonusInterface;
use App\Models\Bonuses\UserBonus;
use App\Models\DisabledAutopayUser;
use App\Models\MailSubscribe;
use App\Models\Partner;
use App\Models\PersonalData;
use App\Models\Slc\SlcUserInventory;
use App\Models\Slc\SlcWallet;
use App\Models\TestUser;
use App\Models\UserCryptoWallet;
use App\Models\UserData;
use App\Models\UserNotifications;
use App\Models\Rank\UserRank;
use App\Models\UserValue;
use App\Models\UserVipStatus;
use App\SMS\SmsInfo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Redis;

/**
 * Class MUser
 * @property int $id
 * @property int $hall_id
 * @property int $money
 * @property int $b_money_s
 * @property int $rank
 * @property string $email
 * @property string $telegramm
 * @property string $phone
 * @property string $once_pass
 * @property int $playing_on
 * @property float $money_sport
 * @property int $is_sport_registered
 * @property int $sport_access_key
 * @property int $sport_id
 * @property string $sport_token
 * @property string $login
 * @property string $social_login
 * @property string $last_ip
 * @property float $TotalBet
 * @property float $TotalWin
 * @property int $personal_data_id
 * @property string $verification_msg
 * @property int currency
 * @property int $dm_currency
 * @property int $sport_currency
 * @property int $click_id
 * @property string $last_dm_game
 * @property int $qt_lock
 * @property int ref_id
 * @property int balance_mode
 * @property string email_verification_token
 * @property Carbon created_at
 * @property string password
 * @property string view_password
 * @property string $favourite_games
 * @property float $current_money_paid_in
 * @property float $current_money_won
 * @property float $total_win
 * @property string $locale
 * @property string $accept_language
 * @property int $admin
 * @property string $addres //use for state AuthController@register
 * @property string $address
 * @property string $post_index
 * @property string $name
 * @property string $surname
 * @property string $city
 * @property string $origin_ref //where user came from
 * @property bool $blocked
 * @property string $block_reason
 * @property Carbon $last_active_user
 * @property Carbon $last_active
 * @property ?string $token
 * @property ?string $ntoken // used for fingerprint
 * @property ?string $last_tz //timezone
 */
class MUser extends Authenticatable
{
    const NOT_PLAYING = 0;
    const GAMES_DRUMMATIC = 2;
    const GAMES_SPORT = 3;
    const GAMES_QT = 4;
    const GAMES_TVBET = 5;

    const BALANCE_MODE_MAIN = 0;
    const BALANCE_MODE_BONUS = 1;
    const BALANCE_MODE_TOURNAMENT = 2;

    const RANK_CABIN_BOY = 1;
    const RANK_MIDSHIPMAN = 2;

    const DEMO_USER = 2;

    public $table = 'm_users';
    public $timestamps = false;

    private GamesCollection $_favGames;
    private PersonalData $_personalData;

    private string $_locale;
    private ?string $_country = null;

    private array $_currencies = [];

    protected $dates = ['created_at', 'last_active'];

    private ?bool $_isTester = null;

    private $_wallet = [];

    protected $guarded = [
        'id', 'login', 'password', 'email',
    ];

    protected $hidden = [
        'password',
        'view_password',
        'token',
        'ntoken',
        'remember_token',
        'email_verification_token',
        'once_pass',
        'sport_token',
        'sport_access_key',
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getBalanceMode(): int
    {
        return $this->balance_mode;
    }

    public function getLastIp(): string
    {
        return $this->last_ip;
    }

    public function setLastIp(string $last_ip): void
    {
        $this->last_ip = $last_ip;
    }

    public function isEmailVerified(): bool
    {
        return !empty($this->email) && empty($this->email_verification_token);
    }


    public function isPhoneVerified(bool $phone_is_set = false): bool
    {
        if (!$phone_is_set && empty($this->phone)) return true;
        return empty($this->once_pass) & !empty($this->phone);
    }


    public function hasAnyVerifiedContact(): bool
    {
        return $this->isEmailVerified() || $this->isPhoneVerified(true);
    }


    public function sendSMS($msg): SmsInfo
    {
        $info = SMSManager::sendSMS($this->phone, $msg, currency: Wallet::currencyName($this->currency));
        if (!$info->success) return $info;
        $partner = Partner::where('id', $this->pid)->first();
        if ($partner !== null) {
            $partner->sms_credits += $info->cost;
            $partner->save();
        }
        return $info;
    }


    /**
     * Возвращает текущий уровень ранга
     * @param int|null $currency
     * @return int
     */
    public function getLevel(?int $currency = null): int
    {
        return $this->getRank($currency)->getLevel();
    }

    /**
     * Возвращает информацию о ранге
     * @param int|null $currency
     * @return UserRank
     */
    public function getRank(?int $currency = null): UserRank
    {
        if (!$currency) $currency = $this->currency;
        $rank = UserRank::getForLvl(UserRank::getAmountLevel($this->getRankValue($currency), $currency), $currency);
        return $rank ?? new UserRank();
    }

    /**
     * Возвращает текущее значение ранга (сумму ставок)
     * @param int|null $currency
     * @return float
     */
    public function getRankValue(?int $currency = null): float
    {
        return $this->getWallet($currency)->getTotalBet();
    }

    /**
     * Возвращает процент прогресса текущего ранга
     * @param int|null $currency
     * @return int
     */
    public function getNextRankProgress(?int $currency = null): int
    {
        if (!$currency) $currency = $this->currency;
        return $this->getRank($currency)->getNextRankProgress($this->getRankValue($currency), $currency);
    }

    /**
     * Возвращает название ранга
     * @return string
     */
    public function getRankName(): string
    {
        return $this->getRank()->getRankName();
    }

    public function getTotalBet(?int $currency = null): float
    {
        if (!$currency) $currency = $this->currency;
        return $this->currency == $currency ? ($this->TotalBet ?? 0) : $this->getWallet($currency)->TotalBet;
    }

    public function personalData(): PersonalData
    {
        if (!isset($this->_personalData)) {
            $data = PersonalData::where('user_id', $this->id)->first();
            if ($data === null) {
                $data = new PersonalData();
                $data->user_id = $this->id;
                $data->status = PersonalData::STATUS_NOT_CONFIRMED;
            }
            $this->_personalData = $data;
        }
        return $this->_personalData;
    }

    public function requiresVerification(): bool
    {
        return $this->personal_data_id == PersonalData::STATUS_REQUIRED;
    }

    /**
     * checks if user has an active bonus or a tournament game
     * for the specified vegas game
     * that is enabled for vegas provider
     * @param string $gameId vegas game name
     * @return bool
     */
    public function hasActiveVegasBonusGame(string $gameId): bool
    {
        /** @var UserBonus $d_bonus */
        $d_bonus = KUserBonus::findFirstActiveBonus($this->id);
        return $d_bonus != null;
    }


    /**
     * Returns money that was paid out since given date.
     * @param string|null $from Date in format (Y-m-d).
     * @param string|null $to Date in format (Y-m-d).
     * @param int|null $currency_id
     * @return float
     */
    public function getMoneyOutSince(string $from = null, string $to = null, int $currency_id = null): float
    {
        if ($currency_id == null) $currency_id = $this->currency;
        $query = PayoutTransaction::where('user_id', $this->id)
            ->whereIn('status_id', PayoutTransaction::getOutgoingStatuses())
            ->where('currency', UserWallet::currencyName($currency_id));

        if (!empty($from)) {
            $query->where('date', '>=', $from);
        }

        if (!empty($to)) {
            $query->where('date', '<=', $to);
        }

        return $query->sum('sum');
    }

    /**
     * Returns money that was paid in since given date.
     * @param string|null $from Date in format (Y-m-d).
     * @param string|null $to Date in format (Y-m-d).
     * @return float
     */
    public function getMoneyInSince(string $from = null, string $to = null, int $currency_id = null): float
    {
        if ($currency_id == null) $currency_id = $this->currency;
        $query = PayoutTransaction::where('user_id', $this->id)
            ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
            ->where('currency', UserWallet::currencyName($currency_id));

        if (!empty($from)) {
            $query->where('date', '>=', $from);
        }

        if (!empty($to)) {
            $query->where('date', '<=', $to);
        }

        return $query->sum('sum');
    }

    /**
     * Returns deposits count was paid in since given date.
     * @param string|null $from Date in format (Y-m-d).
     * @param string|null $to Date in format (Y-m-d).
     * @return int
     */
    public function getDepositsCount(string $from = null, string $to = null, float $min_sum = 0, int $currency_id = 0): int
    {
        $query = PayoutTransaction::where('user_id', $this->id)
            ->whereIn('status_id', PayoutTransaction::getIncomingStatuses());

        if (!empty($from)) {
            $query->where('date', '>=', $from);
        }

        if (!empty($to)) {
            $query->where('date', '<=', $to);
        }

        if ($currency_id > 0) {
            $query->where('currency', UserWallet::currencyName($currency_id));
        }

        $query->where('sum', '>=', $min_sum);

        return $query->count('id');
    }

    /**
     * Returns payout count was paid in since given date.
     * @param string|null $from Date in format (Y-m-d).
     * @param string|null $to Date in format (Y-m-d).
     * @return int
     */
    public function getPayoutsCount(string $from = null, string $to = null): int
    {
        $query = PayoutTransaction::where('user_id', $this->id)
            ->whereIn('status_id', PayoutTransaction::getOutgoingStatuses());

        if (!empty($from)) {
            $query->where('date', '>=', $from);
        }

        if (!empty($to)) {
            $query->where('date', '<=', $to);
        }

        return $query->count('id');
    }

    public function getMoneyPreparedForPayout(string $from = null, string $to = null)
    {
        $query = ApplicationForPayment::where('user_id', $this->id)
            ->where('confirmed', 0);

        if (!empty($from)) {
            $query->where('date', '>=', $from);
        }

        if (!empty($to)) {
            $query->where('date', '<=', $to);
        }

        return $query->sum('suma');
    }


    /**
     * @return string|null
     * @deprecated
     */
    public function getLastBalanceSentAt(): ?string
    {
        return empty($this->last_balance_sent_at) ? null : $this->last_balance_sent_at;
    }

    /**
     * @return int
     * @deprecated
     */
    public function getTotalBalanceSentAmount(): int
    {
        return $this->total_balance_sent_amount === null ? 0 : intval($this->total_balance_sent_amount);
    }
    /**
     * @param int|null $currency
     * @return Wallet
     */
    public function getWallet(?int $currency = null)
    {
        if (!$currency) $currency = $this->currency;

        if (empty($this->_wallet[$currency])) {
            $this->_wallet[$currency] = new Wallet($this->getId(), $currency);
        }

        return $this->_wallet[$currency];

//        if (!UserWallet::where('user_id', $this->id)->where('currency', $currency)->first()) {
//            $wallet = new UserWallet();
//            $wallet->user_id = $this->id;
//            $wallet->currency = $currency;
//            $wallet->TotalBet = 0;
//            $wallet->TotalOut = 0;
//            $wallet->TotalWin = 0;
//            $wallet->TotalIn = 0;
//            $wallet->save();
//        }
//        $this->_wallet[$currency] = UserWallet::where('user_id', $this->id)->where('currency', $currency)->lockForUpdate()->first();
//        return $this->_wallet[$currency];
    }

    public function getSlcWallet(): SlcWallet
    {
        return SlcWallet::getForUser($this->getId(), $this->getCurrency());
    }

    /**
     * get user's slc items inventory
     * @return SlcUserInventory
     */
    public function getSlcInventory(): SlcUserInventory
    {
        return SlcUserInventory::forUser($this);
    }

    public function addMoney(float $amount, ?int $currency = null)
    {
        $this->getWallet($currency)->addMoney($amount)->save();
        return $this;
    }

    public function remMoney(float $amount, ?int $currency = null)
    {
        $this->getWallet($currency)->remMoney($amount)->save();
        return $this;
    }

    public function getBalance(?int $currency = null)
    {
        return $this->getWallet($currency)->getBalance();
    }

    public function getBalanceStr(?int $currency = null)
    {
        return $this->getWallet($currency)->getBalanceStr();
    }

    /**
     * get current currency id
     * @return int
     */
    public function getCurrency(): int
    {
        return $this->currency;
    }

    /**
     * get current currency name
     * @param int|null $currency
     * @return string
     */
    public function getCurrencyName(?int $currency = null): string
    {
        if (!$currency) $currency = $this->currency;
        return $this->getWallet($currency)->getCurrencyName();
    }

    public static function hideLogin(string $login): string
    {
        return mb_substr(str_pad(trim($login), 5, '*', STR_PAD_RIGHT), 0,
                mb_strlen(str_pad(trim($login), 5, '*', STR_PAD_RIGHT)) - 3) . '***';
    }

    public function getActiveBonus(): ?KUserBonus
    {
        return KUserBonus::findFirstActiveBonus($this->id, $this->currency);
    }

    /**
     * get first working or unactivated bonus
     * @return UserBonus|null
     * @throws \Exception
     */
    public function getFirstAvailableBonus(): ?KUserBonus
    {
        $bonus = KBonus::getInstance()->getFirstActiveBonus($this->getId(), $this->getCurrency());
        if ($bonus !== null) return $bonus;
        $bonus = KBonus::getInstance()->getFirstWaitingDepositBonus($this->getId(), $this->getCurrency());
        if ($bonus !== null) return $bonus;

        return $this->getFirstNonActivatedBonus();
    }

    /**
     * get first not unactivated bonus
     * @return UserBonus|null
     * @throws \Exception
     */
    public function getFirstNonActivatedBonus(): ?KUserBonus
    {
        return KBonus::getInstance()->getFirstNotActiveBonus($this->getId(), $this->getCurrency());
    }

    /**
     * get first waiting for payment bonus
     * @return DepositBonusInterface|null
     * @throws \Exception
     */
    public function getFirstDepositBonus(): ?DepositBonusInterface
    {
        return KUserBonus::getWaitingDeposit($this);
    }

    /**
     * @param string $title
     * @param string $message
     * @param string $link_title
     * @param string $link_url
     * @return void
     * @deprecated
     */
    public function notify(string $title, string $message, string $link_title = '', string $link_url = '')
    {
        UserNotifications::notify($this->id, $title, $message, $link_title, $link_url);
    }

    public function isBlocked(): bool
    {
        return $this->blocked != 0;
    }

    public function getBlockReason(): ?string
    {
        return $this->block_reason;
    }

    public function isVerified(): bool
    {
        $data = $this->personalData();
        if (!$data) return false;

        return $data->isConfirmed();
    }

    /**
     * @return GamesCollection
     * @deprecated
     */
    public function getFavouriteGames(): GamesCollection
    {
        if (!isset($this->_favGames)) {
            $this->_favGames = new GamesCollection();
            $ids = explode(',', $this->favourite_games);
            foreach ($ids as $id) {
                $game = GamesManager::getGameByCompoundId($id);
                if ($game !== null) {
                    $this->_favGames->addGame($game);
                }
            }
        }
        return $this->_favGames;
    }

    public function getLocale(): string
    {
        if (empty($this->_locale)) {
            if (!empty($this->locale)) {
                $this->_locale = $this->locale;
            } else {
                if ($this->currency == UserWallet::CURRENCY_RUB) return 'ru';
                if ($this->currency == UserWallet::CURRENCY_UAH) return 'ua';
                if ($this->currency == UserWallet::CURRENCY_INR) return 'hi';
                if ($this->currency == UserWallet::CURRENCY_KZT) return 'kz';
                return 'en';
            }
        }
        return $this->_locale;
    }

    public function setLocale(string $locale): void
    {
        $this->_locale = $locale;
        $this->locale = $locale;
        $this->save();
    }

    /**
     * get active wallets
     * @return mixed
     */
    public function getActiveWallets(bool $onlyCrypto = false, bool $onlyFiat = false)
    {
        $wallets = [];
        try {
            $balances = UserManager::getBalances($this->getId());
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
        if ($balances['cd'] !== 200) {
            Log::error("balances error ".($balances['err']??""));
        }

        foreach ($balances['Values'] as $balance) {
            if (!$balance['a']) continue;
            if ($onlyCrypto && in_array($balance['ci'], Currency::$crypto_id) ) continue;
            if ($onlyFiat && !in_array($balance['ci'], Currency::$crypto_id) ) continue;
            $wallets[] = $this->getWallet($balance['ci']);
        }
        return collect($wallets);

//        $q = UserWallet::where('user_id', $this->id)->where('active', true);
//        if ($onlyCrypto) $q->whereIn('currency', Currency::$crypto_id);
//        if ($onlyFiat) $q->whereNotIn('currency', Currency::$crypto_id);
//        return $q->get();
    }

    /**
     * user for streaming
     * @return bool
     */
    public function isDemoUser(): bool
    {
        return $this->admin == self::DEMO_USER;
    }

    /**
     * return array with users active currency codes
     * @return array
     */
    public function getActiveCurrencies(): array
    {
        if (empty($this->_currencies)) {
            try {
                $balances = UserManager::getBalances($this->getId());
            } catch (\Throwable $e) {
                Hlog::logException($e);
            }
            if ($balances['cd'] !== 200) {
                Log::error("balances error ".($balances['err']??""));
            }

            foreach ($balances['Values'] as $balance) {
                if (!$balance['a']) continue;
                $this->_currencies[] = $balance['ci'];
            }

            //if no active wallets
            if (empty($this->_currencies)) {
                //get or create with current currency and activate
                $this->getWallet()->setActive();
            }
        }
        return $this->_currencies;
    }

    public function hasActiveCurrency(int $currency_id): bool
    {
        return in_array($currency_id, $this->getActiveCurrencies());
    }

    public function getUserData(): UserData
    {
        return UserData::getForUser($this->id);
    }

    public static function getAuthOrFail(): MUser
    {
        return MUser::findOrFail(Auth::id());
    }

    public static function getAuth(): ?MUser
    {
        return MUser::find(Auth::id());
    }

    public function getCountry()
    {
        if ($this->_country === null) {
            $info = IpInfo::getInfo($this->last_ip);
            if ($info !== null) {
                $this->_country = $info->country_code;
            }
        }
        return $this->_country;
    }

    public function isInBonusBlackList(): bool
    {
        return \App\Models\BonusBlackList::where('user_id', $this->id)->first() !== null;
    }

    public function addToBonusBlackList(): void
    {
        if (!$this->isInBonusBlackList()) {
            $bl = new BonusBlackList(['user_id' => $this->id]);
            $bl->save();
        }
    }

    public function remFromBonusBlackList(): void
    {
        BonusBlackList::where('user_id', $this->id)->delete();
    }

    public function isAutoPayDisabled(): bool
    {
        return DisabledAutopayUser::where('user_id', $this->id)->first() !== null;
    }

    public function disableAutoPay(): void
    {
        if (!$this->isAutoPayDisabled()) {
            $bl = new DisabledAutopayUser(['user_id' => $this->id]);
            $bl->save();
        }
    }

    public function enableAutoPay(): void
    {
        DisabledAutopayUser::where('user_id', $this->id)->delete();
    }

    public function isTestUser(): bool
    {
        if ($this->_isTester === null) $this->_isTester = TestUser::where('user_id', $this->id)->first() !== null;
        return $this->_isTester;
    }

    public function getVipStatus(): ?UserVipStatus
    {
        return UserVipStatus::where('user_id', $this->getId())
            ->where('active_to', '>', now()->format('Y-m-d H;i:s'))
            ->first();
    }

    /**
     * set click id to user
     * @param int $click_id
     * @param bool $force overwrite old click_id
     * @return int|null click id set
     */
    public function setClickId(int $click_id, bool $force = false): ?int
    {
        if (!empty($this->ref_id)) {
            Log::warning('ref_id is set, skipping click_id setting');
            return null;
        }

        if ($this->click_id !== null) {
            if (!$force) {
                Log::error('click_id already set #' . $this->click_id . "<<" . $click_id);
                return $click_id;
            } else {
                Log::warning('click_id already set & force flag is set: overriding ' . $this->click_id . "<<" . $click_id);
            }
        }
        //check if click id is already in use
        /** @var MUser $used */
        $used = MUser::where('click_id', $click_id)->first();
        if ($used !== null) {
            Log::info('click id ' . $click_id . ' is already in use by user #' . $used->getId());
            // get partners link of click id
            $link_id = GoldPartnersSdk::getClicksLink($click_id);
            if ($link_id === null) {
                Log::error('cant find link id for click #' . $click_id);
                return null;
            }

            $click_id = GoldPartnersSdk::getClickId($link_id);
            if ($click_id === 0) {
                Log::error('cant get click id for link #' . $link_id);
                return null;
            }
            Log::info('new click_id ' . $click_id);
        }

        $this->click_id = $click_id;
        $this->save();
        return $this->click_id;
    }

    public function isSubscribed(): bool
    {
        return MailSubscribe::isSubscribed($this->id);
    }

    public function getSubscribeToken(): string
    {
        return MailSubscribe::getSubscribeToken($this);
    }

    public function setValue(string $key, mixed $value): bool
    {
        return UserValue::setValue($this->getId(), $key, $value);
    }

    public function getValue(string $key, mixed $default = null): mixed
    {
        return UserValue::getValue($this->getId(), $key, $default);
    }

    public function getRawValue(string $key, mixed $default = null): mixed
    {
        return UserValue::getRawValue($this->getId(), $key, $default);
    }

    public function setRedisValue(string $key, mixed $value): bool
    {
        return Redis::set($this->getId() . ":value:" . $key, json_encode($value));
    }

    public function getRedisValue(string $key, mixed $default = null): mixed
    {
        return json_decode(Redis::get($this->getId() . ":value:" . $key) ?? '', true) ?? $default;
    }

    public function getRedisRawValue(string $key, mixed $default = null): mixed
    {
        return Redis::get($this->getId() . ":value:" . $key);
    }

    public function clearRedisValue(string $key, mixed $default = null): mixed
    {
        return Redis::del($this->getId() . ":value:" . $key);
    }


    public function updateLastActive(): void
    {
        if (empty($this->last_active) || \Carbon\Carbon::parse($this->last_active)->format('Y-m-d') !== now()->format('Y-m-d')) {
            event(new UserFirstDayActivityEvent($this, $this->last_active));
        }
        $this->last_active = date('Y-m-d H:i:s');
    }

    public function getSlcBalance(): int
    {
        return $this->getSlcWallet()->getBalance();
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getFingerprint(): ?string
    {
        return $this->ntoken;
    }

    public function setFingerprint(?string $ntoken): void
    {
        $this->ntoken = $ntoken;
    }

    public function getLastTz(): ?string
    {
        return $this->last_tz;
    }

    public function setLastTz(?string $last_tz): void
    {
        $this->last_tz = $last_tz;
    }



    //favorite disabled
//    protected static function boot()
//    {
//        parent::boot();
//        self::saving(function ($model) {
//            $model->favourite_games = $model->getFavouriteGames()->__toString();
//        });
//    }
}
