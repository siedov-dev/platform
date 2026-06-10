<?php

namespace App;

use App\Helpers\Hlog;
use App\Models\Bonuses\UserBonus;
use App\Models\UserCurrency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/**
 * Class UserWallet
 * @package App
 * @property int $id
 * @property int $user_id
 * @property int $currency
 * @property bool $active
 * @property float $amount
 * @property float $TotalBet
 * @property float $TotalWin
 * @property float $TotalIn
 * @property float $TotalOut
 * @property string $sport_id
 * @property string $sport_token
 * @property string $sport_access_key
 * @property float $bonus_money
 * @property float $referral_money
 * @property float $locked_money
 * @property ?int $bonus_id
 * @property ?UserBonus $userBonus
 */
class UserWallet extends Model
{
    const CURRENCY_RUB = 1;
    const CURRENCY_UAH = 2;
    const CURRENCY_USD = 3;
    const CURRENCY_EUR = 4;
    const CURRENCY_INR = 5;
    const CURRENCY_mBTC = 6;
    const CURRENCY_USDT = 7;
    const CURRENCY_KZT = 8;
    const CURRENCY_UZS = 9;
    //
    protected $guarded = [];

    private $_config;
    private static $_curencies;


    private array $digits = [
        self::CURRENCY_mBTC => 4,
    ];

    public function getUser(): MUser
    {
        return MUser::find($this->user_id);
    }

    /**
     * get currency name (RUB, UAH...)
     * @param int|null $currency
     * @return string
     */
    public function getCurrencyName(?int $currency = null): string
    {
        return $this->getCurrency()['name'];
    }

    public function getGameCurrencyName(?int $currency = null): string
    {
        return $this->getCurrency()['game_currency'] ?? $this->getCurrencyName($currency);
    }

    /**
     * get currency name (RUB, UAH...)
     * @param int $currency
     * @return string
     */
    public static function getCurrencyNameById(int $currency): string
    {
        self::getCurrencies();
        foreach (self::$_curencies as $c) {
            if ($c->id == $currency) return $c->name;
        }
        return 'RUB';
    }

    /**
     * @return UserCurrency
     */
    protected function getCurrency(): UserCurrency
    {
        if ($this->_config === null) {
            $this->_config = UserCurrency::findOrFail($this->currency);
        }
        return $this->_config;
    }

    /**
     * return currency id (1,2,3...)
     * @return int
     */
    public function getCurrencyId(): int
    {
        return $this->currency;
    }

    /**
     * get currency internation code (980, 643...)
     * @return mixed
     */
    public function getCurrencyIntCode()
    {
        return $this->getCurrency()['code'];
    }

    /**
     * @deprecated
     * @return string
     */
    public function getVegasBankName(): string
    {
        return $this->getCurrency()['vegas_bank'];
    }

    /**
     * @deprecated
     * @return string
     */
    public function getVegasBonusBankName(): string
    {
        return $this->getCurrency()['vegas_bonus_bank'];
    }

    /**
     * currency hall id
     * @return int
     */
    public function getHallId(): int
    {
        return $this->getCurrency()['hall_id'];
    }

    /**
     * add money to wallet
     * @param float $amount
     * @return $this
     */
    public function addMoney(float $amount)
    {
        $this->increment('amount', round($amount, $this->getDigits(), PHP_ROUND_HALF_DOWN));
        return $this;
    }

    public function addReferralMoney(float $amount)
    {
        $this->increment('referral_money', round($amount, $this->getDigits(), PHP_ROUND_HALF_DOWN));
        return $this;
    }

    public function addBonusMoney(float $amount)
    {
        $this->increment('bonus_money', round($amount, $this->getDigits(), PHP_ROUND_HALF_DOWN));
        return $this;
    }

    /**
     * remove money from wallet
     * @param float $amount
     * @return $this
     * @throws \Exception
     */
    public function remMoney(float $amount)
    {
        $this->decrement('amount', round($amount, $this->getDigits(), PHP_ROUND_HALF_DOWN));
        if (UserWallet::find($this->id)->getBalance() < 0) {
            throw new \Exception('Balance < 0');
        }
        return $this;
    }

    public function remReferralMoney(float $amount)
    {
        $this->decrement('referral_money', $amount);
        return $this;
    }

    public function remBonusMoney(float $amount)
    {
        $this->decrement('bonus_money', $amount);
        return $this;
    }

    /**
     * add money amount user should play to
     * get access to payout
     * @param float $amount
     * @return $this
     */
    public function addLockedMoney(float $amount): UserWallet
    {
        $this->increment('locked_money', $amount);
        return $this;
    }

