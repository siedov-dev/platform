<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Class BonusCurrencyConfig
 * Модель предназначена для приведения параметров бонусов из руб в другие валюты.
 * Так же используется для приведения значения денег из руб в другие валюты, где это не критично.
 * Не жалательно использовать.
 * @package App\Models
 * @property int $id
 * @property int $bonus_type 0 - default
 * @property int $currency ид валюты
 * @property int $type не используется
 * @property int $round_numbers round precision (2 - two decimals before dec point -2 after dec point)
 * @property float $spins_k коэффициент спинов
 * @property float $money_k коэффициент денег
 * @property float $wager_k коэффициент вейджера
 * @property float $max_bet_k коэффициент максимальной ставки
 * @property float $money_to_activate_k коэффициент суммы активаций
 * @property float $min_deposit_k коэффициент мин депозита
 * @property float $max_deposit_k коэффициент макс депозита
 * @property float $min_win_sum_k when bonus limited by win sum
 * @property float $max_win_sum_k
 * @property float $min_win_coef_k when bonus limited by win coefficient
 * @property float $max_win_coef_k
 */
class BonusCurrencyConfig extends Model
{
    //
    const TYPE_DEFAULT = 0;

    const BONUS_TYPE_DEFAULT = 0;

    public static function getBonusConfig(int $currency, int $bonus_type = 0): ?BonusCurrencyConfig
    {
        return Cache::remember('bonus_conf' . $currency . 'type' . $bonus_type, 600, function () use ($bonus_type, $currency) {
            $conf = self::where('bonus_type', $bonus_type)
                ->where('currency', $currency)
                ->where('type', self::TYPE_DEFAULT)
                ->first();

            if ($conf !== null) {
                return $conf;
            }

            return self::where('bonus_type', self::BONUS_TYPE_DEFAULT)
                ->where('currency', $currency)
                ->where('type', self::TYPE_DEFAULT)
                ->first();

        });
    }

    /**
     * Возвращает коэффициент соотношения указанной валюты к рублю.
     * @param $currency
     * @return float
     */
    public static function getMoneyK($currency): float
    {
        $params = BonusCurrencyConfig::getBonusConfig($currency);

        $money_k = 1;

        if ($params === null) {
            Log::channel('stack')->warning('bonus currency params not found! Currency:' . Auth::user()->currency);
        } else {
            $money_k = $params->money_k;
        }

        return $money_k;
    }

}
