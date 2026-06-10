<?php

namespace App\Models;

use App\Models\Bonuses\UserBonus;
use App\MUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Class BonusStatistic
 * @package App\Models
 * Сохраняет информацию о смене статусов бонусов.
 *
 * @property int $id
 * @property int $bonus_id
 * @property int $bonus_type
 * @property int $promocode_id
 * @property int $pattern_id
 * @property int $user_id
 * @property int $action
 * @property float $amount
 * @property int $currency
 * @property int $status_before
 * @property int $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $data
 */
class BonusStatistic extends Model
{

    const ACTION_CREATED = 0;
    const ACTION_ACTIVATE = 1;
    const ACTION_CANCEL = 2;
    const ACTION_DONE = 3;
    const ACTION_FAIL = 4;
    const ACTION_WIN = 5;
    const ACTION_DEPOSIT = 6;
    const ACTION_EXPIRED = 7;
    const ACTION_FS_DONE = 8;
    const ACTION_ACTIVATION_MENU = 9;

    //
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $guarded = ['id'];

    public static function saveStat(UserBonus $bonus, int $user_id, int $action, ?float $amount, int $status_before,
                                    int $status, ?int $pattern_id = null, $data = null)
    {
        if (is_array($data)) $data = json_encode($data);
        try {

            $stat = new static();
            $stat->bonus_id = $bonus->id;
            $stat->bonus_type = $bonus->type;
//            $stat->promocode_id = $data->promocode_id;
            if ($bonus->creator_type == UserBonus::CREATOR_PROMO) {
                $stat->promocode_id = $bonus->creator_id;
            }
            $stat->user_id = $user_id;
            $stat->action = $action;
            $stat->amount = $amount?? 0;
            $stat->currency = $bonus->currency;
            $stat->status_before = $status_before;
            $stat->status = $status;
            $stat->pattern_id = $pattern_id;
            $stat->data = $data;
            $stat->save();

        } catch (\Exception $e) {
            $stat = null;
            Log::error($e->getMessage());
        }

        return $stat;
    }

    public static function getActionName(int $action): string
    {
        switch ($action) {
            case self::ACTION_CREATED:
                return "Создан";
            case self::ACTION_ACTIVATE:
                return "Активация";
            case self::ACTION_CANCEL:
                return "Отменен";
            case self::ACTION_DONE:
                return "Завершён";
            case self::ACTION_FAIL:
                return "Проигрыш";
            case self::ACTION_WIN:
                return "Выигрыш";
            case self::ACTION_DEPOSIT:
                return "Оплата";
            case self::ACTION_EXPIRED:
                return "Истек";
            case self::ACTION_FS_DONE:
                return "Фриспины завершены";
            case self::ACTION_ACTIVATION_MENU:
                return "Активация меню";
        }
        return 'unknown';
    }

    public static function expired(UserBonus $bonus)
    {
        BonusStatistic::saveStat($bonus, $bonus->user_id, BonusStatistic::ACTION_EXPIRED,
            $bonus->money_available, $bonus->status, UserBonus::STATUS_EXPIRED);
    }

    public static function fail(UserBonus $bonus)
    {
        BonusStatistic::saveStat($bonus, $bonus->user_id, BonusStatistic::ACTION_FAIL, $bonus->money_available,
            UserBonus::STATUS_ACTIVE, UserBonus::STATUS_DONE);
    }

    public static function win(UserBonus $bonus, float $amount)
    {
        BonusStatistic::saveStat($bonus, $bonus->user_id, BonusStatistic::ACTION_WIN, $amount,
            UserBonus::STATUS_ACTIVE, UserBonus::STATUS_DONE, $bonus->pattern_id);
    }

    public static function createdBonus(UserBonus $bonus, $params)
    {
        BonusStatistic::saveStat($bonus, $bonus->user_id, BonusStatistic::ACTION_CREATED,
            $bonus->money_available, UserBonus::STATUS_NOT_ACTIVATED, $bonus->status, $bonus->pattern_id, $params);
    }

    /**
     * @param int $bonus_id
     * @return null|BonusStatistic[]
     */
    public static function getForBonus(int $bonus_id)
    {
        return BonusStatistic::where('bonus_id', $bonus_id)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public static function getParamsName(string $param): string
    {
        return match ($param) {
            'wager' => 'Вейджер (сумма)',
            'wager_k' => 'Вейджер (коеф.)',
            'money' => 'доступная сумма',
            'max_win' => 'Макс. выигрыш',
            'money_to_activate' => 'сумма активации',
            default => $param,
        };
    }

    public static function getActionColor(int $action): string
    {
        return match ($action) {
            self::ACTION_CANCEL, self::ACTION_EXPIRED, self::ACTION_FAIL => '#FF000010',
            self::ACTION_ACTIVATION_MENU, self::ACTION_FS_DONE => '#00FFFF10',
            default => '#00FF0010',
        };
    }
}