    public function remLockedMoney(float $amount): UserWallet
    {
        if ($amount > $this->getLockedMoney()) $amount = $this->getLockedMoney();
        $this->decrement('locked_money', $amount);
        return $this;
    }

    public function setLockedMoney(float $amount): UserWallet
    {
        $this->locked_money = $amount;
        return $this;
    }

    public function getLockedMoney(): float
    {
        return $this->locked_money;
    }


    /**
     * TotalBet increment
     * @param float $amount
     */
    public function addBet(float $amount)
    {
        $this->increment('TotalBet', $amount);
    }

    /**
     * TotalWin increment
     * @param float $amount
     */
    public function addWin(float $amount)
    {
        $this->increment('TotalWin', $amount);
    }


    /**
     * wallets current balance
     * @return float
     */
    public function getBalance(): float
    {
        $k = intval(str_pad('1', 1 + $this->getDigits(), '0'));
        return floor($this->amount * $k) / $k;
    }

    /**
     * wallet balance string
     * @return string
     */
    public function getBalanceStr()
    {
        return number_format($this->amount, $this->getDigits(), '.', ',');
    }

    public function getDigits()
    {
        return $this->digits[$this->currency] ?? 2;
    }

    /**
     * @return float
     */
    public function getBonusMoney(): float
    {
        return round($this->bonus_money, $this->getDigits());
    }

    /**
     * @return float
     */
    public function getReferralMoney(): float
    {
        return round($this->referral_money, $this->getDigits());
    }

    /**
     * @return float
     */
    public function getMaxAutoPayoutSum(): float
    {
        return $this->getCurrency()['max_auto_payout'];
    }

    /**
     * @deprecated
     * if sport user is registered for current currency
     * @return bool
     */
    public function isSportRegistered(): bool
    {
        return !empty($this->sport_id);
    }

    /**
     * @deprecated
     * @return string|null
     */
    public function getSportId(): ?string
    {
        return $this->sport_id;
    }

    /**
     * @deprecated
     * @param string $sport_id
     * @return $this
     */
    public function setSportId(string $sport_id): UserWallet
    {
        $this->sport_id = $sport_id;
        return $this;
    }

    public function getSportToken(): ?string
    {
        return $this->sport_token;
    }

    public function setSportToken(string $token): UserWallet
    {
        $this->sport_token = $token;
        return $this;
    }

    public function getSportAccessKey(): ?string
    {
        return $this->sport_access_key;
    }

    public function setSportAccessKey(string $key): UserWallet
    {
        $this->sport_access_key = $key;
        return $this;
    }

    /**
     * @deprecated
     * sport currency code from config.wallets
     * @return int
     */
    public function getSportCode(): int
    {
        return $this->getCurrency()['sport_code'];
    }

    /**
     * set wallet active
     */
    public function setActive()
    {
        $this->active = true;
        $this->save();
    }

    /**
     * set wallet inactive
     */
    public function setInactive()
    {
        $this->active = false;
        $this->save();
    }

    public function isCrypto(): bool
    {
        return $this->getCurrency()->isCrypto();
    }

    public function getTotalBet(): float
    {
        return $this->TotalBet;
    }

    public function setTotalBet(float $TotalBet): void
    {
        $this->TotalBet = $TotalBet;
    }

    public function getTotalWin(): float
    {
        return $this->TotalWin;
    }

    public function setTotalWin(float $TotalWin): void
    {
        $this->TotalWin = $TotalWin;
    }

    public function getTotalIn(): float
    {
        return $this->TotalIn;
    }

    public function setTotalIn(float $TotalIn): void
    {
        $this->TotalIn = $TotalIn;
    }

    public function getTotalOut(): float
    {
        return $this->TotalOut;
    }

    public function setTotalOut(float $TotalOut): void
    {
        $this->TotalOut = $TotalOut;
    }

    public function getBonusId(): ?int
    {
        return $this->bonus_id;
    }

    public function setBonusId(?int $bonus_id): void
    {
        $this->bonus_id = $bonus_id;
        $this->save();
    }

    /**
     * current bonus affects on total balance
     * @return HasOne
     */
    public function userBonus(): HasOne
    {
        return $this->hasOne(UserBonus::class, 'id', 'bonus_id');
    }

    /**
     * find wallet by sport user id
     * @param string $sport_id
     * @return UserWallet
     */
    public static function getBySportId(string $sport_id): UserWallet
    {
        return UserWallet::where('sport_id', $sport_id)->first();
    }

