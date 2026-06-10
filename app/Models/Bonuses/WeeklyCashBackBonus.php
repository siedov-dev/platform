<?php


namespace App\Models\Bonuses;


use App\UserWallet;
use App\MUser;

/**
 * @deprecated
 */
class WeeklyCashBackBonus extends KUserBonus
{

    /*
     * time of bonuses creation
     * only informational
     * should be same as cron config
    */
    protected $activation_time = '09:00:00';
    protected $activation_day_of_week = 2; //mon - 0, tue - 1...

    protected $level_percents = [
        1 => 3,
        2 => 4,
        3 => 5,
        4 => 7,
        5 => 10,
    ];
//
//    /**
//     * WeeklyCahBackBonus constructor.
//     * @param array $attributes
//     */
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_WEEKLY_CASH_BACK;
//    }
//
//    protected $guarded = ['type'];

    public function getName(): string
    {
        return __('bonus.bonus_16_name');
    }

    public function getDescription(): string
    {
        return __('bonus.bonus_16_descr1', ['prc' => $this->getLevelPercent(), 'date' => $this->getExpirationDate()->format('d.m.Y H:i:s')]);
//        return "При каждом пополнении ".$this->getLevelPercent()."% от суммы накапливается в кешбэк. "
//            .$this->getExpirationDate()->format('d.m.Y H:i:s')." накопленная сума будет переведена для отыграша в бездепозитный бонус.";
    }

    protected function getLevelPercent():int
    {
        /** @var MUser $user */
        $user = MUser::find($this->user_id);
        return $this->level_percents[$user->getLevel()];
    }

    public static function createForUser($user, $currency = UserWallet::CURRENCY_RUB, $wager = 9, $lifetime = 48)
    {
        $bonus = new static();
        $bonus->status = static::STATUS_PENDING;
        $bonus->user_id = $user->id;
        $bonus->hall_id = $user->hall_id;
        $bonus->currency = $user->currency;
        $bonus->spins_available = -1;
        $bonus->money_available = 0;
        $bonus->wager_to_payout = $wager;
        $bonus->hours_active = $lifetime;
        $bonus->save();

        return $bonus;
    }

    public function isCancelable(): bool
    {
        return false;
    }

//
//    public function OnMoneyDeposit(MoneyDeposit $event)
//    {
//        return;
//        Log::info(implode(' ', [__CLASS__, '@OnMoneyDeposit user#', $event->user->id, $event->amount, UserWallet::currencyName($event->currency)]));
//
//        $bonus = WeeklyCashBackBonus::where('user_id', $event->user->id)
//            ->where('currency', $event->currency)
//            ->where('type', static::TYPE_WEEKLY_CASH_BACK)
//            ->first();
//
//        if(!$bonus){
//            $bonus = WeeklyCashBackBonus::createForUser($event->user);
//            $bonus->currency = $event->currency;
//            $bonus->hall_id = UserWallet::currencyHallId($event->currency);
//        }
//
//        $bonus->money_available += ($event->amount * $this->level_percents[$event->user->getLevel($event->currency)]) / 100;
//        $bonus->save();
//    }
//
//    /**
//     * Register the listeners for the subscriber.
//     *
//     * @param  \Illuminate\Events\Dispatcher  $events
//     */
//    public function subscribe($events)
//    {
//        $events->listen('App\Events\MoneyDeposit', 'App\Models\Bonuses\WeeklyCashBackBonus@OnMoneyDeposit');
//    }

//    /**
//     * create bonuses
//     * start from cron once a week
//     */
//    public static function processBonuses()
//    {
//        return;
//        $emails = [];
//
//        $bonuses = WeeklyCashBackBonus::where('type', static::TYPE_WEEKLY_CASH_BACK)->get();
//        /** @var WeeklyCashBackBonus $bonus */
//        foreach ($bonuses as $bonus) {
//
//            $params = BonusCurrencyConfig::getBonusConfig($bonus->currency, $bonus->type);
//
//            $money_k = 1;
//
//            if ($params === null) {
//                Log::channel('stack')->warning('bonus currency params not found! Currency:'.$bonus->currency.' Bonus type#'.$bonus->type);
//            } else {
//                $money_k = $params->money_k;
//            }
//
//
//            if($bonus->money_available >= (100 * $money_k)) {
//
//                /** @var MUser $user */
//                $user = MUser::find($bonus->user_id);
//
//                if($user) {
//                    $pb_bonus = BezdepBonus::createForUser($user, -1, $bonus->money_available, 9);
//                    $pb_bonus->active_to = now()->addHours($bonus->hours_active)->format('Y-m-d H:i:s');
//                    $pb_bonus->hall_id = $bonus->hall_id;
//                    $pb_bonus->currency = $bonus->currency;
//                    $pb_bonus->save();
//
//                    $bonus->money_available = 0;
//                    $bonus->save();
//
//                    if(!empty($user->email)) $emails[] = $user->email;
//
//                    UserNotifications::notify($user->id, __('bonus.bonus_16_notify_title'),
//                        __('bonus.bonus_16_notify_msg'));
//                }
//            }
//        }
//
//        if(!empty($emails)) {
//            $mail = new Email([
//                'to' => $emails,
//                'subject' => 'Krakencassino',
//                'text' => __('bonus.bonus_16_notify_title') . __('bonus.bonus_16_notify_msg'),
//                'html' => \App\Email::createKrakenMailHtml(__('bonus.bonus_16_notify_title') . __('bonus.bonus_16_notify_msg'), 'Krakencassino'),
//            ]);
//            @$mail->send();
//        }
//    }


    public function actualize()
    {
//        if($this->status != self::STATUS_PENDING) {
//            $this->status = self::STATUS_PENDING;
//            $this->save();
//        }
    }

}
