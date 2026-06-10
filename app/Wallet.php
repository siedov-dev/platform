<?php

namespace App;

use App\Helpers\Hlog;
use App\Models\UserCurrency;
use App\Services\Users\UserManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/**
 * Кошелек пользователя.
 * Работает с user_manager(kusers) сервисом.
 */
class Wallet
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

    private array $digits = [
        self::CURRENCY_mBTC => 4,
    ];

    public function __construct(private int $user_id, private int $currency_id)
    {
    }

    public function getUser(): MUser
    {
        return MUser::find($this->user_id);
    }

    /**
     * get currency name (RUB, UAH...)
     * @param int|null $currency_id
     * @return string
     */
    public function getCurrencyName(?int $currency_id = null): string
    {
        if ($currency_id === null) $currency_id = $this->currency_id;
        return UserManager::getCurrencyName($currency_id);
    }

    public function getGameCurrencyName(?int $currency_id = null): string
    {
        if ($currency_id === null) $currency_id = $this->currency_id;
        return UserManager::getGameCurrencyName($currency_id);
    }

    /**
     * get currency name (RUB, UAH...)
     * @param int $currency_id
     * @return string
     */
    public static function getCurrencyNameById(int $currency_id): string
    {
        return UserManager::getCurrencyName($currency_id);
    }

    /**
     * @return UserCurrency
     */
    protected function getCurrency(): UserCurrency
    {
        return UserCurrency::findOrFail($this->currency_id);
    }

    /**
     * return currency id (1,2,3...)
     * @return int
     */
    public function getCurrencyId(): int
    {
        return $this->currency_id;
    }


    /**
     * get currency internation code (980, 643...)
     * @return int
     */
    public function getCurrencyIntCode(): int
    {
        return UserManager::getCurrencyIntCode($this->currency_id);
    }

    /**
     * currency hall id
     * @return int
     */
    public function getHallId(): int
    {
        return UserManager::getCurrencyHallId($this->currency_id);
    }

    /**
     * add money to wallet
     * @param float $amount
     * @return $this
     */
    public function addMoney(float $amount): Wallet
    {
        $resp = UserManager::balanceAdd($this->user_id, $this->currency_id, $amount);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            throw new \Exception($resp['err']);
        }
        return $this;
    }

    public function addReferralMoney(float $amount): Wallet
    {
        $resp = UserManager::balanceAddReferral($this->user_id, $this->currency_id, $amount);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            throw new \Exception($resp['err']);
        }
        return $this;
    }

    public function addBonusMoney(float $amount): Wallet
    {
        $resp = UserManager::balanceAddBonus($this->user_id, $this->currency_id, $amount);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            throw new \Exception($resp['err']);
        }
        return $this;
    }

    /**
     * remove money from wallet
     * @param float $amount
     * @return $this
     * @throws \Exception
     */
    public function remMoney(float $amount): Wallet
    {
        $resp = UserManager::balanceRem($this->user_id, $this->currency_id, $amount);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            throw new \Exception($resp['err']);
        }
        return $this;
    }

    public function remReferralMoney(float $amount): Wallet
    {
        $resp = UserManager::balanceRemReferral($this->user_id, $this->currency_id, $amount);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            throw new \Exception($resp['err']);
        }
        return $this;
    }

    public function remBonusMoney(float $amount): Wallet
    {
        $resp = UserManager::balanceRemBonus($this->user_id, $this->currency_id, $amount);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            throw new \Exception($resp['err']);
        }
        return $this;
    }

    /**
     * add money amount user should play to
     * get access to payout
     * @param float $amount
     * @return $this
     */
    public function addLockedMoney(float $amount): Wallet
    {
        $resp = UserManager::balanceAddLocked($this->user_id, $this->currency_id, $amount);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            throw new \Exception($resp['err']);
        }
        return $this;
    }

    public function remLockedMoney(float $amount): Wallet
    {
        $resp = UserManager::balanceRemLocked($this->user_id, $this->currency_id, $amount);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            throw new \Exception($resp['err']);
        }
        return $this;
    }

    public function setLockedMoney(float $amount): Wallet
    {
        $resp = UserManager::balanceSetLocked($this->user_id, $this->currency_id, $amount);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            throw new \Exception($resp['err']);
        }
        return $this;
    }

    public function getLockedMoney(): float
    {
        $resp = UserManager::getLockedBalance($this->user_id, $this->currency_id);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            throw new \Exception($resp['err']);
        }
        return $resp['v'];
    }


    /**
     * wallets current balance
     * @return float
     */
    public function getBalance(): float
    {
        $k = intval(str_pad('1', 1 + $this->getDigits(), '0'));
        $resp = UserManager::getBalance($this->user_id, $this->currency_id);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            throw new \Exception($resp['err']);
        }
        $amount = $resp['v'];
        return floor($amount * $k) / $k;
    }

    /**
     * wallet balance string
     * @return string
     */
    public function getBalanceStr(): string
    {
        return number_format($this->getBalance(), $this->getDigits(), '.', ',');
    }

    public function getDigits()
    {
        return $this->digits[$this->currency_id] ?? 2;
    }

    /**
     * @return float
     */
    public function getBonusMoney(): float
    {
        $resp = UserManager::getBonusBalance($this->user_id, $this->currency_id);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            throw new \Exception($resp['err']);
        }
        return $resp['v'];
    }

    /**
     * Возвращает реферальный баланс
     * @return float
     * @throws \Exception
     */
    public function getReferralMoney(): float
    {
        $resp = UserManager::getReferralBalance($this->user_id, $this->currency_id);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            throw new \Exception($resp['err']);
        }
        return $resp['v'];
    }

    public function transferReferralMoney(): float
    {
        $resp = UserManager::balanceTransferReferral($this->user_id, $this->currency_id);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            throw new \Exception($resp['err']);
        }
        return $resp['v'];
    }

    /**
     * @return float
     */
    public function getMaxAutoPayoutSum(): float
    {
        return UserManager::getCurrencyMaxAutoPayout($this->currency_id);
    }

    /**
     * set wallet active
     */
    public function setActive(): void
    {
        UserManager::walletSetActive($this->user_id, $this->currency_id, true);
    }

    /**
     * set wallet inactive
     */
    public function setInactive(): void
    {
        UserManager::walletSetActive($this->user_id, $this->currency_id, false);
    }

    public function isCrypto(): bool
    {
        return $this->getCurrency()->isCrypto();
    }

    public function getTotalBet(): float
    {
        $resp = UserManager::walletGetTotalBet($this->user_id, $this->currency_id);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            return 0;
        }
        return $resp['v'];
    }

    public function setTotalBet(float $total_bet): void
    {
        UserManager::walletSetTotalBet($this->user_id, $this->currency_id, $total_bet);
    }

    public function addBet(float $amount)
    {
        $this->setTotalBet($this->getTotalBet() + $amount);
    }

    /**
     * TotalWin increment
     * @param float $amount
     */
    public function addWin(float $amount)
    {
        $this->setTotalWin($this->getTotalWin() + $amount);
    }

    public function getTotalWin(): float
    {
        $resp = UserManager::walletGetTotalWin($this->user_id, $this->currency_id);
        if (empty($resp['cd']) || $resp['cd'] !== 200) {
            return 0;
        }
        return $resp['v'];
    }

    public function setTotalWin(float $total_win): void
    {
        UserManager::walletSetTotalWin($this->user_id, $this->currency_id, $total_win);
    }

    public function save(): bool
    {
        return true;
    }

    public function getBonusId(): ?int
    {
        return UserManager::walletGetBonusId($this->user_id, $this->currency_id);
    }

    public function setBonusId(?int $bonus_id): void
    {
        UserManager::walletSetBonusId($this->user_id, $this->currency_id, $bonus_id);
    }

    public static function getCurrencies(): Collection
    {
        return UserCurrency::all();
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
            Hlog::logException(new \Exception('Can`t find currency with int name#' . $curr_name));
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