    public static function getCurrencies(): Collection
    {
        if (self::$_curencies === null) {
            self::$_curencies = UserCurrency::all();
        }
        return self::$_curencies;
    }

    /**
     * get currency id by international code
     * @param int $int_code
     * @return int
     */
    public static function getIdByIntCode(int $int_code): int
    {
        /** @var UserCurrency $curr */
        $curr = self::getCurrencies()->where('code', $int_code)->first();
        if (!$curr) {
            Hlog::logException(new \Exception('Can`t find currency with int code#' . $int_code));
            return 0;
        }
        return $curr->id;
    }

    /**
     * get international currency code
     * @param int $currency_id
     * @return int
     */
    public static function getIntCodeById(int $currency_id): int
    {
        /** @var UserCurrency $curr */
        $curr = self::getCurrencies()->where('id', $currency_id)->first();
        if (!$curr) {
            Hlog::logException(new \Exception('Can`t find currency with int code#' . $currency_id));
            return 0;
        }
        return $curr->code;

//        return config('wallets.'.config('wallets.currency')[$currency_id])['code'];
    }

    /**
     * currency short name (UAH)
     * @param int $currency_id
     * @return string
     */
    public static function currencyName(int $currency_id): string
    {
        /** @var UserCurrency $curr */
        $curr = self::getCurrencies()->where('id', $currency_id)->first();
        if (!$curr) {
            Hlog::logException(new \Exception('Can`t find currency with int code#' . $currency_id));
            return 0;
        }
        return $curr->name;

        //        return config('wallets.currency')[$currency_id];
    }

    public static function currencySymbol(int $currency_id): string
    {
        /** @var UserCurrency $curr */
        $curr = self::getCurrencies()->where('id', $currency_id)->first();
        if (!$curr) {
            Hlog::logException(new \Exception('Can`t find currency with int code#' . $currency_id));
            return 0;
        }
        return $curr->symbol;
//        return config('wallets.'.self::currencyName($currency_id))['symbol'];
    }

    public static function getHallCurrencyCode(int $hall_id): int
    {
        /** @var UserCurrency $curr */
        $curr = self::getCurrencies()->where('hall_id', $hall_id)->first();
        if (!$curr) {
            Hlog::logException(new \Exception('Can`t find currency with int hall#' . $hall_id));
            return 0;
        }
        return $curr->id;
//        return config('wallets.'.config('wallets.halls')[$hall_id])['currency'];
    }

    public static function currencyHallId(int $currency_id)
    {
        /** @var UserCurrency $curr */
        $curr = self::getCurrencies()->where('id', $currency_id)->first();
        if (!$curr) {
            Hlog::logException(new \Exception('Can`t find currency with int currency_id#' . $currency_id));
            return 0;
        }
        return $curr->hall_id;
//        return config('wallets.' . config('wallets.currency')[$currencyId] )['hall_id'];
    }

    /**
     * get currency id by currency name
     * @param string $curr_name
     * @return int
     */
    public static function getCurrencyCode(string $curr_name): int
    {
        /** @var UserCurrency $curr */
        $curr = self::getCurrencies()->where('name', $curr_name)->first();
        if (!$curr) {
            Hlog::logException(new \Exception('Can`t find currency with name#' . $curr_name));
            return 0;
        }
        return $curr->id;
    }

    /**
     * list of active currencies
     * @return array
     */
    public static function activeCurrencies(): array
    {
        $lang_priorities = [];

        //$available_curr = self::getCurrencies()->pluck('id')->toArray();

        $available_curr = [
            self::CURRENCY_RUB,
            self::CURRENCY_UAH,
            self::CURRENCY_EUR,
            self::CURRENCY_INR,
//            self::CURRENCY_mBTC,
            self::CURRENCY_USDT,
            self::CURRENCY_KZT,
            self::CURRENCY_UZS,
        ];

        try {
            if (App::getLocale() == 'hi') {
                $lang_priorities = [
                    self::CURRENCY_INR,
//                    self::CURRENCY_USD,
                    self::CURRENCY_EUR,
                ];
            }
            if (request()->getHost() === 'krakencasino.bet') {
                $lang_priorities = [
                    self::CURRENCY_KZT,
                ];
            }
            if (request()->getHost() === 'krakencasino.co') {
                $lang_priorities = [
                    self::CURRENCY_USDT,
                ];
            }
        } catch (\Exception $e) {
            Log::channel('stack')->error($e->getMessage());
        }

        $left_currencies = array_diff($available_curr, $lang_priorities);

        return array_merge($lang_priorities, $left_currencies);
    }

}
