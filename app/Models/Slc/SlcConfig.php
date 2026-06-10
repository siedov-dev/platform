<?php

namespace App\Models\Slc;

use App\Models\SiteData;
use App\Models\UserDailyStat;
use App\MUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int $bet_cost
 * @property float $jackpot
 * @property int $jackpot_prc
 * @property int $start_jackpot
 * @property int $max_jackpot
 * @property int $random_gen
 * @property int $wheel_respin
 * @property int $second_respin
 * @property float $jackpot_inc
 * @property int $jackpot_reset
 * @property int $small_key_required
 * @property int $middle_key_required
 * @property int $big_key_required
 * @property int $bermuda_key_required
 * @property int $lost_key_required
 * @property int $black_key_required
 * @property int $spin_price
 */
class SlcConfig extends Model
{
    use HasFactory;

    const RANDOM_GEN_DEFAULT = 0; // генератор php
    const RANDOM_GEN_RDRAND = 1; // генератор rd_rand

    private static ?SlcConfig $_params = null;

    public static function getParams(): SlcConfig
    {
        if (self::$_params === null){
            self::$_params = SlcConfig::firstOrCreate();
        }
        return self::$_params;
    }

    /**
     * Возвращает цену ставки слотмашины
     * @return int
     */
    public static function getBetCost(): int
    {
//        return Cache::remember('slc_bet_cost', 600, function () {
        return self::getParams()->bet_cost ?? 1;
//        });
    }

    /**
     * Возвращает цену ставки слотмашины
     * @return int
     */
    public function getBet(): int
    {
        return $this->bet_cost;
    }

    public function setBetCost(int $bet_cost): void
    {
        $this->bet_cost = $bet_cost;
    }

    /**
     * Возвращает значение джекпота
     * @return float
     */
    public function getJackpot(): float
    {
        return $this->jackpot;
    }

    public function setJackpot(float $jackpot): void
    {
        $this->jackpot = $jackpot;
    }

    /**
     * Увеличивает джекпот от ставки
     * @param int $bet
     * @return void
     */
    public function addJackpotBet(int $bet): void
    {
        $this->increment('jackpot', $bet * $this->getJackpotPrc() / 100);
    }

    /**
     * Возвращает процент джекпота от ставки
     * @return int
     */
    public function getJackpotPrc(): int
    {
        return $this->jackpot_prc;
    }

    public function setJackpotPrc(int $jackpot_prc): void
    {
        $this->jackpot_prc = $jackpot_prc;
    }

    /**
     * Возвращает стартовое значение джекпота
     * @return int
     */
    public function getStartJackpot(): int
    {
        return $this->start_jackpot;
    }

    public function setStartJackpot(int $start_jackpot): void
    {
        $this->start_jackpot = $start_jackpot;
    }

    /**
     * Возвращает максимальное значение джекпота
     * @return int
     */
    public function getMaxJackpot(): int
    {
        return $this->max_jackpot;
    }

    public function setMaxJackpot(int $max_jackpot): void
    {
        $this->max_jackpot = $max_jackpot;
    }

    /**
     * Устанавливает тип генератора случайных чисел
     * @return int
     */
    public function getRandomGen(): int
    {
        return $this->random_gen;
    }

    public function setRandomGen(int $random_gen): void
    {
        $this->random_gen = $random_gen;
    }

    /**
     * Возвращает цену первого респина колеса
     * @return int
     */
    public function getWheelRespin(): int
    {
        return $this->wheel_respin;
    }

    public function setWheelRespin(int $wheel_respin): void
    {
        $this->wheel_respin = $wheel_respin;
    }

    /**
     * Возвращает цену второго респина колеса
     * @return int
     */
    public function getSecondRespin(): int
    {
        return $this->second_respin;
    }

    public function setSecondRespin(int $second_respin): void
    {
        $this->second_respin = $second_respin;
    }

    /**
     * Возвращает значение автоувеличения джекпота
     * @return float
     */
    public function getJackpotInc(): float
    {
        return $this->jackpot_inc;
    }

    public function setJackpotInc(float $jackpot_inc): void
    {
        $this->jackpot_inc = $jackpot_inc;
    }

    /**
     * Возвращает стартовое значение джекпота
     * @return int
     */
    public function getJackpotReset(): int
    {
        return $this->jackpot_reset;
    }

    public function setJackpotReset(int $jackpot_reset): void
    {
        $this->jackpot_reset = $jackpot_reset;
    }

    /**
     * Возвращает к-во осколков необходимое для сборки ключа
     * @param SlcItemType $piece_type
     * @return int
     */
    public function getKeyPiecesRequired(SlcItemType $piece_type): int
    {
        return match ($piece_type) {
            SlcItemType::SmallKeyPiece => $this->small_key_required,
            SlcItemType::MiddleKeyPiece => $this->middle_key_required,
            SlcItemType::BigKeyPiece => $this->big_key_required,
            SlcItemType::LostKeyPiece => $this->lost_key_required,
            SlcItemType::BermudaKeyPiece => $this->bermuda_key_required,
            SlcItemType::BlackKeyPiece => $this->black_key_required,
        };
    }

    public function setKeyPiecesRequired(SlcItemType $piece_type, $amount): void
    {
        match ($piece_type) {
            SlcItemType::SmallKeyPiece => $this->small_key_required = $amount,
            SlcItemType::MiddleKeyPiece => $this->middle_key_required = $amount,
            SlcItemType::BigKeyPiece => $this->big_key_required = $amount,
            SlcItemType::LostKeyPiece => $this->lost_key_required = $amount,
            SlcItemType::BermudaKeyPiece => $this->bermuda_key_required = $amount,
            SlcItemType::BlackKeyPiece => $this->black_key_required = $amount,
        };
    }

    /**
     * Автоувеличение джекпота
     * @return void
     */
    public static function slcJackpotAutoInc()
    {
        $params = SlcConfig::getParams();
        $params->increment('jackpot', $params->getJackpotInc());

        $spins = SiteData::getValue('slc_jackpot_spin', 0);
        $spins++;

        if ($params->getJackpotReset() > 0 && $spins >= $params->getJackpotReset()) {
            $spins = 0;
            $params->setJackpot($params->getStartJackpot());
            $params->save();
        }
        SiteData::setValue('slc_jackpot_spin', $spins);

        if ($params->getMaxJackpot() > 0 && $params->getJackpot() > $params->getMaxJackpot()) {
            $params->setJackpot($params->getMaxJackpot());
            $params->save();
        }
    }

    /**
     * Возвращает цену респина колеса
     * @param MUser $m_user
     * @return int
     */
    public static function getWheelRespinCost(MUser $m_user): int
    {
        $params = self::getParams();
        $respin = UserDailyStat::getIntValue($m_user->getId(), 'wheel_respin');
        return ($respin === 0)? $params->getWheelRespin() : $params->getSecondRespin();
    }

    /**
     * Возвращает цену спина колеса
     * @return int
     */
    public function getSpinPrice(): int
    {
        return $this->spin_price;
    }

    public function setSpinPrice(int $spin_price): void
    {
        $this->spin_price = $spin_price;
    }

}
